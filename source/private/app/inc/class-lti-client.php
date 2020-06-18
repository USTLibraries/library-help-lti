<?php

// this class uses some functions from the php-project-framework library
require_once getPathIncLib()."php-project-framework/functions.php";
// however, it does not use any other functions or variables shared with the lti application
// this class can be used as stand alone with everything necessary to run passed to it in the constructor

class LTI_Client{
	
	public  $oauth_clientid;
	private $oauth_secret;
	private $api_token="";
	private $api_domain="";

	function __construct($oauth_clientid, $oauth_secret, $api_token="", $api_domain="") {

		$this->oauth_clientid = sanitize_hash($oauth_clientid);
		$this->oauth_secret = sanitize_hash($oauth_secret);

		if($api_domain==="") {
			$this->api_domain = $_POST['custom_lri_api_domain'];
		} else {
			$this->api_domain = $api_domain;
		}

		$this->api_token = $api_token;

	}
	
	/**
	 * init function.
	 *
	 * Start session and either store or check the existence of required session vars
	 * 
	 * @access public
	 * @return void
	 */
	 
	public function init() {
	
		//We check if this is the initial load and that Canvas LMS is returning us required data
		if( isset($_POST)
				&& isset($_POST['custom_lri_api_domain'])
			&& isset($_POST['context_label'])
				&& isset($_POST['oauth_consumer_key'])
				&& isset($_POST['user_id'])
				&& isset($_POST['roles'])   
			 
				&& $_POST['oauth_consumer_key'] === $this->oauth_clientid 
				&& validateOAuth()
			){
			// check to make sure we didn't change courses, if so clear out old session
						// this is tricky because we could be loading not from the LMS but the emulator provided in
						// the /lti/tools directory. If we are in an emulator we'll need to detect that so we don't 
						// get logged out of the LTI admin tools area while emulating
						
						// If there is a session already, but the POST LMS ID is not the same as the id stored in the session, we must have switched courses so clear it out and reload
			if ( isset($_SESSION['id']) && $_POST['custom_lri_id'] !== $_SESSION['id'] ) {
								
								// see if we are in an emulator - The LMS sends this field with a different value, but our internal tool sends it with "emulator"
								$emulator = ( isset($_POST['lti_message_type']) && $_POST['lti_message_type'] === "emulator") ? true : false;
								
								// clear everything out
				session_unset();
								
								// since it is an emulator, within the admin tools, we need to keep the admin set to true
								if ($emulator) { $_SESSION['lti-admin'] = true; }
						}

			/* We have the option of using either context_label or custom_lri_course_id
			context_label is ALWAYS supplied by the LMS via post and shouldn't be blank
			custom_lri_course_id may be used if a substituion variable such as Canvas.course.sisSourceId stores the unqiue course ID
			context_label, reguardless of how the LRI is configured, is the default (in case Canvas.course.sisSourceId is empty)
			However if custom_lri_course_id was filled in by an LMS variable then it overrides.
			Typically all courses fed in from a university information system will supply an identifier to the LMS and is typically
			stored in the Canvas.course.sisSourceId variable. However if the course was added manually and is not in the university
			system it may not have a custom ID but it should have a context_label.
			*/
			$course_id = $_POST['context_label']; // default is context_label because it is always present
			if( isset( $_POST['custom_lri_course_id'] ) && $_POST['custom_lri_course_id'] !== "" ) {
				$course_id = $_POST['custom_lri_course_id'];
			}
			// we set both the temporary $data as well as the session
						
			// first we formulate these since they have different keys/values from the POST
			$data['course_id'] = $_SESSION['course_id'] = $course_id;
			$data['id']        = $_SESSION['id']        = $_POST['custom_lri_id'];
			$_SESSION['css_common'] = $_POST['custom_lri_css_common'];
						
			// now we add all the post variables
			foreach ($_POST as $key => $value) {
				$data[$key] = $_SESSION[$key] = $value;
			}
			
			$data['instructor'] = $_SESSION['instructor'] = $this->canvas_api_getInstructor($data['id']);
			
			$data['calledByLMS'] = $_SESSION['calledByLMS'] = true;
			
			$_SESSION['session_signature'] = generateDataSignature($data); // from functions.php
			$_SESSION['session_starttime'] = generateTimestamp(); // from functions.php
					
		} 
		//If it doesn't seem to be the init load we check that we have the data in SESSION or die 
		elseif( !isset($_SESSION['roles']) || 
				!isset($_SESSION['user_id']) ||
					!isset($_SESSION['oauth_consumer_key']) ){
		
			die("An issue occurred while loading the session");
		
		} else {
			// this is not the initial session, so we validate the session
		}
	
	}
	
	
	/**
	 * canvas_api_query function.
	 * 
	 * Function to handle API calls to the Canvas API
	 * 
	 * @access public
	 * @param mixed $path the API route we are trying to query
	 * @param array $query_params (default: array()) query params that we want to pass to the API
	 * @return array the JSON response as an associative array or an error array
	 */
	 
	public function canvas_api_query( $path , $query_params = array() ){
		
		//Default URL params
		$params = array();
		
		//Optional URL params
		if( is_array( $query_params ) && !empty( $query_params ) ){
			$params = array_merge( $params , $query_params );
		}
		
		// We build our CURL URL
		$query = "";
		if ( count($params) > 0) {
			$query = '?'.http_build_query( $params );
		}
				
		logAPIrequest($path.$query, "LMS");
		
		$cURL = new Curl($this->api_token, $this->api_domain);

		$data = $cURL->get($path, $query_params);

		$cURL->closeCurl();
		
		return $data;
		
	}
	
	public function canvas_api_test() {

		$v = array();

		if( $this->api_token !== "" ) {

			logMsg("Testing Canvas API connection");

			$q = array();

			$url = "/courses/";

			$v = $this->getCanvasData( $url, $q );
			logMsg($v);
		}

		return $v;

	}
		
		public function canvas_api_getInstructor($id = 0) {
		
			$lastName = "";
			
			if( $this->api_token !== "" ) {
				
				logMsg("Getting instructor");
			
				$parameters = array("enrollment_type"=>"teacher",
							"sort" => "title",
							"order" => "desc");

				$url = "/courses/".$id."/search_users";
				$v = $this->getCanvasData( $url, $parameters );
				logMsg($v);

				$lastName = "";
				
				// if there is at least one result
				if (is_array($v) && !array_key_exists("errors", $v) && count($v) > 0) {
					// decode it
					$instr = json_decode(json_encode($v[0]), true); // um, okay. For some reason the array returned has a string representation? Dig deeper and fix

					// if there is more than one instructor take the first one alpha by last name
					if( count($instr) > 0) {
						$name = explode(",", $instr['sortable_name']);
						$lastName = preg_replace("/[^A-Z]/", '', strtoupper($name[0])); // remove all non A-Z chars
					}
				}

				
			} else {
				logMsg("Canvas API key not set, unable to request instructor. Follow instructions in Admin Tools to set up Canvas API key.");
			}
			
			return $lastName;
		}

	private function getCanvasData($uri = "", $parameters = array() ) {
	
		$headers = array();
		$headers["Content-Type"] = "application/json";
		$headers["Authorization"] = "Bearer ".$this->api_token;
		
		// remove the trailing / (if there) so there are no extra between the pieces (easier than checking and then adding if not)
		$endpoint = rtrim($this->api_domain,"/") . "/api/v1/" . trim($uri, "/");
		
		return json_decode(generateRequest($endpoint, $parameters, $headers), true);
	}
	
}

?>
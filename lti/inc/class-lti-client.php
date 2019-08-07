<?php

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
		
		if($api_token !== "") {
			$this->api_token = "Authorization: Bearer ".$api_token;
		}
	
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
			
			$v = $this->canvas_api_query( $url, $q );
			logMsg($v);
        }
        
        return $v;

	}
    
    public function canvas_api_getInstructor($id = 0) {
		
		$lastName = "";
		
		if( $this->api_token !== "" ) {
			
			logMsg("Getting instructor");
		
			$q = array("enrollment_type"=>"teacher",
					   "sort" => "title",
					   "order" => "desc");

			$url = "/courses/".$id."/search_users";
			$v = $this->canvas_api_query( $url, $q );
			logMsg($v);

			$lastName = "";
			
			// if there is at least one result
			if (count($v) > 0) {
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
	
	
}

/**
   * Canvas API cURL Class
   *
   * This class was built specifically for use with the Instructure Canvas RESST
   * API.
   *
   * PHP version >= 5.2.0
   *
   * @author Christopher Esbrandt <cesbrandt@ecpi.edu>
   */
    class Curl {
        public $curl;
        public $get;
        public $put;
        private $token;
        private $baseURL;
        private $initCurl;
        private $restartCurl;
        private $closeCurl;
        private $setOpt;
        private $setURLData;
        private $urlPath;
        private $callAPI;
        private $exec;
    /**
     * Contructor function
     *
     * @param $base_url
     */
    public function __construct($token, $domain) {
      if(is_null($token)) {
        throw new \ErrorException('No admin token supplied.');
      }
      if(is_null($domain)) {
        throw new \ErrorException('No domain supplied.');
      }
      $this->token = $token;
      $this->baseURL = 'https://' . $domain . '/api/v1';
      $this->initCurl();
    }
    /**
     * Initialize a cURL call
     */
    private function initCurl() {
      $this->curl = curl_init();
      $this->setOpt(CURLOPT_RETURNTRANSFER, true);
      $this->setOpt(CURLOPT_HEADER, true);
      $this->setOpt(CURLOPT_HTTPHEADER, array('Content-Type: application/json', $this->token));
    }
    /**
     * Restart cURL for multiple calls
     */
    private function restartCurl() {
      $this->closeCurl();
      $this->initCurl();
    }
    /**
     * Close cURL after all calls have been made
     */
    public function closeCurl() {
      curl_close($this->curl);
    }
    /**
     * Execute cURL function
     *
     * @return array
     */
    private function exec($url = NULL) {
      if(!is_null($url)) {
        $this->setURLData($url);
      }
      $results = curl_exec($this->curl);
      $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
      $header = substr($results, 0, $headerSize);
      $results = json_decode(substr($results, $headerSize));
      $this->restartCurl();
      return array($header, $results);
    }
    /**
     * Calls exec() for each page of the API results
     *
     * @return array
     */
    private function callAPI() {
      $currRegex = '/\bpage=\K(\d+\b)(?=[^>]*>; rel="current")/';
      $lastRegex = '/\bpage=\K(\d+\b)(?=[^>]*>; rel="last")/';
      $results = array();
      $call = $this->exec();

      if(substr($call[0], 0, 12) != 'HTTP/1.1 302' && substr($call[0], 0, 12) != 'HTTP/1.1 404') {

        if(is_array($call[1])) {
          foreach($call[1] as $result) {
            array_push($results, $result);
          }
        } else {
          array_push($results, $call[1]);
        }

        preg_match($currRegex, $call[0], $current);
        preg_match($lastRegex, $call[0], $last);
      
        if(sizeof($current) !== 0) {
            while($current[0] !== $last[0]) {
              $call = execCURL($apiURL . ((strpos($apiURL, '?') !== false) ? '&' : '?') . 'page=' . ($current[0] + 1));
              if(substr($call[0], 0, 12) != 'HTTP/1.1 302' && substr($call[0], 0, 12) != 'HTTP/1.1 404') {
                if(is_array($call[1])) {
                  foreach($call[1] as $result) {
                    array_push($results, $result);
                  }
                } else {
                  array_push($results, $call[1]);
                }
                preg_match($currRegex, $call[0], $current);
                preg_match($lastRegex, $call[0], $last);
              }
            }
        }
      }
      return $results;
    }
    /**
     * POST function
     *
     * @param $url, $data
     *
     * @return array
     */
    public function post($url, $data = NULL) {
      if(is_null($data)) {
        throw new \ErrorException('No data supplied.');
      }
      $this->setURLData($url, json_encode($data));
      $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
      return $this->callAPI();
    }
    /**
     * PUT function
     *
     * @param $url, $data
     *
     * @return array
     */
    public function put($url, $data = NULL) {
      if(is_null($data)) {
        throw new \ErrorException('No data supplied.');
      }
      $this->setURLData($url, json_encode($data));
      $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
      return $this->callAPI();
    }
    /**
     * GET function
     *
     * @param $url, $data
     *
     * @return array
     */
    public function get($url, $data = NULL) {
      $this->setURLData($url . (!is_null($data) ? (((strpos($url, '?') !== false) ? '&' : '?') . http_build_query($data)) : ''));
      $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
      return $this->callAPI();
    }
    /**
     * Set the target URL and supplied data function
     *
     * @param $url
     * @param $data
     */
    private function setURLData($url, $data = NULL) {
      if(is_null($url)) {
        throw new \ErrorException('No target URL supplied.');
      }
      $this->urlPath = $url;
      $this->setOpt(CURLOPT_URL, $this->baseURL . $this->urlPath . ((strpos($url, '?') !== false) ? '&' : '?') . 'per_page=100');
      if(!is_null($data)) {
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
      }
    }
    /**
     * Set cURL Options function
     *
     * @param $option
     * @param $value
     */
    private function setOpt($options, $value = null) {
      if(is_array($options)) {
        foreach($options as $option => $value) {
          curl_setopt($this->curl, $option, $value);
        }
      } else {
        curl_setopt($this->curl, $options, $value);
      }
    }
  }
?>
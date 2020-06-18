<?php

// this class uses some functions from the php-project-framework library
require_once getPathIncLib()."php-project-framework/functions.php";
// however, it does not use any other functions or variables shared with the lti application
// this class can be used as stand alone with everything necessary to run passed to it in the constructor

class GuideData {
	
	// passed data
	private $course = array(); // also returned
	private $display = array();
	private $domain = "";
	private $siteID = "";
	private $apikey = "";
	private $ruleset = null;
    private $defaultSearchGroups = "";
	
	// return data
	private $queryInfo = [ "search" => "", "matched" => "", "rule" => "" ];
	private $stats = ["exec_ms" => 0, "api_requests" => 0];
	private $guides = array();
	private $subjects = array();
	private $profiles = array();
	private $databases = array();
    private $group = array();
	
	// processing data
	private $subjectIDs = array();
	private $excludeSubjects = "";
	private $mappingTable = array();
    private $overrides = array();
	
/*  ============================================================================================
    ********************************************************************************************
    CONSTRUCTOR 
	******************************************************************************************** 
*/
	
	function __construct($courseData, $displayParam, $domain, $siteID, $apikey, $ruleset, $excludeSubjects = "", $defaultSearchGroups = "") {
		logMsg("GUIDE DAO: Passed param: courseData",$courseData);
		logMsg("GUIDE DAO: Passed param: displayParam",$displayParam);
		
		$this->course = $courseData;
		$this->display = $displayParam;
		$this->domain = $domain;
		$this->siteID = $siteID;
		$this->apikey = $apikey;
		$this->ruleset = $ruleset;
        $this->excludeSubjects = $excludeSubjects;
        $this->defaultSearchGroups = $defaultSearchGroups;
	}
	 
	
/*  ============================================================================================
    ********************************************************************************************
    PUBLIC 
	******************************************************************************************** 
*/
	
    
    private function override($key) {
        return array_key_exists($key, $this->overrides);
    }
	/** ****************************************************************************************
	 *  init()
	 *
	 *	Initializes the object by triggering all the api requests necessary to fill in the 
	 *  data. Must be called after constructing otherwise the object is empty.
	 *
	 *  @see __construct()
	 *  @access public
	 *  @param none
	 *  @return type Description 
	 */
	public function init() {
        
        $this->checkOverrides();
		
		// set the info
		$this->queryInfo["search"] = $this->course['id'];
		$start = round(microtime(true) * 1000);
		
		if ( $this->course['dept'] !== "" ) {
            
            $this->course['dept_subject_id'] = $this->mapDeptToSubject($this->course['dept']);

        }

		// if we are going to display anything from LibGuides run the guide query (req for librarian and databases)
		if( $this->display['databases'] || $this->display['librarian'] || $this->display['guides'] ) {
	
            if ( $this->override("guide_id") ) {
                $this->guides = $this->requestCourseGuide();
            } else {
                // find related course guides (tagged with a code)
                $this->guides = $this->findCourseGuides($this->getCourse());
            }
			
            
            // TODO ###SUBJECTS###
            
            // ************************************************************
            // SUBJECTS
            
            $subjID_str = "";
            $logStr = "";
            if ( $this->override("subject_id") ) {
                $logStr = "OVERRIDE";
                $subjID_str = $this->overrides['subject_id'];
            } else if ( count($this->guides) ) {
                $logStr = "GUIDES";
                $excludeSubjRegex = "/^".str_replace(",", "|", $this->excludeSubjects )."$/";
                foreach ( $this->guides as $guide ) {
                    if ( isset( $guide['subjects'] ) ) { // there could be a guide without a subject so we'd skip it
                        foreach ($guide['subjects'] as $sub ) {
                            if ( !preg_match($excludeSubjRegex, $sub['id']) ) {
                                $subjID_str .= $sub['id'] . ",";
                            }
                        }                        
                    }
                }
                if ( $subjID_str !== "" ) { $subjID_str = substr($subjID_str, 0, -1); } // remove the trailing ,
            } else {
                $logStr = "DEPARTMENT";
                $subjID_str = isset($this->course['dept_subject_id']) ? $this->course['dept_subject_id'] : "";
            }
            if ( $subjID_str !== "" ) { 
                $this->subjects = $this->requestSubjects($subjID_str);
                logMsg("GUIDE DAO: Subject list obtained from ".$logStr.": subject ids: ".$subjID_str, $this->getSubjects());
            } else {
                logMsg("GUIDE DAO: There were no subjects found for this course, perhaps due to an override or other circumstance");
            }
            

            // ************************************************************
            // PROFILES
            
            $profileID_str = "";
            $logStr = "";
			// PART I: set the librarian either from the guide, override, or subject
            if( $this->display['librarian'] ) {
                if ( $this->override("librarian_id") ) {
                    // use the ids from the overrides
                    $logStr = "OVERRIDE";
                    $profileID_str = $this->overrides['librarian_id'];
                } else if ( count($this->guides) ) {
                    $logStr = "GUIDES";
                    // extract the librarian ids from the guides
                    foreach ($this->guides as $guide) {
                        $profileID_str .= $guide['owner_id'] . ",";
                    }
                    $profileID_str = substr($profileID_str, 0, -1); // remove the trailing ,
                } else {
                    // extract the profile from the subject
                    $logStr = "SUBJECTS";
                    // we do nothing, but drop through to the next evaluation (PART II)
                }
            }
            // PART II: if we obtained profile ids, use them, else get it from the subjects
            if ( $profileID_str !== "" ) {
                $this->profiles = $this->requestAccounts($profileID_str);
            } else if ( $subjID_str !== "" ) {
                $this->profiles = $this->requestLibrariansForSubj($subjID_str);
                foreach($this->profiles as $profile) {
                    $profileID_str .= $profile['id'] . ",";
                }
                $profileID_str = substr($profileID_str, 0, -1); // remove the trailing ,
            }
            if ( count($this->getProfiles()) ) {
                logMsg("GUIDE DAO: Profile list obtained from ".$logStr.": account ids: ".$profileID_str, $this->getProfiles());
            } else {
                logMsg("GUIDE DAO: No profiles to obtain either because no guides or subjects");
            }
            
            
            
            // ************************************************************
            // DATABASES
            
			// only grab databases if we are displaying section AND expanded AND we have subjects
			if ( $this->display['databases'] && $this->display['x-dbexpand'] && count($this->getSubjects() ) ) {
				$this->databases = $this->requestDatabasesForSubjects($subjID_str);
				logMsg("GUIDE DAO: Databases requested", $this->getDatabases());
			}

			
		}
		
		// set execution time
		$this->stats["exec_ms"] = round(microtime(true) * 1000) - $start;
	
	}
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getAll() {
		return ["info" => $this->getQueryInfo(), "course" => $this->getCourse(), "stats" => $this->getStats(), "guides" => $this->getGuides(), "subjects" => $this->getSubjects(), "databases" => $this->getDatabases(), "profiles" => $this->getProfiles()];
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getQueryInfo() {
        $temp = $this->queryInfo;
        $temp['subject'] = $this->getSubjects();
		return $temp;
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getGuides() {
		return $this->guides;
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getSubjects() {
		return $this->subjects;
	}

	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getCourse() {
        $temp = $this->course;
        $temp['subject'] = $this->getSubjects();
        
        if ( $this->override("librarian_id") ) { $temp['librarian_id'] = $this->overrides['librarian_id']; }
        if ( $this->override("group_id") ) { $temp['group_id'] = $this->overrides['group_id']; }
        if ( $this->override("subject_id") ) { $temp['subject_id'] = $this->overrides['subject_id']; }
        if ( $this->override("guide_id") ) { $temp['guide_id'] = $this->overrides['guide_id']; }
        if ( $this->override("tag") ) { $temp['tag'] = $this->overrides['tag']; }
        
		return $temp;
	}
    
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getDatabases() {
		return $this->databases;
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getProfiles() {
		return $this->profiles;
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getStats() {
		return $this->stats;
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getLibAppCourseSearchString($course = array(), $index = 0 ) {

		$t = array("c" => "", "q" => "");

		if (!isset( $course['dept']) || $course['dept'] === "XXXX") {
			$index = -1;
		} else {

			// The ruleset_libapps function is a customizable function
			// The default function is included in inc/ruleset-libapps-default.php
			// To customize the ruleset create a copy into private/app/custom/rulesets/ruleset-libapps-[version].php where [version] is your own verison ID
			$t = $this->ruleset->apply($index, $course);

			$index = $t['index'];

		}

		return [ "code" => $t['c'], "index" => $index, "q" => $t['q'] ];
	}
	
	
	/*  ============================================================================================
    ********************************************************************************************
    PRIVATE 
	******************************************************************************************** 
*/
	
		
	/** ****************************************************************************************
	 *  mapDeptToSubject()
	 * 
	 *  Given a department code, it returns the associated subject id
	 *
	 *  @access private
	 *  @param string $dept The department code to find a subject id for
	 *  @return string The subject id associated with the department code 
	 */
	private function mapDeptToSubject($dept = "XXXX") {	
	
		// hopefully there will be a day when we can get meta data directly from libguides api
		// for now we store mappings in a csv

		$subjID = "";
		
		if( count($this->mappingTable) === 0 ) {
			$this->importMappingTable();
		}

		if( array_key_exists($dept,$this->mappingTable) ) {
			
			$subjID = $this->mappingTable[$dept];
			
			logMsg("GUIDE DAO: Mapping Table ".$dept." to ". $subjID ." using:", $this->mappingTable);

		}

		return $subjID;

	}
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	
	private function importMappingTable() {
        $file = getPathCustom()."data/dept-subj-mapping.csv";
        
		if (($handle = fopen($file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle)) !== FALSE) {
				$key = $data[0];
				$value = $data[1];
				$this->mappingTable[$key] = $value;
			}
			fclose($handle);
		} else {
            logMsg("GUIDE DAO: Couldn't load Department-Subject mappings: ".$file);
        }
	}

    /** ****************************************************************************************
	 *  importLibGuideOverrides()
	 *
	 *
	 *
	 *  @access private
	 *  @return array data from override file 
	 */
	
	private function importLibGuideOverrides() {
        $data = array();
        $file = getPathCustom()."/data/override-libguides-search.json";
        
        $str_data = file_get_contents($file);
        $data = json_decode($str_data, true);
        
		if ( empty($data ) ) {
            logMsg("GUIDE DAO: Couldn't load overrides: ".$file);
        }
        
        return $data;
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function requestDatabasesForSubjects( $subjectIDs = "" ) {

		$uri = "/assets/";

		$params = array();
		$params['asset_types'] = "10";
		if ( $subjectIDs !== "" ) { $params['subject_ids'] = $subjectIDs; }

		return $this->getLibAppData($uri, $params);

	}

	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function requestSubjects($ids = "", $params = array() ) {

		$uri = "/subjects/".$ids;

		if ( $ids === "" ) { $params['guide_published'] = "2"; }

		return $this->getLibAppData($uri, $params);
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function requestLibrariansForSubj($subjID) {
		
		$params = array();
		$params["subject_ids"] = $subjID;
		
		return $this->requestAccounts("", $params);
	}


	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function requestAccounts($ids = "", $params = array()) {
        
		$uri = "/accounts/".$ids;
		$params["expand"] = ["profile","subjects"];

		return $this->getLibAppData($uri, $params);
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function findCourseGuides($course) {

		$g = array();
		$x = -1;

		do {

			$v = array();

			$x++;

			$v = $this->getLibAppCourseSearchString( $course, $x );

			if($v['index'] !== -1) {
				
				$g = $this->requestCourseGuide($v['q']);

				logMsg("Course Guides: Results For: ".$v['code']." w/ rule ".$v['index'], $g);

			}

			if(count($g)) {
				$x = -1; // guide was found
				logMsg("Course Guides: Found one or more guides w/ rule: ".$v['index'], $g);
				$this->queryInfo['matched'] = $v['code'];
				$this->queryInfo['rule'] = $v['index'];
			} else {
				$x = $v['index']; // current rule
				logMsg("Course Guides: Not found with rule: ".$x);
			}

		}  while ($x >= 0);

		return $g;

	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param array $params An array of key value pairs that will be sent to Springshare as parameters.
	 *  @return type Description 
	 */
	private function requestCourseGuide( $params = array() ) {
		
		$uri = "/guides/";
		
        // we either know a specific id or we do a search
        if ( $this->override("guide_id")) {
            $uri .= $this->overrides['guide_id'];
        } else {
           	// if these are not set, set them to defaults
            if (!isset($params['expand'])) { $params['expand'] = "owner,subjects,group"; }
            if (!isset($params['tag_names_match'])) { $params['tag_names_match'] = "1"; }
            if (!isset($params['status'])) { $params['status'] = "1"; }
            if (!isset($params['tag_names'])) { $params['tag_names'] = $this->getCourse()['id']; } // this should never happen

            // if we have defaults or overrides for group_ids then add the param. Note that we add default first, but it could be overriden right away
            if ( $this->defaultSearchGroups !== "" ) { $params['group_ids'] = $this->defaultSearchGroups; }
            if ( $this->override('group_id') ) { $params['group_ids'] = $this->overrides['group_id']; } 
        }

		return $this->getLibAppData($uri, $params);
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function getLibAppData($uri = "", $parameters = array()) {

		// this goes with every request
		$parameters['key'] = $this->apikey;
		$parameters['site_id'] = $this->siteID;

		// remove the trailing / (if there) there are no extra between the pieces (easier than checking and then adding if not)
		$endpoint = rtrim($this->domain,"/") . "/1.1/" . trim($uri, "/");
		
		$this->stats['api_requests']++;

		return generateJSONrequest($endpoint, $parameters);
	}
    
    
	/** ****************************************************************************************
	 *  checkOverrides()
	 *
	 *  We may wish to override some libguide api searches depending on certain values passed 
     *  from the LMS. This checks the private/app/custom/data/override-libguides-search.json file for 
     *  criteria and values used in overrides.
	 *
	 *  @access private
	 */
    
    private function checkOverrides() {
        // load defaults from emulator-test.json document
        $data = $this->importLibGuideOverrides();
        
        logMsg("GUIDE DAO: Loaded overrides", $data);
        
        $override = false;
        // loop through criteria and see if we have a match. Criteria may be a single string, a comma deliminated string, or regex. Detect.
        // the rules are processed in order as AND
        // once there is a criteria match the loop stops (so the json file elements must be placed in priority order)
        $c = count($data);
        $i = 0;
        while (!$override && $i < $c) {
            
            logMsg("GUIDE DAO: Checking override ruleset ".$i." of ".$c, $data[$i]);
            
            $criteriaCount = 0;
            $matchCount = 0;
            
            foreach( $data[$i]["criteria"] as $key => $criteria) {
                
                // get the value of the criteria variable
                $passedValue = getParameter($key); // we only check against parameter values
                
                if ($passedValue !== null) { // don't bother matching, no criteria to match against, we'll end up breaking out once we hit the no match if block
                    
                    $type = "string";

                    // peek at the criteria value and determine if it is a string, regex, or comma separated
                    if ( isRegex($criteria) ) { $type = "regex"; } 
                    else if ( strpos($criteria, ",") !== false ) { $type = "comma"; }
                    
                    logMsg("GUIDE DAO: Override evaluating [".$key."]: if ".$criteria." matches ".$passedValue." (".$type.")"); 
                    
                    // perform the appropriate evaluation of the criteria
                    switch($type) {
                        case "regex": // it is a regex
                            if(preg_match($criteria, $passedValue)) { $matchCount++; }
                            break;
                        case "comma": // it is a comma list
                            $cArr = explode(",",$criteria);
                            if(in_array($passedValue, $cArr)) { $matchCount++; }
                            break;
                        default: // it is a string
                            if($passedValue === $criteria) { $matchCount++; }
                    }
                }
                
                $criteriaCount++;
                
                // no match this round, therefore quit. Since it is an AND all rounds must have a match
                if ( $criteriaCount !== $matchCount) {
                    break; // no use going on in this ruleset, so we break out of the foreach and return to the while to check another ruleset
                }
            } // finished checking all criteria within a ruleset
            
            // now that we've checked the criteria, lets see if our ruleset is a match
            if ($criteriaCount > 0 && $criteriaCount === $matchCount) { 
                $override = true; // we found a matching ruleset
            } else {
                $i++; // try next ruleset
            }
        } // finished checking all rulesets
        
        // set the override variables, we'll perform checks as we do api queries
        if ($override) {
            logMsg("GUIDE DAO: Found an override ruleset", $data[$i]['criteria']);
            
            $this->queryInfo['override_rule'] = $i;
            
            // we only want non-blank values so we can do an easy array_key_exists() when performing api calls
            foreach ( $data[$i]['replacement_values'] as $key => $value ) {
                if ($value !== "") {
                    $this->overrides[$key] = $value;
                }
            }            
            
            logMsg("GUIDE DAO: Override values", $this->overrides);
        } else {
            logMsg("GUIDE DAO: No overrides found for this course.");
        }
        
    }

}
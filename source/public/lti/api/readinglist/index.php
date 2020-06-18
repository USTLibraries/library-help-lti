<?php
/*  ============================================================================================
	********************************************************************************************

	Library Help LTI | University of St. Thomas | stthomas.edu/libraries
	github.com/ustlibraries

	api/readinglist/index.php
	File Version: 20190223-0940

	********************************************************************************************
	
	This script is called directly by the LTI to provide an api for accessing the readinglist.
	
	Example:
	GET https://example.com/lti/api/readinglist/?course=201940ENGR400-X7
	
	Using the class ReadingListData and the customizable ReadingListRuleSet it does a search
	to find a matching course list, returning it in JSON format.
	
	Not only does the script search using different permutations of the course code (stripping
	off the year, term, section, etc.) trying everything from 201940ENGR400-X7 all the way down
	to ENGR, it gathers the separate course, readinglist, and citations from Alma and aggregates
	them into a single, simplified list, displaying basic course info, list sections, and
	citations.
	
	In addition to being used by the LTI, it may be called as an api for integration into other
	systems such as student portals and other authenticated systems. For this an API key will
	need to be set up, stored in the config.ini.php file, and used to access the api.
	
	============================================================================================
*/


/*  ============================================================================================
    ********************************************************************************************
    INCLUDES 
	******************************************************************************************** 
*/

// this is required to be placed at start of execution
require_once __DIR__."/../../inc/inc.php";
// it loads the config, app vars, core app functions, and init

// this is the class that accesses and returns the readinglist data from the ALMA api
require_once getPathIncApp()."class-dao-readinglist.php";

// this brings in the class that applies the rulesets to search by
require_once getRuleSetLocation("reading");


/*  ============================================================================================
    ********************************************************************************************
    LOCAL PAGE VARIABLES 
	******************************************************************************************** 
*/
//  -- NONE --


/*  ============================================================================================
    ********************************************************************************************
    LOCAL PAGE FUNCTIONS 
	******************************************************************************************** 
*/

/** ********************************************************************************************
 *  generate($courseData)
 *
 *	Performs the duties of this page and is called during execution
 *
 *	This takes course information and passes it along with config settings to the ReadingListData
 *	object. It then returns data from the request.
 *
 *  @param array $courseData An array that was created by setCourse() from app-functions.php
 *  @return array The data array returned by the ReadingListData object
 */ 
function generate($courseData) {
	
	// get alma config values as well as the ruleset
	$alma = getCfg("alma"); 
	$ruleset = new ReadingListRuleSet();
	
	// construct a ReadingListData object with the required parameters
	$rObj = new ReadingListData($courseData, $alma['apiDomain'], getCfg('app-secrets')['alma']['apiKey'], getCfg("alma"), $ruleset, getCfg("header")['timezone']);

	// use the settings and make the requests for data
	$rObj->init();
	
	// get all the data gathered by the object in one array
	$r = $rObj->getAll();
	
	logMsg("Reading List request for ".$courseData['id'], $r);
	
	return $r;
}


/*  ============================================================================================
    ********************************************************************************************
    PAGE BODY - EXECUTION BLOCK 
	******************************************************************************************** 
*/

// this is the execution block, only executed if it is an approved origin (javascript) OR authorized by ip or api key (server to server)
if( isApprovedOrigin(TRUE) || authorizeAPIcall() ) {
	
	setCourse();
	
	$json = array();

	// call the local function generate() to deal with the data handling
	$json = generate( getApp("course"));
	
	// as long as we are not in debug mode, return data as JSON
	if(!$app['debug']) {
		
		httpReturnHeader(getCacheExpire("api"), getRequestOrigin(), "application/json");
		echo json_encode($json);
		
	} else { // we are in debug mode so display an information page
		echo "<h3>JSON RAW</h3>";
		echo "<p>";
		echo json_encode($json);
		echo "</p>";
		echo "<h3>JSON FORMATTED</h3>";
		echo "<pre>";
		print_r($json);
		echo "</pre>";
		appExecutionEnd();
	}

}

?>
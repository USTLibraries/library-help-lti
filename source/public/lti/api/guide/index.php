<?php
/*  ============================================================================================
	********************************************************************************************

	Library Help LTI | University of St. Thomas | stthomas.edu/libraries
	github.com/ustlibraries

	api/guide/index.php
	File Version: 20190223-0940

	********************************************************************************************
	
	This script is called directly by the LTI to provide an api for accessing guide information.
	
	Example:
	GET https://example.com/lti/api/guide/?course=201940ENGR400-X7
	
	Using the class GuideData and the customizable GuideRuleSet it does a search
	to find a matching guides, subjects, databases, and profiles, returning data in JSON format.
	
	Not only does the script search using different permutations of the course code (stripping
	off the year, term, section, etc.) trying everything from 201940ENGR400-X7 all the way down
	to ENGR, it gathers the separate guides, subjects, databases, and profiles from Springshare
	LibApps and aggregates them into a single, simplified dataset.
	
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
// this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init

// this is the class that accesses and returns the guide data from the LibApps api
require_once getPathIncApp()."class-dao-guide.php";

// this brings in the class that applies the rulesets to search by
require_once getRuleSetLocation("libapps");


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


// generate() performs the duties of this page and is called during execution
function generate($courseData, $displayParam) {
	
	// get LibApps config values as well as the ruleset
	$libapps = getCfg("libapps");
	$ruleset = new GuideRuleSet();
	
	$gObj = new GuideData($courseData, $displayParam, $libapps['apiDomain'], $libapps['siteID'], getCfg('app-secrets')['libapps']['apiKey'], $ruleset, $libapps['subjexclude']);

	$gObj->init();
	
	$r = $gObj->getAll();
	
	logMsg("Guide request for ".$courseData['id'], $r);
	
	return $r;
}

/*  ============================================================================================
    ********************************************************************************************
    PAGE BODY - EXECUTION BLOCK 
	******************************************************************************************** 
*/

// this is the execution block, only executed if it is an approved origin (javascript) or authorized by ip or api key
if( isApprovedOrigin(TRUE) || authorizeAPIcall() ) {
	
	setCourse();
	setDisplayParameters();
	
	$json = array();

	$json = generate( getApp("course"), getApp("display"));
	
	if(!$app['debug']) {
		
		httpReturnHeader(getCacheExpire("api"), getRequestOrigin(), "application/json");
		echo json_encode($json);
		
	} else {
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
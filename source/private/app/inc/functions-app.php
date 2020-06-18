<?php
/*
===============================================================================
*******************************************************************************
LIBRARY RESOURCE INTEGRATION: Functions for Application
*******************************************************************************

	University of St. Thomas Libraries (www.stthomas.edu/libraries)
	Version: 0.3.0-20190807-1040
	github.com/ustlibraries

-------------------------------------------------------------------------------

	FILE LAST MODIFIED: 2019-08-07 - clkluck

	PURPOSE: Core functions for application

-------------------------------------------------------------------------------

This is a function template file from the PHP PROJECT FRAMEWORK library.
Visit github.com/chadkluck/php-project-framework page for more information.
FRAMEWORK FILE: private/app/inc/functions-app.php
FRAMEWORK FILE VERSION: 2020-04-20

*******************************************************************************
===============================================================================
*/

/*
===============================================================================
*******************************************************************************
APP FUNCTIONS
*******************************************************************************

Add any app-wide functions below.
Add any app-wide runtime init routines in inc-app.php

*******************************************************************************
*/


require_once(__DIR__."/func-toolkit.php");

$tk = new ToolKit();


/* **************************************************************************
 *  FORCE SETUP
 *
 *  If mandatory fields in the config.ini.php file are not filled in, direct to setup
 */

function requireSetup() {

	// check to make sure we aren't on setup.php already (otherwise an infinate redirect)
	// and if any of the required config fields are blank, redirect to setup.php

	if ( basename($_SERVER['PHP_SELF']) !== "basic-setup-10-keys.php" && basename($_SERVER['PHP_SELF']) !== "login.php" && (
		// list all that should be required before even testing is allowed
		getCfg('secrets')['password-hash'] === ""
		|| getCfg('lti')['oauth_clientid'] === ""
		|| getCfg('app-secrets')['lti']['oauth_secret'] === ""
		|| ( getCfg('secrets')['key-store'][0] === "" || strlen(getCfg('secrets')['key-store'][0]) < 32 )
		|| ( getCfg('secrets')['key-store'][1] === "" || strlen(getCfg('secrets')['key-store'][1]) < 32 )
		|| ( getCfg('secrets')['key-store'][2] === "" || strlen(getCfg('secrets')['key-store'][2]) < 32 )
	    || ( getCfg('secrets')['key-store'][3] === "" || strlen(getCfg('secrets')['key-store'][3]) < 32 )
	    ) ) {

		header("Location: ".getBaseURL()."/tools/basic-setup-10-keys.php");
		exit();
	}
}

/* **************************************************************************
 *  FORCE LOGIN
 *
 *  If it is a page that requires login, redirect to the login page (and
 *  after login send them to the page they originally requested)
 */

function requireLogin() {

	session_start();

	if ( !isset($_SESSION['lti-admin']) ) {

		if ( basename($_SERVER['PHP_SELF']) !== "login.php" && getCfg('secrets')['password-hash'] !== "" ) {

			$redirectBackTo = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

			header("Location: ".getBaseURL()."/tools/login.php?r=".$redirectBackTo);
			exit();
		}

	}


}

/*  ============================================================================================
    ********************************************************************************************
    COURSE IDENTIFICATION
	********************************************************************************************
*/

function setCourse() {

	global $app;

	if ( !isset( $app['course'] )) {

		$courseCode = "XXXX";
		if ( getParameter("course") ) {
			$courseCode = getParameter("course"); // because saved custom settings (and /module/getlink) use "course" and will override
		} else {
			if ( getParameter("course_id") ) {
				$courseCode = getParameter("course_id"); // when passed from LMS this overrides context label
			} else if ( getParameter("context_label") ) {
				$courseCode = getParameter("context_label"); // this is the LMS default, context_label
			}
		}

		// courseCode cannot have spaces or bad characters - added 2020-03-03 clkluck
		$courseCode = preg_replace("/[^\w-]/", "", $courseCode); // leave only A-Za-z0-9 - and _

		setSessionParam("course", $courseCode);
		$app['course'] = courseProperties($courseCode);
		$app['course']['instructor'] = getInstructor();

	}

}

/**
 * Get the instructor for the course.
 */
function getInstructor() {
	global $app;

	$instructor = "";

	if ( !isset($app['course']['instructor']) || $app['course']['instructor'] === "" ) {
		$instructor = getParameter("instructor", "SESSION");
	}

	return $instructor;
}

/**
 * Separate out the courseID into its various parts. If no course ID is passed
 * then it uses the course ID already in app memory ( getApp("course") )
 *
 *  SIS ID FORMAT = YYYYTTDDDDCCC-SS (year, term, department, course, section)
 *    201740ACCT210-02
 *    201740ACCT316-D02
 *    201740ACCT495-I1
 *    201740ACCT565-721
 *
 *  But it will take other formats too such as
 *    ACCT892-LAMBERT (Department, Course #, Instructor (stand in for section))
 *    ACCT389         (Department, Course #)
 *    ACCT            (Department)
 *    ACCT-LAMBERT    (Department, Instructor (stand in for section))
 *    ACCT156-02      (Department, Course #, Section)
 *
 */

function courseProperties($courseID = "") {

	global $app;

	$arr = [ "id" => "",
		 "termcode" => "",
		 "year" => "",
		 "term" =>"",
		 "dept" =>"",
		 "num" =>"",
		 "section" => "",
		 "instructor" => "",
		 "termdesc" => "",
		 "subject" => array()
	   ];

	if ($courseID === "") {
		if ( isset($app['course']) ) { $arr = $app['course']; } // either it is already set, or blank. Hey, they didn't send it in a param
	} else {
		$courseID = strtoupper($courseID);
		$arr['id'] = $courseID;

		/* If it is a valid university course regex then parse it out. Otherwise we stick with only setting the id above
		   Allowing a non-valid courseID is useful for non-traditional courses, those that aren't in the catalog.
		   Like sandbox courses, testing, adHoc groups, or other groups who may wish to have a course or community set up
		   with corresponding LibGuides and Reading Lists. Use the full ID to tag reading lists and libguides
		*/
		if( preg_match( "/".getCfg("univ")['regex']['course']."/", $courseID ) ) {
			if( preg_match("/".getCfg("univ")['regex']['year']."/", $courseID, $matches, PREG_OFFSET_CAPTURE)) {
				$arr['year'] = $matches[0][0];
			}
			if( preg_match("/".getCfg("univ")['regex']['term']."/", $courseID, $matches, PREG_OFFSET_CAPTURE)) {
				$arr['term'] = $matches[0][0];
			}
			if( preg_match("/".getCfg("univ")['regex']['dept']."/", $courseID, $matches, PREG_OFFSET_CAPTURE)) {
				$arr['dept'] = $matches[0][0];
			}
			if( preg_match("/".getCfg("univ")['regex']['crsnum']."/", $courseID, $matches, PREG_OFFSET_CAPTURE)) {
				$arr['num'] = $matches[0][0];
			}
			if( preg_match("/".getCfg("univ")['regex']['section']."/", $courseID, $matches, PREG_OFFSET_CAPTURE)) {
				$arr['section'] = $matches[0][0];
			}
			if( $arr['year'] && $arr['term'] ) { $arr['termcode'] = $arr['year'].$arr['term']; }
			if( $arr['term'] ) { $arr['termdesc'] = getDescForTerm($arr['term']); }
		}


	}

	// instructor is not currently set as there is not a solid reason to set it here. In the ruleset section is interchangable with instructor so the section is fine
	// instructor is set in the setCourse() function if access to the LMS API is available. This only parses the data contained in the courseID

	return $arr;

}



function getDescForTerm($term) {

	$val = "";

	$regEx = "/^".$term.",/";
	$matches = preg_grep($regEx, getCfg("univ")['termdesc'] );

	$row = explode(",", array_pop($matches));
	// we use array_pop because the index is the same as it was in the previous array, not 0 (unless it was first). We expect only 1 anyway

	if( isset($row[1])) {
		$val=$row[1];
	}

	return $val;

}

function getTestCourses() {
	return preg_split("/\\r\\n|\\r|\\n/", getCfg("univ")["test"]);
}


/*  ============================================================================================
    ********************************************************************************************
    CRYPTO, HASHING, SIGNING, and VERIFICATION
	********************************************************************************************
*/

/* **************************************************************************
 *  GENERATE PASSWORD
 *  This application is built for php 7 so the newest cryptographically secure
 *  password hasher is available.
 *  If building for php 5.4 then we recommend including a library from ircmaxwell
 *  https://github.com/ircmaxell/password_compat
 */


function generatePasswordHash($plaintext) {

	$hash = "";

	if ($plaintext !== "") {
		$hash = password_hash($plaintext, PASSWORD_DEFAULT);
	}

	return $hash;
}

function verifyPassword($plaintext, $hash = "") {


	if ($hash === "") {
		$hash = getCfg('secrets')['password-hash'];
	}

	return password_verify($plaintext, $hash);
}

/* **************************************************************************
 *  GENERATE A PASSWORD - RANDOM STRING FOR HUMAN
 *  It is named this for a reason. It should not be used for cryptographic keys that don't need to be read or typed in by human hands
 */

function generatePassword($length = 64) {
	global $tk;
	return $tk->generatePassword($length);
}

function generateKey($bytesLen = 128) {
	global $tk;
	return $tk->generateKey($bytesLen);
}

// like a key but only alphanumeric
function generateToken($length = 64) {
	global $tk;
	return $tk->generateToken($length);
}

function generateHex($bytesLen = 128) {
	global $tk;
	return $tk->generateHex($bytesLen);
}

function generateString($alphabet, $length) {
	global $tk;
	return $tk->generateString($alphabet, $length);
}

function encrypt($plaintext, $key) {
	global $tk;
	return $tk->encrypt($plaintext, $key);
}

function decrypt($encrypted, $key) {
	global $tk;
	return $tk->decrypt($encrypted, $key);
}

/* **************************************************************************
 *  SELECT A RANDOM INT
 *  This application is built for php 5.4 so the newest cryptographically secure
 *  random number generator is not yet available until php 7.
 *  In the meantime we are using a library from paragonie
 *  https://github.com/paragonie/random_compat
 *  That library is stored in inc/random_compat/
 */

function selectRandom($upper = 255) {
	global $tk;
	return $tk->selectRandom($upper);
}

/* **************************************************************************
 *  VALIDATE OAUTH
 *
 */
function validateOAuth() {

	global $tk;

	return $tk->validateOAuth(getCfg('lti')['oauth_clientid'], getCfg('app-secrets')['lti']['oauth_secret']);
}

/* **************************************************************************
 *  GENERATE OAUTH REQUEST
 *  Returns a string for an authorization header and an array of oauth parameters including the signature
 */
function generateOAuth($params, $url) {

	global $tk;

	return $tk->generateOAuthSignature(getCfg('lti')['oauth_clientid'], getCfg('app-secrets')['lti']['oauth_secret'], $params, $url);
}

/* **************************************************************************
 *  DATA SIGNATURES
 *  Sign a string or array of data for things like forms and sessions
 *  As the secret key we are using the 0 index of the key-store
 *  These are the only crypto functions that should use this key-store index
 *  All other crypto functions should be using one of the other three
 */
function generateDataSignature($data) {

	global $tk;

	return $tk->generateDataSignature($data, getCfg('secrets')['key-store'][0]);
}

function validateDataSignature($data, $signature) {

	global $tk;

	return $tk->validateDataSignature($data, $signature, getCfg('secrets')['key-store'][0]);
}

function generateTimestamp() {
	global $tk;

	return $tk->timestamp();

}

/*  ============================================================================================
    ********************************************************************************************
    PAGE HELPERS
	********************************************************************************************
*/

/* **************************************************************************
 *  EXPLODE LINK INFO
 *
 *  Sometimes, as in the config.ini we don't store links in a multi dimentional array
 *  (Though the list of links might find itself placed within a multi dimentional array, each link is its own row)
 *
 *  For Example:
 *  $arr['list_of_links']['science_links'][0] = "[[URL:http://some-science.com]],[[TEXT:Sciency Stuff]]"
 *  $arr['list_of_links']['science_links'][1] = "[[URL:http://some-biology.com]],[[TEXT:Biology Stuff]]"
 *
 *  This will explode an individual row and return:
 *  $myLink = explodeLinkInfo($arr['list_of_links']['science_links'][1])
 *  echo print_r($myLink);
 *
 *  Will output:
 *  Array
 *  (
 *    [url] => http://some-biology.com
 *    [text] => Biology Stuff
 *  )
 *
 *  Note that it does not explode a whole array list, only a single element passed to it via index
 *  Though it could be incorporated into a loop to create a new list of exploded items
 *
 *  This can be use with any data in format: [[URL:http://some-url.com]],[[TEXT:Some text for link]]
 */

function explodeLinkInfo($linkRow) {
	$data = array();
	$arr = explode("]],[[", $linkRow);

	if(count($arr) == 2) {
		$data['url'] = str_replace("[[URL:", "", $arr[0]); // don't need regex, it's a url, [[URL: should not appear anywhere else. Key words: Should Not
		$data['text'] = preg_replace("/^TEXT:|\]\]$/", "", $arr[1]); // remove TEXT: at begining and ]] at end
	}

	return $data;
}

/*  ============================================================================================
    ********************************************************************************************
    GET BASE URL
	********************************************************************************************
	  For things like stylesheets and scripts we need to let the html know where the app resides.
	  For example: https://somedomain.com/mypath/lti/module/display/
	  Would be: https://somedomain.com/mypath/lti
*/

function getBaseURL() {

	// starting inner to outer :
	// 1. get the REQUEST_URI, explode by ? and take the first part (effectively removing the query string if there is one)
	// 2. if there is a trailing / remove it with rtrim()
	// 3. if there is a preceeding / remove it with ltrim()
	// 3. explode the path into it's separate directories
	$array = explode("/",ltrim(rtrim(explode("?", $_SERVER['REQUEST_URI'])[0], '/'), '/'));

	// reverse to truncate after last lti (in case https://somedomain.com/something/lti/morestuff/lti/module)
	$i = array_search("lti", array_reverse($array));

	$path = implode("/", array_slice($array, 0, (count($array) - $i) ) );
	$url = "https://". $_SERVER['SERVER_NAME'] . "/" . rtrim($path, '/');

	return $url;
}

function getCSSdirectoryUrl() {
	return getBaseURL()."/assets/css-js";
}

function getAPIdirectoryUrl() {
	return getBaseURL()."/api";
}

function getJSdirectoryUrl() {
	return getBaseURL()."/assets/css-js";
}

function getCustomDirectoryUrl() {
	return getBaseURL()."/assets/custom";
}

/*  ============================================================================================
    ********************************************************************************************
    AWS INTEGRATION
	********************************************************************************************
*/

/*  **************************************************************************
	* Check to see if AWS is set up
    */

function awsIsSet() {

	$r = false;

	if ( getCfg("aws")['enabled'] ) {
		$r = true;
	}

	return $r;
}


/*  ============================================================================================
    ********************************************************************************************
    LMS VALIDATION AND AUTHORIZATION
	********************************************************************************************
*/

/*  **************************************************************************
	* Check to see is it was validly called by the LMS
    */
function calledByLMS() {
	// Was it done via POST and is it passing the right OAuth signature? OR Has an authenticated session already been created?
	return ( ( $_SERVER['REQUEST_METHOD'] === 'POST' && validateOAuth() ) || (isset($_SESSION['calledByLMS']) && $_SESSION['calledByLMS'] === true ) );
}

/*  **************************************************************************
	* Check to see if the current user has the Admin role in the LMS
	*/
function userIsAdmin() {
	// the types of roles valid for what we will classify as an editor
	return isUserA("/Administrator/");
}

/*  **************************************************************************
	* Check to see if the current user has ContentDeveloper role in the LMS
	*/
function userIsContentDeveloper() {
	// the types of roles valid for what we will classify as an ContentDeveloper
	return isUserA("/ContentDeveloper/");
}

/*  **************************************************************************
	* Check to see if the current user has some sort of course management role in the LMS
	*/
function userIsEditor() {
	// the types of roles valid for what we will classify as an editor
	return isUserA("/Administrator|Instructor|ContentDeveloper/");
}

/*  **************************************************************************
	* Helper function for all the userIs___() funcitons
	*/
function isUserA($regex) {
	// first check to see if the call by the LMS was valid, then check the role
	return ( calledByLMS() && preg_match($regex, getParameter("roles") ) === 1 );
}


/*  ============================================================================================
    ********************************************************************************************
    RULESETS
	********************************************************************************************

	These are rule sets for logically searching the Alma and LibApp API for matching pages/lists
	There is a default ruleset for each (rulset-libapps-default.php and ruleset-reading-default.php)
	in the inc/ directory.

	While someone could say including a file with php script from a custom directory is a
	security risk as someone could put malicious code in our custom php file, it is important to
	note that the only way that file got there was through placing it on the server since there is
	no web UI provided by LTI for modifying files. So, if someone has server access to place items
	in the custom/ directory, they already have access to this function.php file and others in
	which they could place malicious code. So, in context, this is not actually a security risk.

	Why is the ruleset set up outside of this function.php file? Because it is ill-advised to modify
	or customize this code base as any updates/enhancements brought in from the developer will
	overrite any customization. Great care has been taken to allow customizable elements to be
	placed in the custom/ directory. Therefore, rulesets, since the logic is quite possibly something
	that is hard to apply to every conceivable instance, we allow the option to customize.
*/

function getRuleSetLocation($ruleset = "libapps" ) {

	$file = "";

	if ( hasData( getCfg("rulesets")[$ruleset]) ) {
		$file = getPathCustom()."rulesets/ruleset-".$ruleset."-".getCfg("rulesets")[$ruleset].".php";
	} else {
		$file = getPathCustom()."ruleset-".$ruleset."-default.php";
	}

	logMsg("Using ".$ruleset." ruleset: ".$file);

	return $file;
}





/*  ============================================================================================
    ********************************************************************************************
    CHECKS
	********************************************************************************************
*/

function hasA($item) {
	global $app;

	return ( isset($app[$item]) && count($app[$item]) > 0 );

}

function hasGuides() {
	return hasA("guides");
}

function hasLibrarian() {
	return hasA("librarian");
}

function hasCourseMaterial() {
	return hasA("coursematerials");
}

function hasSubjects() {
	return hasA("subjects");
}

function hasDatabases() {
	return hasA("databases");
}




//might not be used anymore
function searchSubArray(Array $array, $key, $value) {
    foreach ($array as $subarray){
        if (isset($subarray[$key]) && $subarray[$key] == $value)
          return $subarray;
    }
}




/*  ============================================================================================
    ********************************************************************************************
    FUNCTIONS FOR PRE-PAGE PROCESSING
	********************************************************************************************
*/

function loadDisplayParameters( $arr = array() ) {
	// set the session variables, even if they will be overriden
	setSessionParam("cdp", "1"); // TODO do we?
	setSessionparam("course", $arr['course']);
	setSessionParam("displayIntro", $arr['displayIntro']);
	setSessionParam("displayDiscovery", $arr['displayDiscovery']);
	setSessionParam("displayGuides", $arr['displayGuides']);
	setSessionParam("displayCourseMaterial", $arr['displayCourseMaterial']);
	setSessionParam("displayDatabases", $arr['displayDatabases']);
	setSessionParam("displayLibrarian", $arr['displayLibrarian']);
	setSessionParam("displayChat", $arr['displayChat']);
	setSessionParam("x-dbexpand", $arr['x-dbexpand']);
}

/* *************************************************************
 *  SET DISPLAY PARAMETERS
 *
 */
function setDisplayParameters() {

	global $app;

	// if cdp (Custom Display Parameters) was sent via POST or GET set it in the Session (if loaded in from a saved preference, SESSION should already be set)
	if (getParameter("cdp", "POST") || getParameter("cdp", "GET")) {
		setSessionParam("cdp", getParameter("cdp"));
	} else {
		setSessionParam("cdp", 0);
	}

	setCourse();

	// Set Defaults from Config or, if CFG allows, override
	$app['display']['intro']          = overrideDisplaySetting( "displayIntro", "intro" );
	$app['display']['discovery']      = overrideDisplaySetting( "displayDiscovery", "discovery" );
	$app['display']['guides']         = overrideDisplaySetting( "displayGuides", "guides" );
	$app['display']['coursematerial'] = overrideDisplaySetting( "displayCourseMaterial", "coursematerial" );
	$app['display']['databases']      = overrideDisplaySetting( "displayDatabases", "databases" );
	$app['display']['librarian']      = overrideDisplaySetting( "displayLibrarian", "librarian" );
	$app['display']['chat']           = overrideDisplaySetting( "displayChat", "chat" );

	$app['display']['x-dbexpand'] = overrideSetting( "x-dbexpand", getCfg('sections')['databases']['dbexpand'], getCfg('sections')['databases']['dbexpandAllowOverride'] );

	if( getParameter("css_common") !== "" && getCfg('lti')['useLMScss'] ) {
		$app['css_common'] = getParameter("css_common");
	}

}

/* *************************************************************
 *  Override Logic
 *
 *  Figures out if a parameter is expected to be passed via GET/POST
 *  and if it is allowed to be overriden, what it should be
 *
 */

function overrideSetting( $pName, $default, $allow ) {
	$value = -1;

	$cdp = boolParamIsTrue( "cdp" );
	$isEmpty = (boolParamIsEmpty( $pName, "POST" ) && boolParamIsEmpty( $pName, "GET" ));

	// if cdp=1 (true) then check if a POST or GET is empty. If they are empty that means they evaluate to false
	if ( $cdp && $allow && $isEmpty ) { // if settings were sent via post/get AND override is allowed but the param is empty we treat it as a Do Not Display
		$value = 0;
	} else if ( $cdp && $allow ){ // if settings were sent via post/get AND override is allowed, after falling through for an empty param, we set it to the param value
		$value = boolParamEval($pName);
	} else if ( $allow && !boolParamIsEmpty( $pName, "SESSION" ) ) { // falling through the previous, if override allowed and SESSION param is not empty, set it to session param
		$value = boolParamEval( $pName, "SESSION" );
	} else {
		$value = $default; // No session, no post/get override, so just use default
	}

	setSessionParam( $pName, $value);

	return $value;
}

function overrideDisplaySetting( $pName, $sName ) {


	$allow = getCfg('sections')[$sName]['allowOverride'];
	$default = getCfg('sections')[$sName]['display'];

	$value = overrideSetting($pName, $default, $allow);

	return $value;
}

/*  ============================================================================================
    ********************************************************************************************
    GENERATE CONTENT HTML
	********************************************************************************************
*/


function getGuidesHTML() {
	// DISPLAY RESEARCH GUIDES

	global $app;

	$html = "";

	if ( $app['display']['guides'] ) {

		$section = getCfg('sections')['guides'];

		if ($section['heading']) { $html .= "<h2>".$section['heading']."</h2>\n"; }
		$html .= getCustomHTML($section['prehtml']);
		$html .="\n<!-- DISPLAY GUIDES -->\n\n";
		$html .="\n<div id='lib-section-guides' class='content-block show'>\n";
		$html .= "\n<ul>\n";

		if ( hasGuides() ) {

			$guides = getApp("guides");

			// sort the guides alphabetically
			usort($guides, function ($a, $b) {
				return strtolower($a['name']) <=> strtolower($b['name']);
			});

			$l = count($guides);

			for ($i=0; $i < $l; $i++) {
				$html .= "<li><a href='".$guides[$i]['url']."' data-category='Guides' data-label='GUIDE: ".$guides[$i]['name']."' class='external'>".$guides[$i]['name']."</a></li>\n";
			}


			$html .= "\n";
		} else {

			$html .="\n<!-- NO GUIDES TO DISPLAY SO SHOW DEFAULT -->\n\n";

			if( count(getApp("subjects") ) ) {
				$subjects = getApp("subjects");
                foreach ( $subjects as $subject ) {
                    $html .= "<li><a href='".getCfg('libapps')['libguides']."/sb.php?subject_id=".$subject['id']."' target='_blank' data-category='Guides' data-label='SUBJPAGE: ".$subject['name']."' class='external'>Subject page for ".$subject['name']."</a></li>\n";
                }
			} else {
				$noguides = getCfg('noguides')['link'];
				$l = count($noguides);

				for ($i=0; $i < $l; $i++) {
					$temp = explodeLinkInfo($noguides[$i]);
					$html .= "<li><a href='".$temp['url']."' target='_blank' data-category='Guides' data-label='NOGUIDE: General' class='external'>".$temp['text']."</a></li>\n";
				}
			}

		}

		$html .= "</ul>\n";
		$html .= getCustomHTML($section['posthtml']);
		$html .="\n</div>\n";
		$html .= "\n";

	}

	return $html;
}

function getDiscoveryHTML() {
	// DISPLAY SEARCH BOX

	global $app;

	$html = "";

	if ($app['display']['discovery']) {

		$section = getCfg('sections')['discovery'];

		$html .="\n<!-- DISCOVERY -->\n\n";
		$html .="\n<div id='lib-section-discovery' class='content-block show'>\n";
		if ($section['heading']) { $html .= "<h2>".$section['heading']."</h2>\n"; }
		$html .= getCustomHTML($section['html']);
		$html .="\n</div>\n";
		$html .= "\n";

	}

	return $html;
}

function getChatHTML() {
	// DISPLAY CHAT

	global $app;

	$html = "";

	if ($app['display']['chat']) {

		$section = getCfg('sections')['chat'];

		$html .="\n<!-- CHAT -->\n\n";
		$html .="\n<div id='lib-section-chat' class='content-block show'>\n";
		if ($section['heading']) { $html .= "<h2>".$section['heading']."</h2>\n"; }
		$html .= getCustomHTML($section['html']);
		$html .="\n</div>\n";
		$html .= "\n";

	}

	return $html;
}

function getIntroHTML() {
	// DISPLAY INTRO

	global $app;

	$html = "";

	if ($app['display']['intro']) {

		$section = getCfg('sections')['intro'];

		$html .="\n<!-- INTRODUCTION -->\n\n";
		$html .="\n<div id='lib-section-introduction' class='content-block show'>\n";
		if ( $section['heading'] ) { $html .= "<h2>".$section['heading']."</h2>\n";	}
		$html .= getCustomHTML($section['html']);
		$html .="\n</div>\n";
		$html .= "\n";

	}

	return $html;
}

function getCourseMaterialHTML() {


	global $app;

	$html = "";

	if ( $app['display']['coursematerial'] ) {

		$section = getCfg('sections')['coursematerial'];

		$html .="\n<!-- DISPLAY COURSE MATERIAL -->\n\n";
		$html .="\n<div id='lib-section-coursematerial' class='content-block'>\n";
		if ($section['heading']) { $html .= "<h2>".$section['heading']."</h2>\n"; }
		$html .= getCustomHTML($section['prehtml']);
		$html .= "<div id=\"lib-section-coursematerial-list\"></div>\n";
		$html .= getCustomHTML($section['posthtml']);
		$html .="\n</div>\n";
		$html .= "\n";
		$html .= "\n<script>var rlistConfig = { linkTarget: \"".getCfg("alma")['link_target']."\" }</script>\n";
	}

	return $html;
}

function getDatabasesHTML() {

	$html = "";

	if ( getApp("display")['databases'] ) {

		if ( (hasDatabases() && getApp("display")['x-dbexpand']) || ( hasSubjects() && !getApp("display")['x-dbexpand'] ) ) {

			$section = getCfg('sections')['databases'];

			$html .="\n<!-- DISPLAY RELATED DATABASES -->\n\n";
			$html .="\n<div id='lib-section-databases' class='content-block show'>\n";
			if ($section['heading']) { $html .= "<h2>".$section['heading']."</h2>\n"; }
			$html .= getCustomHTML($section['prehtml']);
			$html .= "\n<ul>\n";

			// do stuff

			if ( getApp("display")['x-dbexpand'] ) {

				$db = getApp("databases");
				$l = count($db);

				for ($i=0; $i < $l; $i++) {
					$html .= "<li><a href='".$db[$i]['url']."' data-category='Databases' data-label='DB: ".$db[$i]['name']."'>".$db[$i]['name']."</a></li>\n";
				}

			}  else {

				$subj = getApp("subjects");
				$l = count($subj);

				for ($i=0; $i < $l; $i++) {
					$html .= "<li><a href='".getCfg('libapps')['libguides']."/az.php?s=".$subj[$i]['id']."' data-category='Databases' data-label='SUBJDB: ".$subj[$i]['name']."'>".$subj[$i]['name']." databases</a></li>\n";
				}
			}

			$html .= "</ul>\n";
			$html .= getCustomHTML($section['posthtml']);
			$html .="\n</div>\n";
			$html .= "\n";
		}

	}

	return $html;
}

function getLibrarianHTML() {


	global $app;

	$html = "";

	if ( $app['display']['librarian'] && hasLibrarian() ) {

		$section = getCfg('sections')['librarian'];

		$html .="\n<!-- DISPLAY LIBRARIAN -->\n\n";
		$html .= "<h2>".$section['heading']."</h2>\n";
		$html .= getCustomHTML($section['prehtml']);

		$html .= generateProfileBoxHTML($app['librarian']);

		$html .= getCustomHTML($section['posthtml']);
		$html .= "\n";
	}

	return $html;
}

// we use verb generate because we're passing a param
function generateProfileBoxHTML($librarians) {

	$html = "";

	/* 2018-08-10 - clkluck
		The API broke and doesn't list an expanded librarian profile in the return JSON. Though springshare will fix we needed to clean up the errors in the wake, even
		if tempoarary. So there are extra checks for the "profile" attribute, providing an alternate display if it does not exist
	*/

    $expandedProfile = count($librarians) === 1 ? true : false;

	// LIBRARIAN PROFILE
	foreach ($librarians as $librarian) {



        $html .= "<div id='profile-".$librarian['id']."' class='librarian-profile-outer'>\n";

        $html .= generateProfileBoxCard($librarian, $expandedProfile);

        // if we are displaying one profile, expand it
		if ($expandedProfile) {

            $html .= generateProfileBoxExpanded($librarian);

		}

        $html .= "</div>\n";
	}

	return $html;

}

// this creates the profile card at the top of the librarian profile

function generateProfileBoxCard($librarian, $expandedProfile=true) {

        $profile_hasImage = isset($librarian['profile']['image']['url']) && $librarian['profile']['image']['url'] !== "" ? true : false;
        $profile_hasURL = isset($librarian['profile']['url']) && $librarian['profile']['url'] !== "" ? true : false;

        $profile_imgSrc = ( $profile_hasImage ) ? $librarian['profile']['image']['url'] : "";
        $profile_URL = ( $profile_hasURL ) ? $librarian['profile']['url'] : "";

        $tipText = "Open ".$librarian['first_name']."'s profile page for contact information";
        $nameText = $librarian['first_name']." ".$librarian['last_name'];

        // we are going to use DOM elements for the profile card because the structure of this HTML section is very dynamic. We will be manipulating elements as we gather information
        $dom = new DOMDocument();
        //$dom->loadHTML("<div></div>");

        // we are going to create this, and then change the link text depending upon if it is expanded or compact
        $linkToProfile = null;
        if ($profile_hasURL) {
            $linkToProfile = $dom->createElement("a");
            $linkToProfile->setAttribute("href", $profile_URL);
            $linkToProfile->setAttribute("title", $tipText);
            $linkToProfile->setAttribute("aria-label", $tipText);
            $linkToProfile->setAttribute("data-category", "Profile");
            $linkToProfile->setAttribute("id", "profile-".$librarian['id']."-link");
        }

        // generate the main profile box which we will put in the profile pic (if there is one) and text
        $profileCard_outerDIV = $dom->createElement("div");
        $profileCard_outerDIV->setAttribute("class","librarian-profile-card");
        $profileCard_outerDIV->setAttribute("id", "profile-".$librarian['id']."-card");

        // generate the profile picture div (if there is one) and add it to the outerDIV
        if ( $profile_hasImage ) {
            // the image div
            $profileCard_imgDIV = $dom->createElement("div");
            $profileCard_imgDIV->setAttribute("class","librarian-profile-card-image");

            // the image itself
            $profileCard_imgIMG = $dom->createElement("img");
            $profileCard_imgIMG->setAttribute("src",$profile_imgSrc);
            $profileCard_imgIMG->setAttribute("alt",$nameText."'s profile picture");

            // add the image to the image container and the container to the profile
            $profileCard_imgDIV->appendChild($profileCard_imgIMG);
            $profileCard_outerDIV->appendChild($profileCard_imgDIV);
        }

        // generate the profile name and link
        $profileCard_aboutDIV = $dom->createElement("div");
        $profileCard_aboutDIV->setAttribute("class","librarian-profile-card-about");

        $profileCard_aboutLines = array();

        // generate profile name
        $div_name = $dom->createElement("div");
        $div_name->setAttribute("class","librarian-profile-card-name");
        $div_name->appendChild(new DOMText($nameText));
        $profileCard_aboutLines[] = $div_name;
        $div_name = null;

        // generate link to profile
        if ($profile_hasURL) {
            $div_link = $dom->createElement("div");
            $div_link->setAttribute("class","librarian-profile-card-link");

            if($expandedProfile) {
                $linkToProfile->appendChild(new DOMText("View profile"));
            } else {
                $linkToProfile->appendChild(new DOMText("Profile and contact info"));
            }

            $div_link->appendChild($linkToProfile);
            $profileCard_aboutLines[] = $div_link;
            $div_link = null;
        }

        // add each about line to the about section
        foreach ($profileCard_aboutLines as $line) {
            $profileCard_aboutDIV->appendChild($line);
        }

        // put the profile box all together
        $profileCard_outerDIV->appendChild($profileCard_aboutDIV);
        $dom->appendChild($profileCard_outerDIV);

        // export DOM obj as HTML
        return $dom->saveHTML();
}

function generateProfileBoxExpanded($librarian) {

    $html = "";

    $html .= "<div class='librarian-profile-expanded' id='profile-".$librarian['id']."-expanded'>\n";

    // add profile pieces
    if (isset($librarian['profile'])) {

        // profile widget_lc (LibCal Widget Code - probably Appointment button)
        if(isset($librarian['profile']['widget_lc']) && $librarian['profile']['widget_lc'] !== "" ) {
            $html .= "<div class='librarian-profile-expanded-widget'>".$librarian['profile']['widget_lc']."</div>";
		}
		
				
		// profile widget_la (LibAnswers Widget)
		if(isset($librarian['profile']['widget_la']) && $librarian['profile']['widget_la'] !== "" ) {
			$html .= "<div class='librarian-profile-expanded-widget'>".$librarian['profile']['widget_la']."</div>";
		}

		// profile widget_other 
		if(isset($librarian['profile']['widget_other']) && $librarian['profile']['widget_other'] !== "" ) {
			$html .= "<div class='librarian-profile-expanded-widget'>".$librarian['profile']['widget_other']."</div>";
		}

        // add librarian contact info
        if ( isset($librarian['profile']['connect']) ) {

            $html .= "<div class='librarian-profile-expanded-contact'><strong>Contact:</strong>\n";

            // DISPLAY PHONE
            if ( isset( $librarian['profile']['connect']['phone']) && $librarian['profile']['connect']['phone'] !== "") {
                $phone = $librarian['profile']['connect']['phone'];
                if ( $phone ) {
                    $html .= "<a href='tel:".$phone."' data-category=\"Profile\">".$phone."</a>";
                }
            }

            // DISPLAY EMAIL
			$email = "";
			if ( isset($librarian['profile']['connect']['email']) && $librarian['profile']['connect']['email'] !== "" ) {
                $email = $librarian['profile']['connect']['email'];
			} else if ( isset($librarian['email']) && $librarian['email'] !== "" ) {
                $email = $librarian['email'];
			}
			
			if ( $email !== "" ) {
				$html .= "<a href='mailto:".$email."' data-category=\"Profile\">".$email."</a>";
			}

            // DISPLAY ADDRESS
            if ( isset($librarian['profile']['connect']['address']) && $librarian['profile']['connect']['address'] ) {
				
				$addr = $librarian['profile']['connect']['address'];

				if ( $email !== "" ) {
					// remove email address if present as we already listed it 
					$addr = preg_replace ( '/[A-Za-z0-9\.\-\_]*@[A-Za-z0-9\.\-]*/i', "" , $addr );
				}

				// remove any empty tags left behind
				$addr = preg_replace ( '/<(\w+)\b(?:\s+[\w\-.:]+(?:\s*=\s*(?:"[^"]*"|"[^"]*"|[\w\-.:]+))?)*\s*\/?>\s*<\/\1\s*>/', "", $addr);
				// remove any double line breaks
				$addr = str_replace("\n\n", "\n", $addr);
				// add html line breaks - we'll also trim in case there were extras after email was removed.
				$addr = nl2br(trim($addr));
				
                $html .= "<p>" . $addr . "</p>";
            }

            $html .= "</div>";
        }

    }

    // add librarian subjects
    if ( isset($librarian['subjects']) && count($librarian['subjects']) > 0 ) {

        $subjList = "";

        foreach($librarian['subjects'] as $subject) {
            $subjList .= $subject['name'] . ", ";
        }

        $subjList = substr($subjList, 0, -2);
        $html .= "<p><strong>".$librarian['first_name']." works with:</strong><br />\n".$subjList."</p>";

    }

    $html .= "</div>\n";

    return $html;

}

function getTrackingHTML() {

	$html = "";

	if ( hasData( getCfg("analytics")['google'] ) ) {

		$label = "Unknown";
		$category = "Unknown";

		$page = getApp("page");

		if ( $page === "launch" || $page === "link") { // we'll just group them together and distinguish by category

			$category = ( calledByLMS() ) ? "LMS Course" : "Link Course";

			$label = getParameter("course"); // what is the course we are searching by?
			// if we override a course, what was the original? "LIBX201-LAMBERT (LIBX201-01)"
			if ( getParameter("course_id") && getParameter("course_id") !== getParameter("course") ) {
				$label .= " (".getParameter("course_id").")";
			}

			// add the user type to the label
			if ( calledByLMS() ) {
				if ( userIsAdmin() ) { $label .= " - Admin User"; }
				else if ( userIsEditor() ) { $label .= " - Instructional User"; }
				else { $label .= " - Student User"; };
			} else {
				$label .= " - Public";
			}
		} else if ( $page === "getlink" ) { // module/getlink/index.php page
			$category = "Get Link";
			$label = "Get Link";
		} else if ( $page === "customize" ) { // app/save.php page
			$category = "Customize";
			$label = "Custom";
			if ( userIsAdmin() ) { $label .= " - Admin User"; }
			else if ( userIsEditor() ) { $label .= " - Instructional User"; }
			else { $label .= " - Unknown"; } // shouldn't happen
		}

		$html = "\n\n";

		// add the google analytics code (no changes, straight from Google - except adding the "UA-" code from config!)
		$html .= "
<!-- Google analytics code -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', '".getCfg("analytics")['google']."', 'auto');
  ga('send', 'pageview');

</script>
<!-- end Google analytics code -->";


		$html .= "\n\n";

		// trigger a display event to be tracked
		$html .= "
<!-- we want to count the page load -->
<script>
  ga('send', 'event', '".$category."', 'Display', '".$label."');
</script>";
	}

	return $html;
}

function getCourseFormVerificationFields($submission = false) {
	$data = array();

	if($submission) {
		$data['form_lms_course_id'] = getParameter("form_lms_course_id");
		$data['form_lms_instructor'] = getParameter("form_lms_instructor");
		$data['form_lms_user'] = getParameter("form_lms_user");
		$data['form_lms_role'] = getParameter("form_lms_role");
	} else {
		$data['form_lms_course_id'] = getParameter("course_id");
		$data['form_lms_instructor'] = getParameter("instructor");
		$data['form_lms_user'] = getParameter("user_id");
		$data['form_lms_role'] = getParameter("roles");
	}

	return $data;
}

function saveDisplayPreferences() {
	global $app;

	$success = false;

	// do the save from data in $app['display'];
	$success = true;

	return $success;
}

function getEditorHTML() {

	//https://www.sitepoint.com/how-to-prevent-replay-attacks-on-your-website/

	// store in session array of tokens, sig, and fields, remove from that array once used

	$html = "<h2>Customize Page</h2>";

	$html .= "<div class=\"hide\">";

	if ( !awsIsSet() ) {
		$html .= "<p><strong>NOTE:</strong> AWS integration is not set! This is for testing purposes only. Admins WILL see this form and settings WILL NOT be saved. Instructors WILL NOT see this form.</p>";
	}

	$formStart = "<form method='POST' target='_self' action='save.php' class=\"lti-settings\">";

	// add the hidden data
	$data = getCourseFormVerificationFields();
	foreach ($data as $key => $value) {
		$formStart .= "<input type='hidden' name='".$key."' value='".$value."'>";
	}

	// add the form signature
	$formStart .= "<input type='hidden' name='form_signature' value='".generateDataSignature($data)."'>";

	$prefForm = getPreferenceFormHTML();

	$ul = "<ul>";

	$ul .= "<li>".generatePrefRadioButton("applyTo", "0", "Only this course section, only this term", "Apply only to this course, this section, this term (201740CICS498-01)", "checked")."</li>";

	$ul .= "<li>".generatePrefRadioButton("applyTo", "1", "All sections of this course by this instructor, all terms present and future", "Apply to this course, all sections by this instructor (CICS498-LAMBERT)")."</li>";

	$ul .= "<li>".generatePrefRadioButton("applyTo", "2", "All courses by this instructor, all terms present and future", "Apply to all courses in this department by this instructor (CICS-LAMBERT)")."</li>";

	$ul .= "</ul>";

	$applyToCourseFieldset = "<fieldset class=\"applyToOptions\">\n<legend>Apply these settings to:</legend>".$ul."</fieldset>";
	$ul = "";

	$formEnd = "<input type=\"submit\" name=\"save\" value=\"Save\"></form>";

	$html .= $formStart.$prefForm.$applyToCourseFieldset.$formEnd."</div>";
	$html .="<script src=\"".getJSdirectoryUrl()."/form-customization.js\"></script>";

	return "<div id=\"lib-editor-section\" class=\"lib-page-section management-section\">".$html."</div>";
}

function getAdminHTML() {
	global $app;
	$html = "<h2>Admin Info</h2>";

	$html .="<div class=\"hide\"></div>";

	return "<div id=\"lib-admin-section\" class=\"lib-page-section management-section\">".$html."</div>";
}

function getFeedbackHTML() {

	global $app;

	$html = "";

	if ( getCfg('messaging')['feedback']['display'] ) {
		$h = "<h2>".getCfg('messaging')['feedback']['heading']."</h2>";

		$h .="<div class=\"hide\">".getCustomHTML(getCfg('messaging')['feedback']['html'])."</div>";

		$html = "<div id=\"lib-feedback-section\" class=\"lib-page-section management-section\">".$h."</div>";
	}

	return $html;
}

function getPreferenceFormHTML() {


	global $app;
	global $tk;

	$ex['dept'] = ( isset(courseProperties()['dept']) && courseProperties()['dept'] !== "" )? courseProperties()['dept'] : "PHIL";
	$ex['num'] = ( isset(courseProperties()['num']) && courseProperties()['num'] !== "" ) ? courseProperties()['num'] : "201";
	$ex['section'] = ( isset(courseProperties()['section']) && courseProperties()['section'] !== "" ) ? courseProperties()['section'] : "01";
	$ex['instructor'] = ( isset(courseProperties()['instructor']) && courseProperties()['instructor'] !== "" ) ? courseProperties()['instructor'] : "THOMAS";

	$displayDefault = ( (boolParamIsEmpty("displayIntro")     || boolParamIsEqualTo("displayIntro", getCfg('sections')['intro']['display']) ) &&
					    (boolParamIsEmpty("displayDiscovery") || boolParamIsEqualTo("displayDiscovery", getCfg('sections')['discovery']['display']) ) &&
					    (boolParamIsEmpty("displayGuides")    || boolParamIsEqualTo("displayGuides", getCfg('sections')['guides']['display']) ) &&
					    (boolParamIsEmpty("displayCourseMaterial") || boolParamIsEqualTo("displayCourseMaterial", getCfg('sections')['coursematerial']['display']) ) &&
					    (boolParamIsEmpty("displayDatabases") || boolParamIsEqualTo("displayDatabases", getCfg('sections')['databases']['display']) ) &&
					    (boolParamIsEmpty("displayLibrarian") || boolParamIsEqualTo("displayLibrarian", getCfg('sections')['librarian']['display']) ) &&
					    (boolParamIsEmpty("displayChat")      || boolParamIsEqualTo("displayChat", getCfg('sections')['chat']['display']) ) &&
					    (boolParamIsEmpty("x-dbexpand")       || boolParamIsEqualTo("x-dbexpand", getCfg('sections')['databases']['dbexpand']) )
					   ) ? 1 : 0; // true : false

	$html = "<!-- accessibility: https://www.w3.org/WAI/tutorials/forms/instructions/ -->\n";

	$hidden = "<input type=\"hidden\" name=\"cdp\" value=\"1\">\n";

	// we only want to display the course ID option if it is not being added through the LMS, or if it is in the LMS, only by Admins and Course Designers
	if ( (calledByLMS() && (userIsContentDeveloper() || userIsAdmin()) ) || !calledByLMS() ) {

		$value = ( courseProperties()['id'] !== getParameter("course_id")) ? courseProperties()['id'] : "";
		$attr = "placeholder=\"ex. ARTH310\" pattern=\"".getCfg('univ')['regex']['course']."\"". ( userIsAdmin() ? "" : " required" );
		$example = "Example: <tt>".$ex['dept']."".$ex['num']."</tt>, <tt>".$ex['dept']."".$ex['num']."-".$ex['section']."</tt>, <tt>".$ex['dept']."".$ex['num']."-".$ex['instructor']."</tt> (instructor last name), <tt>".$ex['dept']."</tt> (general guide for department)";

		$html .= "<div>".generatePrefTextInputField("course", $value, "Course ID".( userIsAdmin() ? "" : " (required)" ).":", $example, $attr)."</div>\n";

		$attr = $value = "";

	}

	$html .= "<div class=\"customOptions\">".$tk->generateCheckBox("recommended", "", "Display default content recommended by library", "You can use the default, recommended settings or pick and choose from a list of options.", /*currently checked?*/( ($displayDefault) ? "checked" : "" ) ) ."</div>\n";

	// create the list of checkboxes of those things we can override
	$x=0;
	$checkboxes = "";

	// INTRO SECTION
	if(getCfg('sections')['intro']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayIntro", getCfg('sections')['intro'], boolParamEval("displayIntro"))."</li>\n";
	}
	//$hidden .= "<input type='hidden' name='displayIntro' value='".getCfg('sections')['intro']['display']."'>";


	// GUIDES SECTION
	if(getCfg('sections')['guides']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayGuides", getCfg('sections')['guides'], boolParamEval("displayGuides"))."</li>\n";
	}
	//$hidden .= "<input type='hidden' name='displayGuides' value='".getCfg('sections')['guides']['display']."'>";

	// DISCOVERY SECTION
	if(getCfg('sections')['discovery']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayDiscovery", getCfg('sections')['discovery'], boolParamEval("displayDiscovery"))."</li>\n";
	}
	//$hidden .= "<input type='hidden' name='displayDiscovery' value='".getCfg('sections')['discovery']['display']."'>";

	// DATABASE SECTION
	if(getCfg('sections')['databases']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayDatabases", getCfg('sections')['databases'], boolParamEval("displayDatabases"));

		$radiobuttons = "";
		if(getCfg('sections')['databases']['dbexpandAllowOverride']) {

			$radiobuttons .= "<li>".generatePrefRadioButton("x-dbexpand", "0", "Link to a list of related databases (compact)", "", defaultCheckState( "0", getCfg('sections')['databases']['dbexpand'], boolParamEval("x-dbexpand") ) )."</li>\n";

			$radiobuttons .= "<li>".generatePrefRadioButton("x-dbexpand", "1", "List all related databases (expanded)", "", defaultCheckState( "1", getCfg('sections')['databases']['dbexpand'], boolParamEval("x-dbexpand") ) )."</li>\n";

			// false = )
			// true = defaultState(getCfg('sections')['databases']['dbexpand'])

			$radiobuttons = "<fieldset id=\"secondary-dbexpand\">\n<legend>Database display type:</legend>\n<ul>\n".$radiobuttons."</ul>\n</fieldset>\n";

		}

		$checkboxes .= $radiobuttons."</li>\n";
		$radiobuttons = "";
	}

	// COURSE MATERIAL SECTION
	if(getCfg('sections')['coursematerial']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayCourseMaterial", getCfg('sections')['coursematerial'], boolParamEval("displayCourseMaterial"))."</li>\n";
	}

	// LIBRARIAN SECTION
	if(getCfg('sections')['librarian']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayLibrarian", getCfg('sections')['librarian'], boolParamEval("displayLibrarian"))."</li>\n";
	}

	// CHAT SECTION
	if(getCfg('sections')['chat']['allowOverride']) { // can we override?
		$x++;
		$checkboxes .= "<li>".generatePrefCheckBox("displayChat", getCfg('sections')['chat'], boolParamEval("displayChat"))."</li>\n";
	}

	$checkboxes = "<ul>\n".$checkboxes."</ul>\n";

	$fieldset = "<fieldset class=\"customOptions\">\n<legend>Custom display options</legend>\n".$checkboxes."</fieldset>\n";
	$checkboxes = ""; // we've moved checkboxes elsewhere so clear it out

	$style = "";
	if($x === 0) {
		$style = "<style>.customOptions { display: none; }</style>\n";
	}

	return $hidden.$html.$fieldset.$style;
}

function generatePrefCheckBox($name, $arr, $current = -1) {

	global $tk;

	$text = $arr['heading'];
	$desc = $arr['desc'];
	$attr = defaultCheckState("1", $arr['display'], $current); // default state?

	return $tk->generateCheckBox($name, "1", $text, $desc, $attr);
}

function generatePrefRadioButton($name, $value, $text, $desc = "", $attr = "") {

	global $tk;

	return $tk->generateRadioButton($name, $value, $text, $desc, $attr);
}

function generatePrefTextInputField($name, $value, $text, $desc, $attr) {

	global $tk;

	return $tk->generateTextInputField($name, $value, $text, $desc, $attr);
}

function defaultCheckState($value, $default, $current = -1) {
	$s = "";

	if ($current === -1) { $current = $default; } // no pref set, use default

	$s .= "data-default-value=\"".$default."\""; // add a data attr that lets js know what sys default is if "use default" checked

	$s .= ( (int) $value === (int) $current ) ? " checked" : ""; // if the value is same as current, we have a match!

	return $s;
}

/* *********************************************************************************************
 *	getCustomHTML( $data )
 *
 *  For HTML we can either bring in a file from the custom folder on the server, or use
 *  the html text already in the variable
 *  Essentially we check to see if we are using a file, if not we just send back the code
 *  sent to us
 *
 *  [[FILE:blah-blah.html]]
 *
 *  @param string $data The string to convert to an array
 */

function getCustomHTML($data) {
	$html = "";

	$regex = "/(?<=^\[\[FILE:)[A-Za-z0-9_-]+\.html(?=\]\]$)/"; // positive, non capturing lookahead and behind strips "[[FILE:" and "]]" in one shot

	// if [[FILE:somefile.html]] then read in the file
	if ( preg_match($regex, $data, $matches) === 1 ) { // if a match found (===1) then put the match in $matches array
		$filename = array_pop($matches); // matches is an array with (presumably) 1 element, but we're just cautious

		try { // read the file contents from the custom folder
			$html = file_get_contents ( getPathCustom()."html/".$filename );
		} catch (Exception $e) {
			logMsg($e);
		}

	} else { // it's just plain html text
		$html = $data;
	}

	return $html;
}

?>
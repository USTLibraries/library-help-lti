<?php

// this is the class that accesses and returns the guide data from the LibApps api
require_once getPathIncApp()."class-dao-guide.php";

// this brings in the class that applies the rulesets to search by
require_once getRuleSetLocation("libapps");

// generate() performs the duties of this page and is called during execution
function page_GuideData($courseData, $displayParam) {
	
	global $app;
	
	// get LibApps config values as well as the ruleset
	$libapps = getCfg("libapps");
	$ruleset = new GuideRuleSet();
	
	$gObj = new GuideData($courseData, $displayParam, $libapps['apiDomain'], $libapps['siteID'], getCfg('app-secrets')['libapps']['apiKey'], $ruleset, $libapps['subjexclude'], $libapps['defaultSearchGroup']);

	$gObj->init();
	
	$g = $gObj->getAll();
	
	logMsg("Guide request for ".$courseData['id'], $g);
	
	$app['guides'] = $g['guides']; // api
	$app['subjects'] = $g['subjects']; // api
	$app['databases'] = $g['databases']; // api
	$app['librarian'] = $g['profiles']; // api
	$app['course'] = $g['course'];
	$app['coursematerials'] = array();
}


// set the course (XXXX is default)
setCourse();
setDisplayParameters();
page_GuideData( getApp("course"), getApp("display"));

?><!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width"/>
<title><?php echo getCfg("lti")['property']['text']; ?></title>

<base target="_blank">

<!-- Order counts so we load them in this order -->
<?php 
	// add the lms custom style sheet
	if( isset($app['css_common']) && $app['css_common'] ) {
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$app['css_common']."\">\n";
	}
?>
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/display.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCustomDirectoryUrl(); ?>/css/custom.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body data-course="<?php echo $app['course']['id']; ?>" data-department="<?php echo $app['course']['dept']; ?>" class="<?php echo calledByLMS() ? "lms" : "non-lms" ?>" >

<h1 class="screen-reader-text"><?php echo getCfg("lti")['property']['text']; ?></h1>
	
<?php include getPathCustom()."html/display-header.html"; ?>

<?php require getPathCustom()."html/display-body.php"; ?>

<?php include getPathCustom()."html/display-footer.html"; ?>

<?php
	if(userIsEditor()) {
		if ( awsIsSet() || userIsAdmin() ) { // even if AWS isn't set up, we want Admins to play
			echo getEditorHTML(); // customization form
		}
		
		echo getFeedbackHTML(); // feedback html (could be html for form or link)
		
		if(userIsAdmin()) {
			echo getAdminHTML(); // debug info
		}
	}
?>

<?php echo getTrackingHTML(); ?>
	
<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/display.js"></script>
<script src="<?php echo getCustomDirectoryUrl(); ?>/js/custom.js"></script>

</body>
</html>
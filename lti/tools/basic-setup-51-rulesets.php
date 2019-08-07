<?php

require_once __DIR__."/../custom/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require(__DIR__."/../inc/inc-tool-access-check.php");


// this is the class that accesses and returns the guide data from the LibApps api
require_once __DIR__."/../inc/class-dao-guide.php";

// this brings in the class that applies the rulesets to search by
require_once getRuleSetLocation("libapps");

// this is the class that accesses and returns the readinglist data from the ALMA api
require_once __DIR__."/../inc/class-dao-readinglist.php";

// this brings in the class that applies the rulesets to search by
require_once getRuleSetLocation("reading");

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo getCfg("lti")['name'] ?>: Installation Tools</title>

<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/tool.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body data-course="<?php echo $app['course']['id']; ?>">
<?php
	include(__DIR__."/../inc/inc-tool-header.php");
?>

<div>

	<h1><?php echo getCfg("lti")['name'] ?>: Installation Tools</h1>
	<h2>Test Rulesets: Results for <?php echo getApp("course")['code']; ?></h2>

	<h3>Current Ruleset Files Being Used:</h3>

	<ul>
		<li><strong>LibGuides:</strong> <?php echo getRuleSetLocation("libapps"); ?></li>
		<li><strong>Alma:</strong> <?php echo getRuleSetLocation("reading"); ?></li>
	</ul>


<h3>Test LibGuide Search Rules</h3>

<?php
	setCourse();
	setDisplayParameters();
	$a = getApp("course");

	$libapps = getCfg("libapps");
	$ruleset = new GuideRuleSet();

	$gObj = new GuideData(getApp("course"), getApp("display"), $libapps['apiDomain'], $libapps['siteID'], getCfg('app-secrets')['libapps']['apiKey'], $ruleset);

	echo "<pre><font color=blue>";

	$a = getApp("course");
	echo "\n\n***".$a['id']."***\n\n";

	$x = -1;
	do {

		$x++;
		$v = $gObj->getLibAppCourseSearchString( $a, $x );

		echo "Search for: ".$v['code']."\n";
		echo "With rule : ".$v['index']."\n\n";

		$x = $v['index'];

	}  while ($x >= 0);


	echo "</font></pre>";
?>


<h3>Test LibGuide Results</h3>

<?php

	$gObj->init();
	$g = $gObj->getAll();

	echo "<pre><font color=blue>";

	echo "\n\n***".$a['id']."***\n\n";

	print_r($g);

	echo "</font></pre>";

	$alma = (getCfg("app-secrets")['alma']['apiKey'] !== "" );
	if ($alma) {
?>


<h3>Test Alma Search Rules</h3>

<?php

	// get alma config values as well as the ruleset
	$alma = getCfg("alma");
	$ruleset = new ReadingListRuleSet();

	// construct a ReadingListData object with the required parameters
	$rObj = new ReadingListData(getApp("course"), $alma['apiDomain'], getCfg('app-secrets')['alma']['apiKey'], getCfg("alma"), $ruleset);

	echo "<pre><font color=blue>";

	echo "\n\n***".$a['id']."***\n\n";

	$x = -1;
	do {

		$x++;

		$v = $rObj->getAlmaCourseSearchString( $a, $x );

		echo "Search for: ".$v['code']."\n";
		echo "With rule : ".$v['index']."\n\n";

		$x = $v['index'];

	}  while ($x >= 0);


	echo "</font></pre>";
?>


<h3>Test Alma Results</h3>

<?php

	$rObj->init();
	$r = $rObj->getAll();

	echo "<pre><font color=blue>";

	echo "\n\n***".$a['id']."***\n\n";

	print_r($r);

	echo "</font></pre>";
} else {
?>
<h3>Alma Not Tested</h3>
<p>Alma API is not configured.</p>
<?php
}
?>
</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(__DIR__."/../inc/inc-tool-footer.php");
?>
</body>
</html>
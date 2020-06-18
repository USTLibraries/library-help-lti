<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require_once getPathIncApp()."inc-tool-access-check.php";

$pageName = getCfg("lti")['name'] . ": Advanced LibGuide Overrides";

// load defaults from emulator-test.json document
$str_data = file_get_contents(getPathCustom()."data/override-libguides-search.json");
$data = json_decode($str_data, true);

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageName ?></title>

<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/tool.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body>
<?php
	include(getPathIncApp()."inc-tool-header.php");
?>

<div>


	<h1><?php echo $pageName ?></h1>

    
    <p>The <a href="lms-emulator-canvas-10.php">LMS Emulator</a> may be used to test advanced overrides in a simulated environment with or without access to an actual LMS. Overrides also may be used by passing custom parameters to the LTI's API.</p>
    <p>Current contents of private/app/custom/data/override-libguides-search.json</p>
    
    <pre><?php 
    	echo "<pre><font color='blue'>";
		sanitized_print_r($data);
		echo "</font></pre>";
    ?></pre>



</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(getPathIncApp()."inc-tool-footer.php");
?>
</body>
</html>
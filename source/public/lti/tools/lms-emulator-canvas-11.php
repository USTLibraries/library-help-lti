<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require_once getPathIncApp()."inc-tool-access-check.php";

$pageName = getCfg("lti")['name'] . ": LMS Emulator Test Data";

// load defaults from emulator-test.json document
$str_data = file_get_contents(getPathCustom()."data/emulator-test.json");
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

    
    <p>The <a href="lms-emulator-canvas-10.php">LMS Emulator</a> may be used to test the LTI's configuration in a simulated environment with or without access to an actual LMS.</p>
    <p>The emulator-test.json file may be edited to provide some demo courses to test configurations. Use it to make sure guides, subjects, librarians, and course lists show up as expected.</p>
    <p>The fields of the first block, <code>default</code>, should remain in tact. However, you may change the values of those fields. Update the blocks after <code>default</code> to create your test/demo cases.</p>
    <p>Note that the default block is used as a base to fill in all unspecified values.</p>
    <p>Also note, that the <code>custom_lri_id</code> field is required and MUST be unique among the blocks, otherwise the LTI will fail in switching between courses both in the emulator and in the LMS.</p>
    <p>Current contents of /private/app/custom/data/emulator-test.json</p>
    
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
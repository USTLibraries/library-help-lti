<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require(getPathIncApp()."inc-tool-access-check.php");

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo getCfg("lti")['name'] ?>: Installation Tools</title>

<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/tool.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body>
<?php
	include(getPathIncApp()."inc-tool-header.php");
?>

<div>
	
	<h1><?php echo getCfg("lti")['name'] ?>: Set-Up Canvas</h1>
	
	<h2>1. Set Course ID</h2>
	
	<p><code>context_label</code> is the field used by default by most LTI applications. If you have set up an LTI before you'll recognize whether you kept it as <code>context_label</code> or changed it to something else. Even if you haven't set one up before here's the question to ask yourself: What variable field in the LMS stores the information relating to the year, term, and course identifier (e.g. 2018FA-BIOL201-01) that is used to link the course to other systems such as Leganto and the University/Student Information System?</p>
	<p>You may find that instead of <code>context_label</code> you will want to use something like <code>Canvas.course.sisSourceId</code>. (See <a href="https://canvas.instructure.com/doc/api/file.tools_variable_substitutions.html">variable substitutions on Canvas</a>). Work with your LMS administrator to identify where the university's unique course id for the course is stored in the LMS.</p>
	
	<p>To use <code>context_label</code> leave the following line in the config.ini.php file as <code>match_value = ""</code>. If you wish to change the source variable change it to something like: <code>>match_value = "Canvas.course.sisSourceId"</code></p>
	
	<p>You are currently using:</p>
	<ul>
		<li><?php echo ( getCfg("lti")['match_value'] !== "" ) ? getCfg("lti")['match_value'] : "context_label"; ?></li>
	</ul>
	
	<h2>2. Change settings in config.ini.php</h2>
	<p>Set the <code>name</code>, <code>description</code>, <code>property[text]</code>, <code>property[tool_id]</code>, etc in your config.ini.php file. Use the instructions in the config file to guide you.</p>
	<p>When done, click on the Next button.</p>
	
	<p class="lti-settings"><a href="lms-setup-11-app.php" class="button">Next</a></p>
	
</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>


<?php
	appExecutionEnd();	
	include(getPathIncApp()."inc-tool-footer.php");
?>
</body>
</html>
<?php

require_once __DIR__."/../custom/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require(__DIR__."/../inc/inc-tool-access-check.php");

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
	include(__DIR__."/../inc/inc-tool-header.php");
?>

<div>
	
	<h1><?php echo getCfg("lti")['name'] ?>: Set-Up Canvas Part 2</h1>
	
	<h2>3. Add App in LMS</h2>
	
	<p>Go into your LMS and add an app. Choose the &quot;By URL&quot; option.</p>
	
	<p>Add an app &quot;By URL&quot;:</p>
	
	<ul>
		<li>Name: <?php echo getCfg("lti")['property']['text'] ;?></li>
		<li>Consumer Key: <?php echo getCfg("lti")['oauth_clientid'] ;?></li>
		<li>Shared Secret: <?php echo getCfg("app-secrets")['lti']['oauth_secret'] ;?></li>
		<li>Config URL: <?php echo getBaseURL(); ?>/app/config.php</li>
	</ul>
	
</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>


<?php
	appExecutionEnd();	
	include(__DIR__."/../inc/inc-tool-footer.php");
?>
</body>
</html>
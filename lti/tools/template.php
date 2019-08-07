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
	
	<h1><?php echo getCfg("lti")['name'] ?>: Installation Tools</h1>
	<h2></h2>
	
</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();	
	include(__DIR__."/../inc/inc-tool-footer.php");
?>
</body>
</html>
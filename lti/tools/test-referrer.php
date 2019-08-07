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
	<h2>Referrer Test Page</h2>
		<p>See how your pages open directly, in iframes, inpage, and through a jQuery script to make sure the allowed referrers are configured correctly. There should be no problem using the IFRAME and JQUERY on or off campus. The direct link to the test page may fail and that's okay, that's what should happen unless you allow all referrers.</p>

		<h2>DIRECT LINK:</h2>
		<p>Copy link for no referrer: <a href="referrer-test-page.php" target="_blank">referrer-test-page.php</a></p>

		<h2>IFRAME:</h2>
		<iframe src="referrer-test-page.php" width="100%" height="1000px"></iframe>

		<h2>PHP:</h2>
		<div style="border-style: solid; width: 100%">

			<?php

			$url = str_replace("referrer.php","","https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
			$url = rtrim($url, '/') . '/referrer-test-page.php';

			echo file_get_contents($url);
			?>

		</div>

		<h2>JQUERY</h2>
		<div id="someID" style="border-style: solid; width: 100%;"></div>
		<script>$('#someID').load('referrer-test-page.php');</script>
	
</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();	
	include(__DIR__."/../inc/inc-tool-footer.php");
?>
</body>
</html>
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
	<h2>LibGuides and Alma API Keys</h2>

	<?php

	$libguides = (getCfg("app-secrets")['libapps']['apiKey'] !== "" );
	$alma = (getCfg("app-secrets")['alma']['apiKey'] !== "" );


	if($libguides) { echo "<p>LibGuides API Key is Configured.</P>"; } else { echo "<p>LibGuides API Key is not configured and is REQUIRED.</P>"; }
	if($alma) { echo "<p>Alma API Key is Configured.</P>"; } else { echo "<p>Alma API Key is not configured but is optional.</P>"; }
	if(!$libguides) {
	?>

		<h3>Set-Up LibGuides API Key</h3>
		<p>Go into your LibGuides admin area and obtain the API key for LibGuides.</p>
	<?php
	} else {
	?>
		<h3>LibGuides API Key is Configured</h3>
		<p>Congratulations! Your LibGuides API key is configured.</p>
		<p>If you need to update the key go into your LibGuides admin area and obtain the API key for LibGuides.</p>
	<?php
	}
	?>
		<p>Then go into config.ini.php and update the following line under <code>[app-secrets]</code>:</p>
		<pre>libapps[apiKey] = "{{your_key_here}}"</pre>
		<p>And make sure your <code>siteID</code>, <code>libguides</code>, <code>apiDomain</code>, and subject IDs to exclude (<code>subjexclude</code>) are set under <code>[libapps]</code>.

<pre>siteID = "<?php echo getCfg("libapps")['siteID']; ?>"
libguides = "<?php echo getCfg("libapps")['libguides']; ?>"
apiDomain = "<?php echo getCfg("libapps")['apiDomain']; ?>"
subjexclude = "<?php echo getCfg("libapps")['subjexclude']; ?>"</pre>
	<?php

	if(!$alma) {
	?>
		<h3>Set-Up Alma API Key (Optional)</h3>
		<p>If you have Alma and plan on using the Course Reserves/Leganto readinglist feature, you must obtain an API key from the Ex Libris Developers Network: <a href="https://developers.exlibrisgroup.com/" target="_blank">https://developers.exlibrisgroup.com</a>.</p>
	<?php
	} else {
	?>
		<h3>Alma API Key is Configured</h3>
		<p>Congratulations! Your Alma API key is configured.</p>
		<p>If you need to update the key go to the Ex Libris Developer's Network and obtain a new one: <a href="https://developers.exlibrisgroup.com/" target="_blank">https://developers.exlibrisgroup.com</a>.</p>
	<?php
	}
	?>
		<p>Then go into config.ini.php and update the following line:</p>
		<pre>alma[apiKey] = "{{your_key_here}}"</pre>


</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(__DIR__."/../inc/inc-tool-footer.php");
?>
</body>
</html>
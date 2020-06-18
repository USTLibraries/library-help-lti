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

	<h1><?php echo getCfg("lti")['name'] ?>: Installation Tools</h1>

	<p>You are currently running PHP version <?php echo phpversion(); ?> (&gt; 7.1 is required)</p>

	<p>It is recommended you follow the set-up in order and not try to configure the config.ini.php file all at once.</p>

	<h2>Set-Up &amp; Configuration Tests</h2>
	<p>Use these pages to check your configuration</p>
	<ol>
		<li><a href="basic-setup-10-keys.php">Set-Up Password and Keys (and Authenticator app)</a> - If you are seeing this page then the required password and keys are set up. But if you need help resetting anything or need to configure Authenticator then visit this link.</li>
		<li><a href="basic-setup-20-config.php">Test configuration file</a> - Make sure your configuration file loads without any errors</li>
		<li><a href="basic-setup-30-regex.php">Test Course Identifier Parsing</a> - Test your RegEx skills to make sure the LTI can extract course data from the course ID.</li>
		<li><a href="basic-setup-40-dept-mapping.php">Set-Up Department to Subject Mapping</a> - Map your university department codes to related subjects in LibGuides.</li>
		<li><a href="basic-setup-45-apikeys.php">Set-Up LibGuides and Alma API keys</a> - Configure your LibGuides and Alma API keys.</li>
		<li><a href="basic-setup-50-rulesets.php">Test Rule Sets</a> - With Course RegEx and APIs in place, check out if the rulesets perform searches over LibGuides and Alma correctly.</li>
		<li><a href="../module/getlink/" target="_blank">Test Display</a> - Use the Get Link page to test course codes and see the Library Help application in action using the test page.</li>
	</ol>

	<p>Once the basic set-up is complete you are now ready to add the application to your Learning Management System (LMS). If you don't have access to one, or are still waiting for access, don't worry, we've got an emulator for you!</p>

	<h2>Set-Up LMS</h2>
	<p>Use these pages to set-up and check your LMS configuration. You do not need access to an LMS to perform these actions as you can use the emulator below for testing.</p>
	<ol>
		<li><a href="lms-setup-10.php">Set-Up LMS App</a> - Information necessary to install the app in your LMS.</li>
        <li><a href="lms-emulator-canvas-10.php">LMS Emulator</a> - An emulator that allows you to pass variables to the LTI as if it were being displayed in your LMS. An LMS is not required, but the Set-Up LMS steps are required.</li>
        <li><a href="lms-emulator-canvas-11.php">LMS Emulator Test Courses</a> - View the file that loads demo course information into the emulator.</li>
        <li>Optional: <a href="lms-setup-20-api.php">Set-Up and Test LMS API</a> - For getting instructor info. Used to allow searches by instructor last name (ex: LIBX201-THOMAS)</li>
	</ol>

	<h2>Lock it Down</h2>
	<p>Use these pages to complete set-up and begin locking down access including debug functionality. Once these steps are done you are ready to move to production.</p>
	<ol>
		<li><a href="test-referrer.php">Test Referrer Settings</a> - Make sure your site can show up in IFRAMEs.</li>
		<li><a href="basic-setup-10-keys.php">Use Google Authenticator</a> to prevent brute forcing of admin login.</li>
	</ol>

    <h2>Advanced</h2>
    <p>Use these pages to perform advanced configurations. It is recommended everything above is up and running in a stable production environment before performing these tasks. You'll want to have a firm understanding of how the LTI works before tweaking.</p>

    <ol>
        <li><a href="advanced-setup-libguide-overrides-10.php">LibGuide Overrides</a> - For complex set-ups, you can pass additional fields from the LMS or to the API in order to surface specific guides, librarians, and subjects. For example, distinguishing campuses, colleges, special courses, etc.</li>
    </ol>

</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(getPathIncApp()."inc-tool-footer.php");
?>
</body>
</html>
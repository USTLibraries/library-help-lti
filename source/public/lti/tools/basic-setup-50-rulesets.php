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

<body data-course="<?php echo $app['course']['id']; ?>">
<?php
	include(getPathIncApp()."inc-tool-header.php");
?>

<div>

	<h1><?php echo getCfg("lti")['name'] ?>: Installation Tools</h1>
	<h2>Test Rulesets</h2>

	<?php

	$libguides = (getCfg("app-secrets")['libapps']['apiKey'] !== "" );
	$alma = (getCfg("app-secrets")['alma']['apiKey'] !== "" );


	if($libguides) { echo "<p>LibGuides API Key is Configured.</P>"; } else { echo "<p>LibGuides API Key is not configured and is REQUIRED.</P>"; }
	if($alma) { echo "<p>Alma API Key is Configured.</P>"; } else { echo "<p>Alma API Key is not configured but is optional.</P>"; }
	if($libguides) {
	?>

	<p><strong>NOTE:</strong> Make sure your Regular Expressions for your course identifiers are working by using the <a href="basic-setup-30-regex.php">Course RegEx Test Page</a> first!</p>
	<p>There is a default rule set that establishes the order to &quot;Roll-Up&quot; the hierarcy when searching for tagged LibGuides and Reading Lists.</p>
	<p>The default hierarchy is search by the complete id which typically includes year, term, department, course, and section. Typically all of this information is passed to the API and searched on. If, for example, no match is found for BIOL201 section 01 in Fall Term 2018, then BIOL201 section 01 is searched. If still no results the rules move up the hierarchy searching for BIOL201 (without a section) and then for a department level LibGuide or Reading List of BIOL. Again, that is the default behavior.</p>
	<p>There could be circumstances where it is more efficient to change the order. Perhaps, as a prime example, there is absolutely no reason a LibGuide would be tagged so specific to include a year and term. The system would be sending out two unnessary API calls for every page request which would slow down page response time and count against any API call limits LibGuides or Alma may have implemented. Of course, if caching was enabled this issue would be improved, but even so, it might be worthwhile to change the order of the rules, or even remove the search for year and term specific LibGuides.</p>
	<p>Let's get started with looking at the rulesets currently used and testing any customizations you might want to make. Custom rulesets may be uploaded and saved in the custom/rulesets directory. It is strongly advised that you come up with a versioning system and never change the default rulesets that came with the LTI install. You always need something to revert back to. Be sure to save separate ruleset files with dates/version numbers in the name. Rulesets are comprised of php code that is loaded as a function at runtime, so a little bit of PHP coding experience is necessary, but for the most part everything is already written, you will just be reordering the rules or tweaking already made templates. The templates are fully documented so look there for more information on creating the rulesets.</p>
	<p>Also, after you have uploaded a new ruleset to your custom folder, be sure to point to it in your config.ini.php file!</p>
	<h3>Current Ruleset Files Being Used:</h3>

	<ul>
		<li><strong>LibGuides:</strong> <?php echo getRuleSetLocation("libapps"); ?></li>
		<li><strong>Alma:</strong> <?php echo getRuleSetLocation("reading"); ?></li>
	</ul>

	<h3>Testing</h3>

	<h4>Choose a course code to test or enter your own.</h4>

	<form action="basic-setup-51-rulesets.php" method="get"><input type="text" name="course" width="20" placeholder="BIOL201-02"><input type="submit" value="Submit"></form>

<?php

	$cArr = getTestCourses();

	echo "<ul>\n";

	foreach($cArr as $value) {

		$a = courseProperties($value);
		echo "<li><a href=\"basic-setup-51-rulesets.php?course=".$a['id']."\">".$a['id']."</a></li>\n";

	}

	echo "</ul>\n";

} else {
?>
	<h3>Set-Up LibGuides API Key</h3>
	<p>Go into your LibGuides admin area and obtain the API key for LibGuides.</p>
	<p>Then go into config.ini.php and update the following line:</p>
	<pre>libapps[apiKey] = ""</pre>

<?php
}

if(!$alma) {
?>
		<h3>Set-Up Alma API Key (Optional)</h3>
		<p>If you have Alma and plan on using the Course Reserves/Leganto readinglist feature, you must obtain an API key from the Ex Libris Developers Network: <a href="https://developers.exlibrisgroup.com/" target="_blank">https://developers.exlibrisgroup.com</a>.</p>
		<p>Then go into config.ini.php and update the following line:</p>
		<pre>alma[apiKey] = ""</pre>
<?php
}
?>

</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(getPathIncApp()."inc-tool-footer.php");
?>
</body>
</html>
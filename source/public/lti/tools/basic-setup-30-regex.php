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
	<h2>Test Course RegEx</h2>
	
	<h3>RegEx Configuration</h3>
	<p>These are the Regular Expressions found in the custom/config.ini.php file:</p>
	
	<pre><?php
		foreach( getCfg("univ")["regex"] as $key => $value) {
			echo "<strong>".$key.":</strong> ".$value."\n";
		}
	?></pre>
	
	
	
	<h3>AdHoc Test</h3>
	<?php
	// we can send adHoc requests by using ?course=[something]
	setCourse();
	if( $app['course']['id'] !== "XXXX" ) {
		echo "<h3>".$app['course']['id']."</h3>";
		echo "<pre>";
		print_r($app['course']);
		echo "</pre>";		
	} else {
		$testURL = getBaseURL()."/tools/test-course-regex.php?course=BIOL201-02";
		echo "<p>You can perform adHoc tests by adding course=[something] to this page's query string.<br />";
		echo "For Example: <a href='".$testURL."'>".$testURL."</a></p>";
	}
	
	$courseArray = preg_split("/\\r\\n|\\r|\\n/", getCfg("univ")["test"]);
	?>

	<h3>Saved Test Cases:</h3>
	<p>These are updated in your config.ini.php file under <code>univ[test]</code>.</p>
	<pre><?php 
		foreach($courseArray as $value) {
			echo "<a href='#".$value."'>".$value."</a>\n";
		}
	?></pre>

	<?php

		foreach($courseArray as $value) {
			echo "<h4 id='".$value."'>Test: ".$value."</h4>";
			echo "<pre>";
			print_r(courseProperties($value));
			echo "</pre>";
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
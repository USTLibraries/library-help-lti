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
	<h2>Set-Up Department to Subject Mapping</h2>

	<h3>CSV File</h3>
	<p>The CSV file is located in /lti/custom/data/dept-subj-mapping.csv and is in the following format:</p>
<pre>LIBX,3046
BIOL,4930</pre>

<p>Which can be looked at as:</p>
<style>
table, td, tr {
	border: 1px solid black;
}
td {
padding: 2px 8px 2px 2px
}
</style>
<table>
<tr><td>LIBX</td><td>3046</td></tr>
<tr><td>BIOL</td><td>4930</td></tr>
</table>
<p>Where column 1 is your university's department code and column 2 would be the corresponding Subject ID in LibGuides.</p>
<p>NOTE: Subject IDs are unique to your instance of LibGuides. The IDs you use will differ from the examples.</p>

<p>To create the file from scratch, you may wish to open a new spreadsheet and place your university department codes in column one, and then figure out which subject ids are related.</p>
<p>For example, if BIOL (Biology) should be listed under the LibGuide subject "Biology and Life Science" with subject id 4930, then BIOL,4930 would be entered into the CSV.</P>
<p>If BICM (Biochemistry) should also be listed under the subject "Biology and Life Science" then it also could be mapped to the subject id 4930.</p>
<p>More than 1 University Department Code may be mapped to each LibGuide Subject, but only 1 subject may be mapped to each University Department Code. BICM can't be mapped to both "Biology and Life Science" and "Chemistry"</p>
<p>In the above example the following is what you would end up with after adding BICM:</p>

<table>
<tr><td>LIBX</td><td>3046</td></tr>
<tr><td>BIOL</td><td>4930</td></tr>
<tr><td>BICM</td><td>4930</td></tr>
</table>

<p>The mapping sheet should be saved as a CSV and uploaded to: /private/app/custom/data/dept-subj-mapping.csv</p>

<h3>Current CSV File</h3>

<?php

$homepage = file_get_contents(getPathCustom()."data/dept-subj-mapping.csv");
?>

<pre><?php echo $homepage; ?></pre>

</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include(getPathIncApp()."inc-tool-footer.php");
?>
</body>
</html>
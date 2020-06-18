<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require(getPathIncApp()."inc-tool-access-check.php");
require_once(getPathIncApp()."class-lti-client.php");


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

	<h1><?php echo getCfg("lti")['name'] ?>: Set-Up Canvas API</h1>

	<?php
	$hasToken = (getCfg("app-secrets")['lti']['api_token'] !== "" && getCfg('lti')['api_domain'] !== "" );
	if($hasToken) { echo "<p>Canvas API key IS configured.</P>"; } else { echo "<p><strong>Canvas API key IS NOT configured.</strong></P>"; }
	?>

	<p>This is an optional step. It allows Library Help to query Canvas for the course's instructor so that it can do a LibGuide tag match based on the instructor. (e.g. <code>BIOL219-THOMAS</code>)</p>
    
    <p>To add/update the LMS key:</p>

    <ul>
        <ol>Contact your Canvas administrator and obtain an API key and API Domain to access Canvas. (This function only needs access to courses and instructors of courses)</ol>
        <ol>Then go into config.ini.php and update the following line under <code>[app-secrets]</code> by adding the key between the quotes:<br />
	<pre>lti[api_token] = ""</pre></ol>
        <ol>You will also need to update the following line under <code>[lti]</code> by adding the domain between the quotes:<br />
	<pre>api_domain = ""</pre></ol>
        <ol>Then refresh this page you should see sample results of a query below.</ol>
    </ul>
    
    <?php
	if($hasToken) { 
    
        $client = new LTI_Client(getCfg('lti')['oauth_clientid'],getCfg('app-secrets')['lti']['oauth_secret'], getCfg('app-secrets')['lti']['api_token'], getCfg('lti')['api_domain']);

        $courses = json_decode(json_encode($client->canvas_api_test()), true);
        
        if ( count($courses) > 0 ) {
            echo "<p>A test query to ". getCfg('lti')['api_domain'] ." returned ". count($courses) ." courses:</p>";
            echo "<ul>";
            foreach ($courses as $course ) {
                echo "<li>".$course['id'].": ".$course['name']."</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red;'><strong>Test query to ". getCfg('lti')['api_domain'] ." returned 0 results. Check to make sure LMS key is correct.</strong></p>";
        }

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
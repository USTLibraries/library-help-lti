<?php

	//Start session to store data
	session_start();

	require_once __DIR__."/../custom/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
	require_once(__DIR__."/../inc/class-lti-client.php");

	$app['page'] = "customize";


	// validate that original call was by LMS
    if ( calledByLMS() ) {

		// start the LTI client
		// start the LTI client
		$client = new LTI_Client(getCfg('lti')['oauth_clientid'],getCfg('app-secrets')['lti']['oauth_secret'], getCfg('app-secrets')['lti']['api_token'], getCfg('lti')['api_domain']);

		$client->init();
				
		//setCourse();		
		
		//validate the form
		$data = getCourseFormVerificationFields(true);
		$signature = getParameter("form_signature", "POST");
		$isValid = validateDataSignature($data, $signature);
		$isSaved = false;
		
		if($isValid) {
			setDisplayParameters();
			$isSaved = saveDisplayPreferences();
		}
		
		
		?><!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width"/>
<title>Library Resource Integration</title>

	<!-- Order counts so we load them in this order -->
<?php 
	// add the lms custom style sheet
	if( isset($app['css_common']) && $app['css_common'] ) {
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$app['css_common']."\">\n";
	}
?>
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/display.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCustomDirectoryUrl(); ?>/css/custom.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body data-course="<?php echo $app['course']['id']; ?>" class="lri-settings <?php echo calledByLMS() ? "lms" : "non-lms" ?>" >

<h1>Saved</h1>

<div style="padding: 2.5rem 2.5rem 2.5rem 2.5rem">

<?php 
		if ($isSaved) {
			?><div class="lri-message success">Content Saved</div><?php
		} else {
			?><div class="lri-message error">Content Not Saved</div><?php
		}
?>


<a href="launch.php" class="button">Back</a>

</div>

<?php
		
	if(userIsAdmin()) {
		echo getAdminHTML(); // debug info
	}
		
?>

<?php echo getTrackingHTML(); ?>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/display.js"></script>
<script src="<?php echo getCustomDirectoryUrl(); ?>/js/custom.js"></script>

</body>
</html>
		<?php
		
		
		appExecutionEnd();
		
	} else {
		die("Unable to validate OAuth");
	}

?>
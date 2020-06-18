<?php

	//Start session to store data
	session_start();

	require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
	require_once(getPathIncApp()."class-lti-client.php");

	$app['page'] = "launch";

    if ( calledByLMS() ) {

		// start the LTI client
		$client = new LTI_Client(getCfg('lti')['oauth_clientid'],getCfg('app-secrets')['lti']['oauth_secret'], getCfg('app-secrets')['lti']['api_token'], getCfg('lti')['api_domain']);

		$client->init();
				
		setCourse();

		include(getPathIncApp()."page-display.php");
		appExecutionEnd();
		
	} else {
		die("Unable to validate OAuth");
	}

?>
<?php
/*  ============================================================================================
    ********************************************************************************************
	LIBRARY RESOURCE INTEGRATION: Functions for Application
    ********************************************************************************************

	University of St. Thomas Libraries (www.stthomas.edu/libraries)
	github.com/ustlibraries
		
	********************************************************************************************
	
	FILE LAST MODIFIED: 2019-02-23 - clkluck
	
	PURPOSE: Application initialization

	********************************************************************************************

	This script initializes the application. Anything that needs to be done at the start of each
	run should be put in this file.

	********************************************************************************************
	********************************************************************************************

		This is function template file from the PHP PROJECT FRAMEWORK library.
		Visit github.com/chadkluck/php-project-framework page for more information.
		FRAMEWORK FILE: inc/inc-app.php
		FRAMEWORK FILE VERSION: 2018-08-10

	********************************************************************************************
	============================================================================================
*/

/*  ============================================================================================
    ********************************************************************************************
    INITIALIZE APP
	******************************************************************************************** 
*/ 


// require an ssl connection (if required as set in config). If request was sent via http, redirect to https
requireSSL(); // note that even with a redirect, the initial request was sent insecurly
			  // also note that this is primarily for the admin tools section, module/getlink, and module/display
			  // It uses a redirect which does not resubmit POST data
			  // Do not rely on the redirect, always link to https. This is only optimal when a user is typing links directly


// Initialize the $app variable
$app = array();
$app['debug'] = false; //true|false - enables logging and output, can be overriden by ?debug=true in query string. server and ip locks can be put in place
$app['log']['event'] = array();
$app['log']['api'] = array();
$app['start_time'] = 0;
$app['end_time'] = 0;
$app['exe_time'] = -1;
$app['phpversion'] = phpversion();

// if your app sometimes receives a get/post param with a prefix put that prefix here. For example, xforward_debug=true will be treated same as if debug=true were passed in query
$app['param_prefix'] = "custom_lri_"; //EXAMPLE: $app['param_prefix'] = "xforward_";

// checks param sent, config settings, etc to see if we switch to debug mode
setDebugSwitch();

// start script execution timer for troubleshooting
setExeStartTime();

?>
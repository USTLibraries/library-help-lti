<?php

/*  ============================================================================================
    ********************************************************************************************
        Library Resource Integration - Functions
    ********************************************************************************************

	University of St. Thomas Libraries (www.stthomas.edu/libraries)
	github.com/ustlibraries
	
    ********************************************************************************************
*/

require_once __DIR__."/../../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init


if(!$app['debug']) {
	// for display we don't worry about origin so we don't check, we just pass it through
	httpReturnHeader(getCacheExpire("page"), getRequestOrigin());
}

$app['page'] = "link";

include_once(getPathIncApp()."page-display.php");

appExecutionEnd();


?>

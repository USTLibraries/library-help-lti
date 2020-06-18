<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init

/* **********************************************
 *  START
 */

if(isApprovedOrigin()) {
	echo "<h1>You're OK!</h1>";
} else {
	echo "<h1>You're NOT an approved requester!</h1>";
}

appExecutionEnd();

?>
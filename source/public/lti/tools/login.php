<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require_once getPathIncApp()."inc-tool-access-check.php";
require_once  getPathIncLib()."GoogleAuthenticator/GoogleAuthenticator.php";

function verifyAuthenticator($token) {
	$r = false;

	$secret = getCfg("secrets")['google-authenticator'];

	// if authenticator app is set up in config, then verify
	if ( hasData($secret)) {

		$ga = new PHPGangsta_GoogleAuthenticator();

		// take the secret from the confg and check against the token provided upon login
		if ($ga->verifyCode($secret, $token, 2)) {
			$r = true;
		}

	} else {
		$r = true; // not set up in config so just pass true
	}

	return $r;
}

function getAuthenticatorFieldHTML() {
	$s = "";

	if ( hasData(getCfg("secrets")['google-authenticator']) ) {
		$s = "<label>Authenticator Code:</label><input type=\"text\" name=\"authenticator\" id=\"authenticator\" value=\"\">";
	}

	return $s;

}

/* **************************************************************
 * AUTHENTICATION CHECK
 */

$password_fail = false;

if( isset($_SESSION['lti-admin']) ) {
	header("Location: index.php");
	exit();
} else {

	if( hasData(getParameter("password"))) {

	   if( verifyPassword(getParameter("password")) && verifyAuthenticator(getParameter("authenticator")) ) {
		   $_SESSION['lti-admin'] = true;

		   $redir = getParameter("r");
		   if ($redir === "") {
			   $redir = "index.php";
		   }
		   header("Location: ".$redir);
		   exit();
	   } else {
		   $password_fail = true;
	   }

	}
}

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
	include getPathIncApp()."inc-tool-header.php";
?>

<div>

	<h1><?php echo getCfg("lti")['name'] ?>: Installation Tools</h1>

<?php
if ($password_fail) {
?>
	<div>
		Password failed. Please try again.
	</div>

<?php
}
?>
	<p>Login as Admin</p>
	<form action="login.php" method="post" id="login">
		<label>Password:</label><input type="text" id="password" name="password" value="" autofocus>
		<?php echo getAuthenticatorFieldHTML(); ?>
		<input type="hidden" name="r" value="<?php echo getParameter("r"); ?>">
		<input type="submit" name="login" value="Log In">
	</form>


</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();
	include getPathIncApp()."inc-tool-footer.php";
?>
</body>
</html>
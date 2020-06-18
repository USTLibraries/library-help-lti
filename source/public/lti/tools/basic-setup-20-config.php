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
	
	$cfg = getCfg();
?>

<div>
	
	<h1><?php echo getCfg("lti")['name'] ?>: Installation Tools</h1>
	<h2>Test Config File</h2>
	
	<?php
	$location = getPathCustom()."config.ini.php";
	$found =  file_exists ( $location );
	
	?>
	
	<h3>Location</h3>
	<p><code><?php echo $location; ?></code><br />
	<?php echo ($found ? "<font color='green'>Found! :)</font>" : "<font color='red'>Not Found :(</font>"); // it should never not be found, otherwise this page wouldn't show ?>
	</p>
	
	<h3>Contents:</h3>
	<?php
		echo "<pre><font color='blue'>";
		sanitized_print_r($cfg);
		echo "</font></pre>";	
	?>
	
	<h3>Updating</h3>
	
	<p>The <code>config.ini.php</code> file is written in php.ini format. You'll notice that comments start with <code>;</code> and there are many comments to guide you through set-up. php.ini format is an array structure notation. Main sections are denoted by [header], [secrets], [lti], etc. Under each section you will find related variables that look like <code>allow-origin =</code>, <code>zone-restrict-allow-ip[tools] =</code>, and <code>key-store[] =</code>. You may update anything to the right of the equal (=) sign and pay particular attention if there are quotes. Most of the time numbers won't have quotes. The comments for each variable will note the format expected.</p>
	
	<p>Note that IP addresses and domains are written in regex. Always use a backslash before a . (\.). Samples are in the comments to get you started.</p>
	
	<p>For information about each of the variables, please refer to the comments in config.ini.php. The values and notes below are only here to help direct you important variables.</p>
	
	<h4>[header]</h4>
	
	<h5>allow-origin</h5>
	
	<p><code>allow-origin = &quot;<?php echo $cfg['header']["allow-origin"]; ?>&quot;</code></p>
	
	<p>What is this? explained here: <a href="https://en.wikipedia.org/wiki/Cross-origin_resource_sharing">https://en.wikipedia.org/wiki/Cross-origin_resource_sharing</a></p>
	
	<h5>bad-origin-allow-ip</h5>
	
	<p><code>bad-origin-allow-ip = &quot;<?php echo $cfg['header']["bad-origin-allow-ip"]; ?>&quot;</code></p>
	
	<p>Your Current IP is: <code><?php echo getRequestClient(); ?></code></p>
	
	<p>Set this to allow the application to ignore <code>allow-origin</code> if the request is coming from this range of IPs (typically your institution range, your deptartment range, or your computer's static IP). Useful when developing and testing.</p>
	
	<h5>api-cache</h5>
	
	<p><code>api-cache = <?php echo $cfg['header']["api-cache"]; ?></code></p>
	
	<p>The current setting is equal to <?php echo (($cfg['header']["api-cache"]/60)/60) ?> hours (or <?php echo (($cfg['header']["api-cache"]/60)) ?> minutes).</p>
	
	<h5>page-cache</h5>
	
	<p><code>page-cache = <?php echo $cfg['header']["page-cache"]; ?></code></p>
	
	<p>The current setting is equal to <?php echo (($cfg['header']["page-cache"]/60)/60) ?> hours (or <?php echo (($cfg['header']["page-cache"]/60)) ?> minutes).</p>

	<h4>[security]</h4>
	
	<h5>allow-debug</h5>
	
	<p><code>allow-debug = <?php echo $cfg['security']["allow-debug"]; ?></code></p>
	
	<p>When <code>?debug=true</code> is appended to the application's URL should debug info be shown to the user? You can (and should) lock this down to certain IPs and even distinguish between development/test and production servers/hosts using <code>allow-debug-ip</code> and <code>allow-debug-host</code> below.</p>
	
	<h5>allow-debug-ip</h5>
	
	<p><code>allow-debug-ip = &quot;<?php echo $cfg['security']["allow-debug-ip"]; ?>&quot;</code></p>
	
	<p>We recommend not having debug info available to the world so why not lock it down to your organization's IP range or your individual static IP?</p>
	
	<p>Your Current IP is: <code><?php echo getRequestClient(); ?></code></p>
	
	<h5>allow-debug-host</h5>
	
	<p><code>allow-debug-host = &quot;<?php echo $cfg['security']["allow-debug-host"]; ?>&quot;</code></p>
	
	<p>If you are fortunate enough to have a development and test environment separate from production, why have debug available in production? Use this to formulate a regex that can identify non-production environments.</p>
	
	<p>You are currently accessing this page from host: <code><?php echo getRequestHost(); ?></code></p>
	
	<h5>require-ssl</h5>
	
	<p><code>require-ssl = <?php echo $cfg['security']["require-ssl"]; ?></code></p>
	
	<p>1 means yes and 0 means no. We are living in an https world so set this to 1 unless you don't have a certificate installed yet.</p>
	
	<h5>zone-restrict-allow-ip[tools]</h5>
	
	<p><code>zone-restrict-allow-ip[tools] = &quot;<?php echo $cfg['security']["zone-restrict-allow-ip"]["tools"]; ?>&quot;</code></p>
	
	<p>Lock down this <code>lti/tools</code> section so it may only be accessed within your organization's IP range or your individual static IP.</p>
	
	<p>Your Current IP is: <code><?php echo getRequestClient(); ?></code></p>

</div>

<script src="<?php echo getJSdirectoryUrl(); ?>/main.js"></script>
<script src="<?php echo getJSdirectoryUrl(); ?>/tool.js"></script>

<?php
	appExecutionEnd();	
	include(getPathIncApp()."inc-tool-footer.php");
?>
</body>
</html>
<?php

require_once __DIR__."/../inc/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init
require_once getPathIncApp()."inc-tool-access-check.php";
require_once getPathIncLib()."GoogleAuthenticator/GoogleAuthenticator.php";


?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo getCfg("lti")['name'] ?>: Set Up LTI</title>

<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="<?php echo getCSSdirectoryUrl(); ?>/tool.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

</head>

<body>
<?php
	include(getPathIncApp()."inc-tool-header.php");
?>

<div>
	<?php
	$needs_setup = false;
	?>

	<h1><?php echo getCfg("lti")['name'] ?>: Set Up LTI</h1>

	<?php

	if( getCfg('secrets')['password-hash'] === "") {

		$needs_setup = true;

		?>

		<h2>Tools Password</h2>
		<p>Below you will find your admin password which you will have to keep for reference in order to access any of the tools in this &quot;tools&quot; directory. Also below you will find the password hash blob that you will need to put into the config.ini.php file for <code>password-hash</code> in the <code>SECRETS</code> section. If you forget/lose the password or need to change it just replace the blob for <code>password-hash</code> in config.ini.php with an empty string &quot;&quot; and upload. (If a hacker can FTP into your web server and change the config.ini.php file then you have worse things to worry about than them resetting your password!) This is not a mulit-user system, there is no username. You may, however, lock down the web facing tools directory to a specific IP range.</p>
		<p>The password is more of a random key, you are not able to set it yourself. So no, you may not use <code>monkey123</code> or <code>p@ssw0rd</code>. It's for your own good.</p>

		<?php
			$pswd = array();
			$pswd['text'] = generatePassword(12);
			$pswd['hash'] = generatePasswordHash($pswd['text']);
		?>

		<h3>Tools Password</h3>
		<p>Keep this safe and accessible only to you and those trusted with the care of this LTI.</p>
		<input type="text" value="<?php echo $pswd['text']; ?>">
		<h3>Blob for <code>password-hash</code> in config.ini.php</h3>
		<p>Place this string into the <code>SECRETS</code> section of config.ini.php where it says: <code>password-hash = </code></p>
		<textarea rows=3 cols=80><?php echo $pswd['hash']; ?></textarea>
	    <p>So the line should read:</p>
		<pre>password-hash = &quot;<?php echo $pswd['hash']; ?>&quot;</pre>

		<?php


	}

	?>

		<?php

		$ctemp = getCfg('secrets');

		if(    ( $ctemp['key-store'][0] === "" || strlen($ctemp['key-store'][0]) < 32 )
		    || ( $ctemp['key-store'][1] === "" || strlen($ctemp['key-store'][1]) < 32 )
		    || ( $ctemp['key-store'][2] === "" || strlen($ctemp['key-store'][2]) < 32 )
	        || ( $ctemp['key-store'][3] === "" || strlen($ctemp['key-store'][3]) < 32 )
		  ) {

			$ctemp = array(); // clear out

			$needs_setup = true;

			$ks1 = generateToken(32);;
			$ks2 = generateToken(32);;
			$ks3 = generateToken(32);;
			$ks4 = generateToken(32);;
			?>

			<h2>Keys</h2>
			<p>Below you will find secret keys used for various security functions. You will need to put these into the config.ini.php file for each of the 4 <code>key-store[]</code> variables in the <code>SECRETS</code> section.</p>

			<p>key-store (line 1)</p>
			<input type="text" value="<?php echo $ks1; ?>" size="40">
			<p>key-store (line 2)</p>
			<input type="text" value="<?php echo $ks2; ?>" size="40">
			<p>key-store (line 3)</p>
			<input type="text" value="<?php echo $ks3; ?>" size="40">
			<p>key-store (line 4)</p>
			<input type="text" value="<?php echo $ks4; ?>" size="40">

			<p>So replace:</p>

<pre>key-store[] = &quot;&quot;
key-store[] = &quot;&quot;
key-store[] = &quot;&quot;
key-store[] = &quot;&quot;</pre>

			<p>With:</p>

<pre>key-store[] = &quot;<?php echo $ks1; ?>&quot;
key-store[] = &quot;<?php echo $ks2; ?>&quot;
key-store[] = &quot;<?php echo $ks3; ?>&quot;
key-store[] = &quot;<?php echo $ks4; ?>&quot;</pre>

			<?php


		}

	?>

	<?php

		if( getCfg('lti')['oauth_clientid'] === "" || getCfg('app-secrets')['lti']['oauth_secret'] === "" ) {

			$needs_setup = true;

			?>

			<h2>OAuth Info</h2>
			<p>Below you will find your OAuth info for the LTI. These are stored separate from each other in the config.ini.php file. It is also separate from the oath info requested under [secrets].</p>
			<p><code>oauth_clientid</code> goes under the <code>LTI</code> section and <code>oauth_secret</code> goes under the <code>app-secrets</code> section.</p>

			<?php
				$o_id = generateToken(32);
				$o_tk = generateToken(32);
			?>

			<p>oauth_clientid</p>
			<input type="text" value="<?php echo $o_id; ?>" size="40">
			<p>oauth_secret</p>
			<input type="text" value="<?php echo $o_tk; ?>" size="40">

				<p>So under the <code>[lti]</code> section replace:</p>

<pre>oauth_clientid = &quot;&quot;</pre>

<p>With:</p>

<pre>oauth_clientid = &quot;<?php echo $o_id; ?>&quot;</pre>

<p>And under the <code>[app-secrets]</code> section replace:</p>

<pre>lti[oauth_secret] = &quot;&quot;</pre>

<p>With:</p>

<pre>lti[oauth_secret] = &quot;<?php echo $o_tk; ?>&quot;</pre>
			<?php


		}

	?>


	<?php

		if( !$needs_setup ) {

			$ga = new PHPGangsta_GoogleAuthenticator();
			$ga_name = hasData(getCfg("lti")["name"]) ? getCfg("lti")["name"] : "Library Help LTI";
			$ga_key = hasData(getCfg("secrets")['google-authenticator']) ? getCfg("secrets")['google-authenticator'] : $ga->createSecret();
			$ga_qrCodeUrl = $ga->getQRCodeGoogleUrl('Library Help LTI', $ga_key);

			?>

			<h2>Congratulations!</h2>
			<p>You have all the required fields configured in your config.ini.php file!</p>

			<p class="lti-settings"><a href="./" class="button">Go to Main Menu</a><a href="test-config.php" class="button">Next</a></p>

			<?php
			if ( !hasData(getCfg("secrets")['google-authenticator']) ) { // ga is not set up so go through process

				?>
				<h2>However, an Authenticator app is not yet set up.</h2>
				<p>To get started, download an Authenticator app (such as Google Authenticator or Microsoft Authenticator) to your mobile device from the Google Play Store (Android) or Apple App Store (iOS).</p>
				<p>Once downloaded and installed on your device you (and any other staff member who should be an administrator of the LTI needing access to the Tools section) should follow these steps:</p>
				<ol>
					<li>Open the Authenticator app on your device</li>
					<li>Add a new site</li>
					<li>Use the key below either by entering the key or scanning the QR code below into your Authenticator app:<br />
						KEY:<br />
						<input type="text" value="<?php echo $ga_key ?>" size="40"><br />
						QR Code:<br />
						<img src="<?php echo $ga_qrCodeUrl ?>"></li>
					<li>Important! Copy the key and place it in the google-authenticator field under the <code>secrets</code> section in the config.ini.php file and upload the config file to the server. This will be a new key each time you open this page UNTIL the config file is updated and uploaded. If you set this key in your app but fail to set it in the config it will not work and you won't be able to get the code back! Even though the variable is listed at google-authenticator, any authenticator app will do.
					<pre>google-authenticator = &quot;<?php echo $ga_key ?>&quot;</pre></li>
				</ol>
				<?php
			} else { // ga is set up so show code to add additional users
				?>
				<h2>Authenticator app is configured!</h2>
				<p>To add additional devices download and install an Authenticator app (such as Google Authenticator or Microsoft Authenticator) and use the following steps:</p>
				<ol>
					<li>Open the Authenticator app on your device</li>
					<li>Add a new site</li>
					<li>Use the code below either by entering the key or scanning the QR code below into your Authenticator app:<br />
						KEY:<br />
						<input type="text" value="<?php echo $ga_key ?>" size="40"><br />
						QR Code:<br />
						<img src="<?php echo $ga_qrCodeUrl ?>"></li>
				</ol>
				<?php
			} ?>

			<p>While not required, it is strongly suggested that an Authenticator is set up in order to protect against brute force logins.
			The Authenticator app provides a secret, one time token to be used for each login. The secret is stored in the configuration file,
			verified locally on this server, operates independently of Google services, does not send any information to Google, and a Google account is not required.</p>
			<p>Note: Once the google-authenticator key is set in the config.ini.php file the Authenticator code provided from the app will be required by anyone logging in.
			You can set up the same code on multiple devices either now or by returning to this page in the future. If the code is ever lost and no one is able to log in you may
			clear out the authentication code in the config.ini.php file and log in without it before going through the steps to set it up again.</p>
			<p>It is advised that at least two (2) trusted staff members have Authenticator set up in the event a staff member leaves or is unavailable. However, what can be done in
			this tools section is minimal after the initial set up and to reset passwords just clear them out of the config.ini.php file to run through the set-up again.</p>
			<p>Controlling authentication to <code>/tools</code>is a lost cause if access to the files on the server is not restricted.</p>

			<h2>Need to reset some values?</h2>

			<p>If you feel like you need to reset and regenerate the admin password, Authenticator key, OAuth keys, or key-stores, remove the values from the config.ini.php file (leave them blank/empty) and reload this page. If any of those required fields are blank in the config file you will be prompted to fill them in with new values from this page.</p>
			<p>Upload the config file (with necessary empty values) and click the Reload button below.</p>

			<p class="lti-settings"><a href="" class="button">Reload</a></p>

			<?php


		} else {

			?>

			<h2>Update and Upload config file</h2>

			<p>Please add the values above to the config.ini.php file in <code>custom/</code> and then upload. Once uploaded click the Reload button below.</p>

			<p class="lti-settings"><a href="" class="button">Reload</a></p>

			<?php
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
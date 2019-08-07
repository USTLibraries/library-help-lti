<?php

require_once(__DIR__."/lib/oauth_validator/lib/oauth-validator.php");


/**
 * sanitize_hash function.
 *
 * Our app will have user input oauth credentials so we have a function that takes a hash string paremeter and sanitize it
 * 
 * @access public
 * @param string $hash a hash
 * @return string the sanitized hash
 */

if ( !function_exists( 'sanitize_hash' ) ){
	function sanitize_hash($hash){
		return filter_var($hash, FILTER_CALLBACK, ['options' => function($hash) {
	    	return preg_replace('/[^a-zA-Z0-9$\/.]/', '', $hash);
		}]);
	}
}

/**
 * hash_equals function.
 *
 * The function hash_equals is only available from php 5.6 and on, so we need an alternative if php < 5.6
 * 
 * @access public
 * @param string $hash_a a hash
 * @param string $hash_b a hash to compare to
 * @return boolean are they equal
 */

	// 
	// http://php.net/manual/en/function.hash-hmac.php#111435
if (!function_exists( 'hash_equals') ) {
	function hash_equals($hash_a, $hash_b) {
		/* The reason $a === $b is not desired is that a timing attack is possible.
		   Using === compares characters sequentially 1 by 1 until it doesn't find a match.
		   So if an attacker were paying attention they'd be able to lock in characters in order
		   Obviously, in normal code, once we determine that the 3rd character (abcde === abzde)
		   does not match we should stop for performance/efficency but when we are using crypto 
		   functions we need to fudge efficency so as to not give hints.
		*/
		
		$isEqual = false;
		
		if (is_string($hash_a) && is_string($hash_b)) { // they are both strings

			$len = strlen($hash_a);
			if ($len === strlen($hash_b)) { // they are the same length
				
				// go through the motions of comparing ALL characters, even if we already found a mismatch and know they aren't equal
				$status = 0;
				for ($i = 0; $i < $len; $i++) {
					$status |= ord($hash_a[$i]) ^ ord($hash_b[$i]);
				}
				$isEqual = ($status === 0);
				
			}
			
		}
		
		return $isEqual;
		
	}
} 

/**
 * isRegex function.
 *
 * This function checks to see if a string is in regex format. It does not check validity
 * 
 * @access public
 * @param string $str a string to check
 * @return boolean true if it is regex
 */

if (!function_exists( 'isRegex') ) {
    
    // https://stackoverflow.com/questions/10778318/test-if-a-string-is-regex
    function isRegex($str) {
        $regex = "/^\/[\s\S]+\/$/";
        return preg_match($regex, $str);
    }
}

class ToolKit {
	
	function __construct() {
		
	}
	
	function timestamp($tz = "UTC") {

		date_default_timezone_set($tz);
		
		$date = new DateTime();
		return $date->format('YmdHis');
	}
	
	
	/* **************************************************************************
	 *  Validate an OAuth request (Client)
	 */
	
	function validateOAuth($clientId, $clientSecret) {
		
		$result = false;
				
		// if version 1.0
		if( isset($_POST['oauth_version']) && $_POST['oauth_version'] = "1.0") {
						
			\Jublo\Oauth_Validator::setConsumerKey($clientId, $clientSecret);
			$ov = \Jublo\Oauth_Validator::getInstance();

			// we are going to pass all the post parameters except oauth_signature (per OAuth 1.0 signing guidelines)
			$params = $_POST;
			foreach ($params as $key => $value) {
 				if( $key === "oauth_signature") { unset($params[$key]); }
			} // the $ov-validate() function sorts the $params as necessary (per OAuth 1.0 signing guidelines)
			// https://oauth1.wp-api.org/docs/basics/Signing.html

			$authorization = 'OAuth oauth_consumer_key="'.$_POST['oauth_consumer_key'].'", oauth_nonce="'.$_POST['oauth_nonce'].'", oauth_signature="'.urlencode($_POST['oauth_signature']).'", oauth_signature_method="'.$_POST['oauth_signature_method'].'", oauth_timestamp="'.$_POST['oauth_timestamp'].'", oauth_version="'.$_POST['oauth_version'].'"';

			$result = $ov->validate($authorization, 'POST', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'', $params);
			
		} else {
			// not oauth_version 1.0
			// compatibility for other versions not yet added
		}
		
		return $result;
		
	}
    
	/* **************************************************************************
	 *  Generate an OAuth Signature (Provider)
     *  In accordance with OAuth specifications, the nonce needs to be recorded
     *  and kept and checked for a certain amount of time to prevent replay attacks
     *  The nonce is not recorded here. It is returned with the signature
     *
     *  Currently only supports ver 1.0
     *  https://oauth.net/core/1.0a/
	 */
    
    function generateOAuthSignature($clientId, $clientSecret, $params, $requestUrl, $token = "", $method = "POST", $signatureMethod = "HMAC-SHA1", $version = "1.0") {

        $nonce = $this->generateToken(16);
        $timestamp = time();
        $method = strtoupper($method);
        $requestUrl = strtolower($requestUrl);
        
        $oauthFields = array();
            
        // Gather all of the oauth parameters together, then add the oauth_token if one will be present.
        // These will then be copied into the parameters array, yet maintained separately so that we can
        // return these fields in the end (as they will need to be sent with the request)
        // The oauth_signature field will be added to this array as soon as the signature is generated.
        $oauth_params    = array(
            'oauth_consumer_key'     => $clientId,
            'oauth_version'          => $version,
            'oauth_timestamp'        => $timestamp,
            'oauth_nonce'            => $nonce,
            'oauth_signature_method' => $signatureMethod
        );
    
        // add the token to the $oauth_params if present
        if ($token != "") {
            $oauth_params['oauth_token'] = $token;
        }
        
        // copy these into the $params array (but keep a separate copy of oauth_params)
        $paramBase = array_merge($params, $oauth_params);
        
        // Generate the string to be signed
        // https://oauth1.wp-api.org/docs/basics/Signing.html
        // https://tools.ietf.org/html/rfc5849#section-3.4.1.1
        // https://oauth.net/core/1.0a/
        // Concatenate the Method (uppercase POST or GET) + Request URL (no query string) + Parameters
        // METHOD: Uppercase HTTP Method
        // URL: Lowercase scheme and host, port excluded if 80 for HTTP or 443 for SSL
        // GET or POST parameters (those passed in $param) are form encoded (a=b&c=d not JSON). Encode the name and value for each, sort by name (and value for duplicate keys). Combine key and value with a =, then concatenate with & into a string
        // NOTE: since this implementation expects unique keys, we are only sorting by the keys, not values.
        $paramString = "";
        ksort($paramBase);
        foreach ($paramBase as $key => $value) {
            $paramString .= $this->oauth_encode($key) . "=" . $this->oauth_encode($value) . "&";
        }
        $paramString = substr($paramString, 0, -1); // remove trailing &
        
        // Encode and concatenate with &: method & request URL & parameter string. Yes, parameter string key and values will be double encoded
        // Example result: POST&http%3A%2F%2Fexample.com%2Fwp-json%2Fwp%2Fv2%2Fposts&oauth_consumer_key%3Dkey%26oauth_nonce%3Dnonce%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D123456789%26oauth_token%3Dtoken
        // Note: RFC 5849 (OAuth 1.0) allows for a custom method, but requires it to be encoded
        $oauthBaseString = $this->oauth_encode($method) . "&" . $this->oauth_encode($requestUrl) . "&" . $this->oauth_encode($paramString);
        
        // Create the signing key
        // The signature key for HMAC-SHA1 is created by taking the client/consumer secret and the token secret, URL-encoding each, then concatenating them with & into a string.
        // This process is always the same, even if you don't have a token.
        // For example, if your client secret is abcd and your token secret is 1234, the key is abcd&1234. If your client secret is abcd, and you don't have a token yet, the key is abcd&
        $oauthSigningKey = $this->oauth_encode($clientSecret) . "&" . $this->oauth_encode($token);
        
        // Using the Base String and Signing Key, generate a base 64 encoded signature using sha1
        $oauthSignature = base64_encode(hash_hmac("sha1", $oauthBaseString, $oauthSigningKey, true));
        
        // now that we have a signature, we can add it to both parameter arrays
        $oauth_params['oauth_signature'] = $oauthSignature; // add signature to oauth field set
        
        // for loop to add all OAuth parameters (including signature) for authorization header  
        $authorizationString = "OAuth ";
        foreach ($oauth_params as $key => $value) {
            $authorizationString .= $this->oauth_encode($key) . "=\"" . $this->oauth_encode($value) . "\", ";
        }
        $authorizationString = substr($authorizationString, 0, -2); // remove trailing , and space

        $oauthFields['authorization'] = $authorizationString;
        $oauthFields['fields'] = $oauth_params;

        return $oauthFields;
    }
    
    function oauth_encode($stringToEncode) {
        // we can extend this further for special cases
        $r = rawurlencode($stringToEncode);
        return $r;
    }
	
	/* *********************************************************************
	*/
	function generateDataSignature($data, $secret, $nonce = "", $timestamp = "", $algorithm = "sha512") {
		
		if ($nonce === "") {
			$nonce = $this->generateToken(24);
		}
		
		if ($timestamp === "") {
			$timestamp = $this->timestamp();
		}
		
		$dataToSign = "";
		
		if (is_array($data)) {
			$dataToSign = $this->arrayToRequestString($data);
		} else {
			$dataToSign = (string) $data; // number, or string okay
		}
		
		$string = $dataToSign."&nonce=".$nonce."&timestamp=".$timestamp;

		return "$".$algorithm."$".$nonce."$".$timestamp."$".base64_encode(hash_hmac($algorithm, $string, $secret, true));
	}
	
	function validateDataSignature($data, $passedSignature, $secret) {
		
		$arr = explode("$", $passedSignature); // note pos 0 will be empty because of leading $
		$algorithm = $arr[1]; // first section (0+1)
		$nonce = $arr[2]; // second section (1+1)
		$timestamp = $arr[3]; // third section (2+1)
		// and we don't care about the rest, also note that the "rest" could contain $ as well
		
		$mySignature = $this->generateDataSignature($data, $secret, $nonce, $timestamp, $algorithm);
		
		return hash_equals($mySignature, $passedSignature);
	}
	
	function arrayToRequestString($data) {
		ksort($data); // we're going to alphabetize for comparasion reasons
		$request = "";
		foreach ($data as $key => $value) {
      		$request .= $key . '=' . $value . '&';
    	}
		
		return substr($request, 0, -1); // remove last &;
	}

	/* **************************************************************************
	 *  GENERATE A PASSWORD - RANDOM STRING FOR HUMAN
	 *  It is named this for a reason. It should not be used for cryptographic keys that don't need to be read or typed in by human hands
	 */

	function generatePassword($length = 64, $alphabet = 'abdefghkmnpqrtyABDEFGHJKLMNPQRTY23456789!@#$%&*+-?.=~') { // we removed confusing characters, but added some specials

		return $this->generateString($alphabet, $length);

	}

	function generateKey($bytesLen = 128) {

		return openssl_random_pseudo_bytes ($bytesLen);

	}

	// like a key but only alphanumeric
	function generateToken($length = 64, $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890") {

		return $this->generateString($alphabet, $length);

	}

	function generateHex($bytesLen = 128) {

		return bin2hex(openssl_random_pseudo_bytes ($bytesLen));

	}

	function generateString($alphabet, $length) {

		// https://stackoverflow.com/questions/6101956/generating-a-random-password-in-php

		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < $length; $i++) {
			$n = $this->selectRandom($alphaLength);
			$pass[] = $alphabet[$n];
		}

		return implode($pass); //turn the array into a string
	}

	function encrypt($plaintext, $key) {
		//AES-256-CBC

		$encrypted = [ "cipher" => "AES-128-CBC", 
					   "iv" => "",
					   "ciphertext" => "",
					   "tag" => ""
					 ];

		if (in_array($encrypted['cipher'], openssl_get_cipher_methods())) {
			$ivlen = openssl_cipher_iv_length($encrypted['cipher']);
			$encrypted['iv'] = openssl_random_pseudo_bytes($ivlen);
			$encrypted['ciphertext'] = openssl_encrypt($plaintext, $cipher, $key, 0, $encrypted['iv']);
		}



		return $encrypted;
	}

	function decrypt($encrypted, $key) {

		return openssl_decrypt($encrypted['ciphertext'], $encrypted['cipher'], $key, 0, $encrypted['iv']);

	}

	/* **************************************************************************
	 *  SELECT A RANDOM INT
	 *  This application is built for php 5.4 so the newest cryptographically secure
	 *  random number generator is not yet available until php 7.
	 *  In the meantime we are using a library from paragonie
	 *  https://github.com/paragonie/random_compat
	 *  That library is stored in inc/random_compat/
	 */

	function selectRandom($upper = 255) {

		if ($upper > 255) { $upper = 255; }
		$int = 0;

		try {
			$int = random_int(0, $upper);
		} catch (TypeError $e) {
			// Well, it's an integer, so this IS unexpected.
			die("An unexpected error has occurred"); 
		} catch (Error $e) {
			// This is also unexpected because 0 and 255 are both reasonable integers.
			die("An unexpected error has occurred");
		} catch (Exception $e) {
			// If you get this message, the CSPRNG failed hard.
			die("Could not generate a random int. Is our OS secure?");
		}

		return $int;
	}
	
/*  ============================================================================================
    ********************************************************************************************
    GENERATE FORM HTML 
	******************************************************************************************** 
*/
	
	function generateCheckBox($name, $value, $text, $desc = "", $attr = "" ) {
		
		$idname = ($value !== "") ? $name."-".preg_replace("/[^A-Za-z0-9]/", "", $value) : $name;
		
		$input = $this->generateInputField("checkbox", $idname, $value, $text, $desc, $attr);
		$html = str_replace("name=\"".$idname."\"", "name=\"".$name."\"", $input);
		
		return $html;
	}
	
	function generateRadioButton($name, $value, $text, $desc = "", $attr = "" ) {
		
		$idname = ($value !== "") ? $name."-".preg_replace("/[^A-Za-z0-9]/", "", $value) : $name;
		
		$input = $this->generateInputField("radio", $idname, $value, $text, $desc, $attr);
		$html = str_replace("name=\"".$idname."\"", "name=\"".$name."\"", $input);
		
		return $html;
	}
	
	function generateTextInputField($name, $value = "", $text, $desc = "", $attr = "") {
		return $this->generateInputField("text", $name, $value, $text, $desc, $attr);
	}
	
	function generateInputField($type, $name, $value, $text, $desc = "", $attr = "" ) {

		$field = "<input id=\"field_".$name."\" aria-labelledby=\"label_".$name."\" ";
		if ( $desc !== "" ) { $field .= "aria-describedby=\"desc_".$name."\" "; }
		$field .= "name=\"".$name."\" type=\"".$type."\" value=\"".$value."\" tabindex=\"1\" ".$attr. ">\n";
		
		$label = "<label id=\"label_".$name."\" for=\"field_".$name."\">".$text."</label>";
		
		$span = ( $desc !== "" ) ? "<span  id=\"desc_".$name."\" tabindex=\"-1\">".$desc."</span>" : "";
		
		return ($type === "text") ? $label.$field.$span : $field.$label.$span;
	}
	
}

?>
<?php

require_once __DIR__."/../custom/inc.php"; // this is required to be placed at start of execution - it loads the config, app vars, core app functions, and init



$data['fruit1'] = "banana";
$data['fruit2'] = "pear";
$data['fruit3'] = "apple";
$data['fruit4'] = "strawberry";

$signature1 = generateDataSignature($data);

//$data['fruit4'] = "tomato";

$isValid = validateDataSignature($data, $signature1);
		
if($isValid) {
	echo "Valid";
} else {
	echo "Not valid";
}

?>
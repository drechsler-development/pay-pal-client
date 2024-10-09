<?php

// might be modified to your path where the vendor autoload.php is located
//require 'vendor/autoload.php';
session_start ();

// in your casse you might be modified to your path where the vendor autoload.php is located and using the autoloader
//require 'vendor/autoload.php';

//As my tests are in the same project, we cannot use autoloader and I will include the classes manually
//you can delete that on your site, when you work in your own project

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPal.php';

require 'payPalConfig.php';

try {

	//In case we placed the order and the user authorized the payment, we need to capture the order
	//Hence we need to extract the token and PayerID from the URL

	$token = $_GET['token'] ?? '';
	$payerId = $_GET['PayerID'] ?? '';

	if(empty($token)){
		throw new Exception("No 'token' in the url or it was empty");
	}

	if(empty($payerId)){
		throw new Exception("No 'PayerID' in the url or it was empty");
	}

	$orderId = $_SESSION['orderId'] ?? '';

	if(empty($orderId)){
		throw new Exception('No orderId in the session');
	}

	$paypal = new \DD\PayPal(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET, PAYPAL_IS_SANDBOX);
	$paypal->IsOrderApproved ($orderId);

	$paypal->CaptureOrder ($orderId);

	echo "SUCCESS";

} catch (Exception $e){
	echo $e->getMessage ();

}

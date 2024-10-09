<?php

session_start ();

use DD\PayPal;
use DD\PayPalAmount;
use DD\PayPalItem;
use DD\PayPalOrder;
use DD\PayPalPaymentSource;
use DD\PayPalPurchaseUnit;

// in your casse you might be modified to your path where the vendor autoload.php is located and using the autoloader
//require 'vendor/autoload.php';

//As my tests are in the same project, we cannot use autoloader and I will include the classes manually
//you can delete that on your site, when you work in your own project

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPal.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPalAmount.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPalItem.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPalOrder.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPalPaymentSource.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/DD/PayPalPurchaseUnit.php';

require 'payPalConfig.php';

try {

	$item1Amount    = new PayPalAmount('1000');
	$item1TaxAmount = new PayPalAmount('100');

	$item2Amount    = new PayPalAmount('2000');
	$item2TaxAmount = new PayPalAmount('200');


	$item1 = new PayPalItem('Test Item 1', '1', $item1Amount, $item1TaxAmount);
	$item2 = new PayPalItem('Test Item 2', '1', $item2Amount, $item2TaxAmount);

	$orderAmount = new PayPalAmount('30.00');

	$purchaseUnit = new PayPalPurchaseUnit('REF1231', 'Test Purchase Unit', (array)$orderAmount);
	/*$purchaseUnit->AddItem ($item1);
	$purchaseUnit->AddItem ($item2);*/

	$paymentSource = new PayPalPaymentSource('Test Brand Name', 'https://' . $_SERVER['HTTP_HOST'] . '/src/test/success', 'https://' . $_SERVER['HTTP_HOST'] . '/src/test/cancel', 'de-DE', 'LOGIN', 'NO_SHIPPING', 'PAY_NOW');

	$order = new PayPalOrder($purchaseUnit, $paymentSource);

	$paypal = new PayPal(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET, PAYPAL_IS_SANDBOX);

	$response = $paypal->PostCreateOrder ($order);

	//Get OrderId from response and dsave it into a session to capture the order later on the success site
	$_SESSION['orderId'] = $paypal->GetOrderId ($response);

	//Get Approval Link from Response
	echo $approveLink = $paypal->GetApprovalLink ($response);

	//Redirect to PayPal Approval Link
	header ("Location: $approveLink");
	exit();

} catch (Exception $e) {
	echo $e->getMessage ();
}

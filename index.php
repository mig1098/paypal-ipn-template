<?php
/**
 * Angell EYE - PayPal IPN for FileMaker
 */

require_once('includes/config.php');

// Connect to FileMaker
require_once('vendor/matatirosoln/filemaker-api/FileMaker.php');
$fm =  new FileMaker('PayPal_IPN', FM_HOST, FM_USER, FM_PASS);
  
// Read the post from PayPal system and add 'cmd'   
$req = 'cmd=_notify-validate';   
  
// Store each $_POST value in a NVP string: 1 string encoded and 1 string decoded   
$ipn_data_array = array();
foreach ($_POST as $key => $value)   
{   
 $value = urlencode(stripslashes($value));   
 $req .= "&" . $key . "=" . $value;   
 $ipn_email .= $key . " = " . urldecode($value) . '<br />';  
 $ipn_data_array[$key] = urldecode($value);
}

// Validate IPN with PayPal
require_once('validate.php');

// If there was a problem connecting to the database email site admin with the mysql error info and the raw IPN data.  Then exit.
$error_email_body = '';
if(count($db -> errors) > 0)
{	
	foreach($db -> errors as $error) 
		$error_email_body .= $error . '<br />';
		
	$mail -> Subject  =  'PayPal IPN : Connection to database failed!';
	$mail -> Body =  $error_email_body . '<br /><br />' . $ipn_email;
	$mail -> AddAddress($admin_email_address, $admin_name);
	$mail -> Send();
	$mail -> ClearAddresses();
	
	exit();
}
  
// Load IPN data into PHP variables
require_once('parse-ipn-data.php');

// Store RAW IPN log in the DB
$ipn_log_data['ipn_data_serialized'] = $ipn_serialized;
$ipn_log_data_id = $db -> query_insert('raw_log', $ipn_log_data);

// Check for disputes/chargebacks/chargeback-reversals
if(
   strtoupper($txn_type) == 'NEW_CASE' || 
   strtoupper($payment_status) == 'REVERSED' || 
   strtoupper($payment_status) == 'CANCELED_REVERSAL' || 
   strtoupper($txn_type) == 'ADJUSTMENT'
   )
	require_once('dispute.php');
	
// Check if this was a refund.  
// Flag that it's a refund so you can skip entering a new order record in order.php
if(strtoupper($reason_code) == 'REFUND')
	require_once('refund.php');

// Check if this was a mass payment
if(strtoupper($txn_type) == 'MASSPAY')
	require_once('mass-payment.php');

// Check for subscription sign-up
if(
   strtoupper($txn_type) == 'SUBSCR_SIGNUP' || 
   strtoupper($txn_type) == 'SUBSCR_FAILED' || 
   strtoupper($txn_type) == 'SUBSCR_CANCEL' || 
   strtoupper($txn_type) == 'SUBSCR_EOT' || 
   strtoupper($txn_type) == 'SUBSCR_MODIFY'
   )
	require_once('subscr.php');

// Check for subscription payment
if(strtoupper($txn_type) == 'SUBSCR_PAYMENT')
	require_once('subscr-payment.php');
	
// Check for new recurring payment profile
if(
   strtoupper($txn_type) == 'RECURRING_PAYMENT_PROFILE_CREATED' || 
   strtoupper($txn_type) == 'RECURRING_PAYMENT_PROFILE_CANCEL' || 
   strtoupper($txn_type) == 'RECURRING_PAYMENT_PROFILE_MODIFY'
   )
	require_once('recurring-payment-profile.php');
	
// Check for recurring payment
if(
   strtoupper($txn_type) == 'RECURRING_PAYMENT' || 
   strtoupper($txn_type) == 'RECURRING_PAYMENT_SKIPPED' || 
   strtoupper($txn_type) == 'RECURRING_PAYMENT_FAILED' || 
   strtoupper($txn_type) == 'RECURRING_PAYMENT_SUSPENDED_DUE_TO_MAX_FAILED_PAYMENT'
   )
	require_once('recurring-payment.php');
	
// Any other type of IPN can be treated as a normal order
// Refunds come back with the same txn_type of the original payment so we skip order.php 
// for refunds because refund.php will take care of updating the existing record data
if(strtoupper($reason_code) != 'REFUND' && 
   (
	strtoupper($txn_type) == 'CART' || 
   	strtoupper($txn_type) == 'EXPRESS_CHECKOUT' || 
   	strtoupper($txn_type) == 'VIRTUAL_TERMINAL' || 
   	strtoupper($txn_type) == 'WEB_ACCEPT' || 
	strtoupper($txn_type) == 'SEND_MONEY'
	)
   )
	require_once('order.php');
	
// Check for Adaptive Account creation
if($account_key != '')
	require_once('adaptive-accounts.php');

// If there were any errors adding data to the DB send an email to the site admin.
$error_email_body = '';
if(count($db -> errors) > 0)
{	
	foreach($db -> errors as $error) 
		$error_email_body .= $error . '<br />';
		
	$mail -> Subject  =  'PayPal IPN : Error(s) adding data to database.';
	$mail -> Body =  $error_email_body . '<br /><br />' . $ipn_email;
	$mail -> AddAddress($admin_email_address, $admin_name);
	$mail -> Send();
	$mail -> ClearAddresses();
}
else
{
	$mail -> Subject  =  'PayPal IPN : Completed Successfully';
	$mail -> Body = $ipn_email;
	$mail -> AddAddress($admin_email_address, $admin_name);
	$mail -> Send();
	$mail -> ClearAddresses();
}
	
$db -> close();
?>
<?php
// Validate with curl
$curl_result=$curl_err='';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,PAYPAL_HOST . '/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
curl_setopt($ch, CURLOPT_HEADER , 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, SSL);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$curl_result = curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

//are we verified? If so, let's process the IPN
if(strpos($curl_result, "VERIFIED")!==FALSE)
{
	$valid = TRUE;
}
else
{
	$valid = FALSE;
}
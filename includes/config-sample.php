<?php
$sandbox = !empty($_POST['test_ipn']) ? TRUE : FALSE;
define('SANDBOX', $sandbox);

$domain = SANDBOX ? 'http://sandbox.domain.com' : 'http://www.domain.com';
define('DOMAIN', $domain);
define('DEBUG', TRUE);

if(DEBUG)
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$admin_email = '';
$admin_name = '';
$developer_email = '';
$developer_name = '';

$fm_host = SANDBOX ? '' : '';
$fm_username = SANDBOX ? '' : '';
$fm_password = SANDBOX ? '' : '';

$log_results = TRUE;
$log_path = 'log/';
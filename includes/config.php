<?php
$sandbox = !empty($_POST['test_ipn']) ? TRUE : FALSE;
define('SANDBOX', $sandbox);

$domain = SANDBOX ? 'http://fmipn.angelleye.com' : 'http://fmipn.angelleye.com';
define('DOMAIN', $domain);
define('DEBUG', TRUE);

if(DEBUG)
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$admin_email = 'andrew+admin@angelleye.com';
$admin_name = 'Andrew Angell';
$developer_email = 'andrew@angelleye.com';
$developer_name = 'Andrew Angell';

if(SANDBOX)
{
    define('FM_HOST', 'filemaker.gearbrokers.com');
    define('FM_USER', 'php');
    define('FM_PASS', '1111');
}
else
{
    define('FM_HOST', 'filemaker.gearbrokers.com');
    define('FM_USER', 'php');
    define('FM_PASS', '1111');
}

define('LOG_RESULTS', TRUE);
define('LOG_PATH', '/home/angelley/fmipn/log/');
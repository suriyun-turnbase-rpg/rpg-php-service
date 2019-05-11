<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Origin, Cache-Control, X-Requested-With, Content-Type, Access-Control-Allow-Origin');
header('Access-Control-Allow-Methods: *');
header('Content-type: application/json');

require_once('config.php');
$f3 = require_once('fatfree/lib/base.php');

if ((float)PCRE_VERSION < 7.9) {
    trigger_error('PCRE version is out of date');
}

// Read configs
$f3->config('configs/config.ini');

// Prepare database
$f3->set('DB', new DB\SQL('mysql:'.
    'host='.$GLOBALS['db_host'].';'.
    'port='.$GLOBALS['db_port'].';'.
    'dbname='.$GLOBALS['db_name'], 
    $GLOBALS['db_user'], 
    $GLOBALS['db_pass']));

// Services
$f3->set('AUTOLOAD', 'databases/|functions/|jwt/');
$f3->route('GET /', function() {
    echo ';)';
});

$f3->run();
?>
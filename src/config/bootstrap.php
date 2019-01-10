<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 08/01/2019
 * Time: 13:20
 */

$autoload = require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";


/*
|--------------------------------------------------------------------------
| Process name
|--------------------------------------------------------------------------
*/
cli_set_process_title('sauron-tase');


/*
|--------------------------------------------------------------------------
| Logs DB
|--------------------------------------------------------------------------
|
| Require MongoDB constants where the logs will be registered
|
*/
require __DIR__ . '/db.php';


/*
|--------------------------------------------------------------------------
| AWS Configurations
|--------------------------------------------------------------------------
|
| Configure AWS secret and key to send SMS as a contingency
|
*/
require __DIR__ . '/aws.php';

/*
|--------------------------------------------------------------------------
| SMS Configurations
|--------------------------------------------------------------------------
|
| Configure where is the sms sender service
|
*/
require __DIR__ . '/sms.php';


/*
|--------------------------------------------------------------------------
| Socket Port
|--------------------------------------------------------------------------
|
| Socket port to check if the system is up
|
*/
define('SOCKET_PORT', 25004);

/*
|--------------------------------------------------------------------------
| Directory
|--------------------------------------------------------------------------
|
| Directory to load the programs
|
*/
$dir = 'app' . DIRECTORY_SEPARATOR . 'Programs' . DIRECTORY_SEPARATOR;

/*
|--------------------------------------------------------------------------
| Set time execution
|--------------------------------------------------------------------------
*/
set_time_limit(0);
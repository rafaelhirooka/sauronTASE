<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 08/01/2019
 * Time: 13:20
 */

$autoload = require_once "../vendor/autoload.php";

/**
 * MongoDB host address to register logs
 */
define('MONGO_HOST', '127.0.0.1');

/**
 * MongoDB port to register logs
 */
define('MONGO_PORT', 27017);

/**
 * Socket port to check if the system is up
 */
define('SOCKET_PORT', 25004);

/**
 * Directory to load the programs
 */
$dir = 'app' . DIRECTORY_SEPARATOR . 'Programs' . DIRECTORY_SEPARATOR;


<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 10/01/2019
 * Time: 10:14
 */


/*
|--------------------------------------------------------------------------
| AWS key
|--------------------------------------------------------------------------
*/
define('AWS_KEY', getenv('AWS_ACCESS_KEY'));


/*
|--------------------------------------------------------------------------
| AWS secret
|--------------------------------------------------------------------------
*/
define('AWS_SECRET', getenv('AWS_SECRET_ACCESS_KEY'));

/*
|--------------------------------------------------------------------------
| AWS TopicArn
|--------------------------------------------------------------------------
|
| TopicArn is a communication channel to send messages and subscribe to notifications
|
*/
define('AWS_TOPIC_ARN', 'arn:aws:sns:us-east-1:398709985239:monitoring-system');
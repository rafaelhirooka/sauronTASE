<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 09/01/2019
 * Time: 08:04
 */

use PHPUnit\Framework\TestCase;
use App\Classes\v1\Logger;
use App\Classes\v1\DB\MongoController;

class LoggerTest extends TestCase {

    public function testLog() {
        $logger = new Logger(new MongoController('127.0.0.1', 27017));
        $logger->log();
    }
}

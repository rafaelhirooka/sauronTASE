<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 09/01/2019
 * Time: 08:04
 */

use PHPUnit\Framework\TestCase;
use App\Classes\v1\Logger;

class LoggerTest extends TestCase {

    public function testLog() {
        $logger = new Logger();
        $logger->log();
    }
}

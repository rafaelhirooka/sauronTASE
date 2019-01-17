<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 02/01/2019
 * Time: 15:58
 */

namespace App\Classes\v1;


use App\Classes\v1\DB\MongoController;
use App\Interfaces\Program;
use Composer\Autoload\ClassLoader;

class Healer extends \Volatile implements Program {

    private $className;

    private $thread;

    private $loader;

    private $logger;

    private $time;

    private $name;

    public function __construct($className, \Thread &$thread, ClassLoader $loader) {
        try {
            $this->thread = $thread;
            $this->className = $className;
            $this->loader = $loader;
            $this->logger = new Logger(new MongoController(MONGO_HOST, MONGO_PORT));
        } catch (\Exception $e) {

        }
    }

    public function setTime(int $time) {
        $this->time = $time;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function run() {
        $penalty = 0;
        while(true) {
            try {
                if (!$this->thread->isRunning()) {
                    $penalty++;

                    $this->logger->log('info', "Thread {$this->className} parou - $penalty");
                }

                if ($penalty >= 3) {
                    $penalty = 0;

                    $this->thread = new $this->className($this->loader);
                    $this->thread->setName($this->className);
                    $this->thread->start();
                    $this->logger->log('info', "Thread {$this->className} reiniciada pelo Healer");
                }

                sleep($this->time);

            } catch (\Exception $e) {
                $this->logger->log('info', "Thread {$this->className} parou. Erro: " . $e->getMessage());
            }
        }
    }
}
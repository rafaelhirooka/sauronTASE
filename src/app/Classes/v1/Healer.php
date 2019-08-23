<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 02/01/2019
 * Time: 15:58
 */

namespace App\Classes\v1;

use App\Interfaces\Program;
use Composer\Autoload\ClassLoader;

class Healer extends \Volatile implements Program {

    /**
     * Class name to healer
     *
     * @var string
     */
    private $className;

    /**
     * Thread to check if is up
     *
     * @var \Thread
     */
    private $thread;

    /**
     * Composer autoload
     *
     * @var ClassLoader
     */
    private $loader;

    /**
     * Logger class to register logs
     *
     * @var Logger
     */
    private $logger;

    /**
     * Frequency that the program will be executed
     *
     * @var int
     */
    private $time;

    /**
     * Program name
     *
     * @var string
     */
    private $name;

    /**
     * Healer constructor.
     *
     * @param $className
     * @param \Thread $thread
     * @param ClassLoader $loader
     */
    public function __construct($className, \Thread &$thread, ClassLoader $loader) {
        try {
            $this->thread = $thread;
            $this->className = $className;
            $this->loader = $loader;
            $this->logger = new Logger();
        } catch (\Exception $e) {

        }
    }

    /**
     * Set time
     *
     * @param int $time
     */
    public function setTime(int $time) {
        $this->time = $time;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * Check if the program is alive
     *
     * If the program is dead 3 times, start a new one
     */
    public function run() {
        $penalty = 0;
        while(true) {
            try {
                if (!$this->thread->isRunning()) { // check if program is running
                    $penalty++;

                    $this->logger->log('info', "Thread {$this->className} parou - $penalty");
                }

                if ($penalty >= 3) { // 3 times
                    $penalty = 0;

                    // Start a new one
                    $this->thread = new $this->className($this->loader);
                    $this->thread->setName($this->className);
                    $this->thread->start();
                    $this->logger->log('info', "Thread {$this->className} reiniciada pelo Healer");
                }

                sleep($this->time); // wait
            } catch (\Exception $e) {
                $this->logger->log('info', "Thread {$this->className} parou. Erro: " . $e->getMessage());
            }
        }
    }
}
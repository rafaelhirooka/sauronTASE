<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 14/12/2018
 * Time: 10:57
 */

namespace App\Classes\v1;


use App\Classes\v1\DB\MongoController;
use App\Interfaces\Program;
use Aws\Sns\SnsClient;
use Composer\Autoload\ClassLoader;

abstract class AbstractProgram extends \Thread implements Program {

    /**
     * Program name
     *
     * @var string
     */
    protected $name;

    /**
     * Composer autoload
     *
     * @var ClassLoader
     */
    protected $loader;

    /**
     * Logger class to register logs
     *
     * @var Logger
     */
    public $logger;

    /**
     * Frequency that the program will be executed
     *
     * @var int
     */
    protected $time = 60;

    /**
     * AbstractProgram constructor.
     *
     * @param ClassLoader $loader
     */
    public function __construct(ClassLoader $loader) {
        try {
            $this->loader = $loader;

            $this->logger = new Logger(new MongoController(MONGO_HOST, MONGO_PORT));
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
     * Set program name
     *
     * @param string $name
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * Get logger class to register logs
     *
     * @return Logger
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * This is the primary way to send SMS
     * Send socket to SMS sender server
     *
     * @param array $json
     */
    protected function send(array $json) {
        try {
            $sender = new SendSocket($this->loader, $this, $json);

            $sender->start();

        } catch (\Exception $e) {
            $this->logger->log('error', $e->getMessage());
        }
    }

    /**
     * This is the second way to send SMS
     *
     * If the send() method don't has success
     * the message will be sent using this method
     *
     * This uses AWS SNS
     *
     * @param array $json
     */
    public function sendContingency(array $json) {
        try {
            $this->logger->log('info', 'Sending using AWS');

            $client = new SnsClient([
                'profile' => 'profile-sauron',
                'region' => 'us-east-1',
                'version' => '2010-03-31'
            ]);

            foreach ($json as $item) {
                $client->publish([
                    'Message' => $item,
                    'TopicArn' => AWS_TOPIC_ARN
                    //'PhoneNumber' => '+5519996941420'
                ]);
            }

        } catch (\Exception $e) {
            $this->logger->log('error', $e->getMessage() . '. File: ' . $e->getFile() . '. Line: ' . $e->getLine());
        }
    }

    /**
     * When instance the class, this method will be use to run program
     */
    final public function run() {
        $this->verify();
        $this->call();
    }

    /**
     * Abstract method to set the program logic
     *
     * @return mixed
     */
    abstract protected function main();

    /**
     * Verify somethings before run the program
     */
    final private function verify() {
        $this->loader->register();

        $this->logger->log('info', 'Up: ' . $this->name);

        /*print "Envio teste FuturoFone para {$this->name}\n";
        $this->send(['message' => 'Testando o envio FuturoFone - ' . $this->name, 'phone' => '5519996941420']);

        print "Envio teste AWS para {$this->name}\n";
        $this->sendContingency('Testando o envio FuturoFone - ' . $this->name);*/
    }

    /**
     * How the program will behave when it started
     */
    public function call() {
        while (true) {
            try {
                $this->main();

                sleep($this->time);
            } catch (\Exception $e) {
                $this->logger->log('error', $e->getMessage());
            }
        }
    }

    public function __destruct() {
        //$this->logger = NULL;
    }
}
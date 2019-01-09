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

    protected $name;

    protected $loader;

    protected $logger;

    protected $time = 60;

    public function __construct(ClassLoader $loader) {
        $this->loader = $loader;

        $this->logger = new Logger(new MongoController(MONGO_HOST, MONGO_PORT));
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setTime(int $time) {
        $this->time = $time;
    }

    public function getLogger() {
        return $this->logger;
    }

    protected function send(array $json) {
        try {
            $sender = new SendSocket($this->loader, $this, $json);

            $sender->start();

        } catch (\Exception $e) {
            $this->logger->log('error', $e->getMessage());
        }
    }

    public function sendContingency($message) {
        try {
            $this->logger->log('info', 'Sending using AWS');

            $client = new SnsClient([
                'region' => 'us-east-1',
                'version' => '2010-03-31',
                'credentials' => [
                    'key' => 'AKIAIGMEFVZH4BHD4YPA',
                    'secret' => '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'
                ]
            ]);
            $client->publish([
                'Message' => $message,
                //'TopicArn' => 'arn:aws:sns:us-east-1:398709985239:monitoring-system'
                'PhoneNumber' => '+5519996941420'
            ]);
        } catch (\Exception $e) {
            $this->logger->log('error', $e->getMessage() . '. File: ' . $e->getFile() . '. Line: ' . $e->getLine());
        }
    }

    final public function run() {
        $this->verify();
        $this->call();
    }

    abstract protected function main();

    final private function verify() {
        $this->loader->register();

        /*print "Envio teste FuturoFone para {$this->name}\n";
        $this->send(['message' => 'Testando o envio FuturoFone - ' . $this->name, 'phone' => '5519996941420']);

        print "Envio teste AWS para {$this->name}\n";
        $this->sendContingency('Testando o envio FuturoFone - ' . $this->name);*/
    }

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
        $this->logger = NULL;
    }
}
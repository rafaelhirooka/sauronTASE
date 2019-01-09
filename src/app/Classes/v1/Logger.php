<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 27/12/2018
 * Time: 16:04
 */

namespace App\Classes\v1;


use App\Classes\v1\DB\MongoController;

class Logger {
    /**
     * Mongo instance
     *
     * @var MongoController
     */
    private $db;

    /**
     * Date format that insert in Mongo
     * @var string
     */
    private $dateFormat;

    /**
     * Logger constructor.
     *
     * @param MongoController $db
     * @param string $dateFormat
     * @throws \Exception
     */
    public function __construct(MongoController $db, string $dateFormat = 'Y-m-d H:i:s') {
        $this->db = $db;

        $this->db->setDB('monitoring_system');

        $this->dateFormat = $dateFormat;
    }

    /**
     * Get current timestamp
     * @return string
     */
    private function getTimestamp() {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new \DateTime(date('Y-m-d H:i:s.'.$micro, $originalTime));
        return $date->format($this->dateFormat);
    }

    /**
     * Format message log to insert in Mongo
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return array|string
     */
    private function formatMessage(string $level, string $message, array $context = array()) {
        if (!empty($context)) {
            $message = [
                'datetime' => $this->getTimestamp(),
                'level' => strtoupper($level),
                'message' => $message,
                'context' => json_encode($context)
            ];
        } else {
            $message = [
                'datetime' => $this->getTimestamp(),
                'level' => strtoupper($level),
                'message' => $message
            ];
        }


        return $message;
    }

    /**
     * Insert message in Mongo
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log(string $level, string $message, array $context = array()) {
        try {
            $m = $this->formatMessage($level, $message, $context);

            switch ($level) {
                case 'info':

                    $this->db->setCollection('logs_infos');

                    break;

                case 'error':

                    $this->db->setCollection('logs_errors');

                    break;

                case 'access':

                    $this->db->setCollection('logs_access');

                    break;
            }

            $this->db->insertBatch([$m]);
        } catch (\Exception $e) {

        }
    }

    public function __destruct() {
        $this->db = NULL;
    }
}
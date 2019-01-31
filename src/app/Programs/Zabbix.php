<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 14/12/2018
 * Time: 11:00
 */

namespace App\Programs;


use App\Classes\v1\AbstractProgram;
use App\Classes\v1\DB\DBController;

class Zabbix extends AbstractProgram {

    /**
     * Alert to send
     *
     * @var array
     */
    private $alerts;

    /**
     * Frequency
     *
     * @var int
     */
    protected $time = 5;

    /**
     * Program logic
     *
     * @return mixed|void
     */
    protected function main() {
        try {
            // Connect to DB
            $db = new DBController('192.168.1.8', 'root', 's3gr3d0', 'zabbix', 'mysql');

            // Get alerts from zabbix db
            $query = "SELECT * FROM alerts 
            INNER JOIN users ON users.userid = alerts.userid
            WHERE status = 1 AND mediatypeid = 6 AND sent = 0 
            ORDER BY clock DESC";

            $r = $db->custom($query);

            $alerts = [];
            if (!empty($r)) {

                // Set alerts
                $i = 0;
                foreach ($r as $k => $item) {
                    // Set formatted subjects
                    $alerts[$i]['message'] = $this->buildSubject($item["subject"]);
                    $alerts[$i]['phone'] = preg_replace('/\D/', '', $item["sendto"]);
                    $alerts[$i]['id'] = $item["alertid"];

                    $i++;
                }
            }

            if (!empty($alerts)) {
                $param = array('sent' => 1);

                foreach ($alerts as $alert) {
                    $condition = array(
                        array(
                            'c' => 'alertid',
                            'o' => '=',
                            'v' => $alert['id']
                        )
                    );

                    // Update status to "sent"
                    $db->update('alerts', $param, $condition);
                }

                $this->send($alerts); // send the alerts
            }

            $db->CloseConnection();

        } catch (\Exception $e) {
            $this->logger->log('error', $e->getMessage() . '. File: ' . $e->getFile() . '. Line: ' . $e->getLine());
        }



    }

    /**
     * Set alerts
     *
     * @param array $info
     * @throws \Exception
     */
    public function setAlerts(array $info) {
        try {

            if (!empty($info)) {

                $i = 0;
                foreach ($info as $k => $item) {
                    // Set formatted subjects
                    $this->alerts[$i]['message'] = $this->buildSubject($item["subject"]);
                    $this->alerts[$i]['phone'] = preg_replace('/\D/', '', $item["sendto"]);
                    $this->alerts[$i]['id'] = $item["alertid"];

                    $i++;
                }
            } else {
                $this->alerts = array();
            }


        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get alerts
     *
     * @return array
     */
    public function getAlerts() {
        return $this->alerts;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAndUpdate() {
        try {
            // Update sent status
            if (!empty($this->alerts)) {
                $param = array('sent' => 1);

                foreach ($this->alerts as $k => $alert) {
                    $this->updateSentStatus($alert['id'], $param);
                }
            }

            return $this->alerts;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Build message to send
     *
     * @param $subject
     * @return string
     * @throws \Exception
     */
    private function buildSubject($subject) {
        try {
            $res = array();

            // All to lower case
            $subject = strtolower($subject);

            // Remove DOWN string
            $subject = str_replace("down", "", $subject);

            // Check if is a problem or a solution
            if (strpos($subject, "ok") === false) {
                // It's a problem
                $res["type"] = 'problem';

                // Remove problem string
                $subject = str_replace("problem:", "", $subject);

                // Remove spaces
                $subject = trim($subject);

                if ($subject == 'aalerta teste sms') {
                    $res["type"] = 'test';
                    $res["subject"] = "Boa tarde. Este é um SMS de teste.";


                } else {
                    $res["subject"] = "Olá. Temos um problema: ". ucfirst($subject);
                }



            } else {
                // It's a solution
                $res["type"] = 'ok';

                $subject = str_replace("ok: ", "", $subject);

                // Remove spaces
                $subject = trim($subject);

                $res["subject"] = "Olá. O problema ". ucfirst($subject) . " foi normalizado.";
            }

            return $res["subject"];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
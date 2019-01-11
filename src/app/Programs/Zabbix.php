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

    private $db;

    private $alerts;

    protected $time = 300;

    protected function main() {
        try {
            $this->setName('zabbix');


            $this->connectDB();


            $alerts = $this->getAlertsToSend();

            //var_dump($alerts);

        } catch (\Exception $e) {

        }

    }

    private function connectDB() {
        try {
            $this->db = new DBController('192.168.1.8', 'root', 's3gr3d0', 'zabbix', 'mysql');
        } catch (\Exception $e) {

        }
    }

    public function getAlertsToSend() {
        try {
            // Build the query
            $query = "SELECT * FROM alerts 
            INNER JOIN users ON users.userid = alerts.userid
            WHERE status = 1 AND mediatypeid = 6 AND sent = 0 
            ORDER BY clock DESC";

            $r = $this->db->custom($query);

            $this->set($r);


        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function set(array $info) {
        try {

            if (!empty($info)) {

                $i = 0;
                foreach ($info as $k => $item) {
                    // Set formatted subjects
                    $this->alerts[$i]['message'] = $this->buildSubject($item["subject"], $item["name"]);
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

    public function getAlerts() {
        return $this->alerts;
    }

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

    private function buildSubject($subject, $user_name) {
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
                    $res["subject"] = "Boa tarde " .$user_name . ". Este é um SMS de teste.";


                } else {
                    $res["subject"] = "Olá " . $user_name . ". Temos um problema: ". ucfirst($subject);
                }



            } else {
                // It's a solution
                $res["type"] = 'ok';

                $subject = str_replace("ok: ", "", $subject);

                // Remove spaces
                $subject = trim($subject);

                $res["subject"] = "Olá " . $user_name . ". O problema ". ucfirst($subject) . " foi normalizado.";
            }

            return $res["subject"];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


}
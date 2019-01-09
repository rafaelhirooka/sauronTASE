<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 14/12/2018
 * Time: 11:00
 */

namespace App\Programs;


use App\Classes\v1\AbstractProgram;

class Zabbix extends AbstractProgram {

    protected $time = 300;

    protected function main() {
        $this->send(['message' => 'Mensagem daora 123', 'phone' => '5519996941420']);
    }


}
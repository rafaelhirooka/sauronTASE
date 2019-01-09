<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 08/01/2019
 * Time: 09:35
 */

namespace App\Interfaces;


interface Program {

    public function run();

    public function setTime(int $time);

    public function setName(string $name);

}
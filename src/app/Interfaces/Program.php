<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 08/01/2019
 * Time: 09:35
 */

namespace App\Interfaces;


interface Program {

    /**
     * Run program
     *
     * @return void
     */
    public function run();

    /**
     * Set frequency that the program will be executed
     * @param int $time
     * @return void
     */
    public function setTime(int $time);

    /**
     * Set program name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name);

}
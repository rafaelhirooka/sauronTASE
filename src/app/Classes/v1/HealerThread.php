<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 08/01/2019
 * Time: 13:46
 */

namespace App\Classes\v1;


class HealerThread extends \Thread {

    private $healer;

    public function __construct(Healer $healer) {
        $this->healer = $healer;
    }

    public function run() {
        $this->healer->run();
    }
}
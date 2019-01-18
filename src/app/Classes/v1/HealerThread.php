<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 08/01/2019
 * Time: 13:46
 */

namespace App\Classes\v1;


class HealerThread extends \Thread {

    /**
     * Healer class to start
     *
     * @var Healer
     */
    private $healer;

    /**
     * HealerThread constructor.
     *
     * @param Healer $healer
     */
    public function __construct(Healer $healer) {
        $this->healer = $healer;
    }

    /**
     * Start the healer
     */
    public function run() {
        $this->healer->setTime(30);
        $this->healer->setName('Healer');
        $this->healer->run();
    }
}
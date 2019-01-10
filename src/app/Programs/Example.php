<?php
namespace App\Programs;

use App\Classes\v1\AbstractProgram;

class Example extends AbstractProgram {

    /**
     * Time in seconds to know how long the function will sleep to be called again
     * @var int time
     */
    protected $time = 60;

    /**
     * Write the logic of program
     */
    protected function main() {
        // Set program name
        // DO NOT REMOVE THIS
        $this->setName('Example');
        
        // write your code here
        // please, use try catch man
    }
}
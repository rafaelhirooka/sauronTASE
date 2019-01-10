#!/usr/bin/env php
<?php

$dir = dirname(__DIR__) . '/src/app/Programs/';

if (file_exists($dir)) {


    $files =  scandir($dir);

    if (!in_array($argv[1] . '.php', $files)) {
        $class = <<<EOL
<?php
namespace App\\Programs;

use App\\Classes\\v1\\AbstractProgram;

class $argv[1] extends AbstractProgram {

    /**
     * Time in seconds to know how long the function will sleep to be called again
     * @var int time
     */
    protected \$time = 60;

    /**
     * Write the logic of program
     */
    protected function main() {
        // Set program name
        // DO NOT REMOVE THIS
        \$this->setName('{$argv[1]}');
        
        // write your code here
        // please, use try catch man
    }
}
EOL;

        $fopen = fopen(dirname(__DIR__) . '/src/app/Programs/' . $argv[1] . '.php', 'w');
        fwrite($fopen, $class);
        fclose($fopen);

        exit('Created with success in app/Programs. Just edit it now.');
    } else {
        exit('Program already exists');
    }


}


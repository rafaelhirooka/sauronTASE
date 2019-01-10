#!/usr/bin/env php
<?php

$fileName = dirname(__DIR__) . '/src/config/programs.json';
$programs = json_decode(file_get_contents($fileName));

if (!in_array($argv[1], $programs->programsToRun)) {
    $programs->programsToRun[] = $argv[1];

    $fopen = fopen($fileName, 'w');
    fwrite($fopen, json_encode($programs));
    fclose($fopen);

    exit('Program started');
} else {
    exit('Program already started');
}

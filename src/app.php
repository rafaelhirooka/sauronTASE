<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 27/12/2018
 * Time: 15:26
 */

require_once __DIR__ . "/config/bootstrap.php";


try {
    // Get programs names
    $programs = json_decode(file_get_contents(__DIR__ . "/config/programs.json"));

    if ($programs != NULL && !empty($programs)) {

        $programs = (array)$programs; // convert json to array
        $files = $programs['programsToRun'];

        $all_threads = [];
        foreach ($files as $file) {
            $file = str_replace('.php', '', $file);
            $class_name = '\App\Programs\\' . $file;

            if (class_exists($class_name)) {
                $task = new $class_name($autoload); // instance program class

                $task->setName($file);
                $task->start(); // start thread

                $healer = new \App\Classes\v1\HealerThread(new \App\Classes\v1\Healer($class_name, $task, $autoload)); // instance program healer
                $healer->start(); // start program healer

                // save thread in array to join
                $all_threads[] = $healer;
                $all_threads[] = $task;
            }
        }

        foreach ($all_threads as $thread) {
            $thread->join(); // wait all threads
        }

    } else {
        exit('-> No programs');
    }


    exit(0);

} catch (\Exception $e) {
    exit('-> ' . $e->getMessage());
}
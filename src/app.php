<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 27/12/2018
 * Time: 15:26
 */

require_once __DIR__ . "/config/bootstrap.php";


try {
    $programs = json_decode(file_get_contents(__DIR__ . "/config/programs.json"));

    if ($programs != NULL && !empty($programs)) {

        $programs = (array)$programs;
        $files = $programs['programsToRun'];

        $all_threads = [];
        foreach ($files as $file) {
            $file = str_replace('.php', '', $file);
            $class_name = '\App\Programs\\' . $file;

            if (class_exists($class_name)) {
                $task = new $class_name($autoload);

                $task->setName($file);
                $task->start();

                $healer = new \App\Classes\v1\HealerThread(new \App\Classes\v1\Healer($class_name, $task, $autoload));
                $healer->start();

                $all_threads[] = $healer;
                $all_threads[] = $task;
            }
        }

        foreach ($all_threads as $thread) {
            $thread->join();
        }

    } else {
        exit('-> No programs');
    }


    exit(0);

} catch (\Exception $e) {
    exit('-> ' . $e->getMessage());
}
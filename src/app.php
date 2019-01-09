<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 27/12/2018
 * Time: 15:26
 */

require_once "config/bootstrap.php";


try {

    if (file_exists($dir)) {
        $files = array_diff(scandir($dir), array('..', '.'));

        $files = array_values($files);

        $all_threads = [];
        foreach ($files as $file) {
            echo $file . "\n";
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
        echo 'Nao tem';
    }




} catch (\Exception $e) {
    echo $e->getMessage();
}
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function Dump($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

spl_autoload_register(function ($class_name) {
    $path[] = _PATH_CORE_ . 'pclass/other/';
    $path[] = _PATH_CORE_ . 'pclass/sdy/util/';
    $path[] = _PATH_CORE_ . 'pclass/sdy/ver01/';
    $path[] = _PATH_CORE_ . 'pclass/sdy/ver01/soap/';
    foreach ($path as &$value) {
        $path_class = $value . $class_name . '.class.php';
        $path_class_php = $value . $class_name . '.php';
        if (file_exists($path_class)) {
            require_once $path_class;
        } elseif (file_exists ($path_class_php)) {
            require_once $path_class_php;
        }
    }
});

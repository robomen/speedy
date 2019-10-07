<?php

define('_PATH_CORE_', __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR);

$fnc_basic = _PATH_CORE_ . 'pfun/basic.php';
if (is_file($fnc_basic)) {
    require_once $fnc_basic;
} else {
    die('Error not file: ' . $fnc_basic);
}
$config = _PATH_CORE_ . 'config.php';
if (is_file($config)) {
    require_once $config;
} else {
    die('Error not file: ' . $config);
}

$SExtractData = new SExtractData();
$SExtractData->Working();

<?php
define("ROOT", rtrim(realpath(__DIR__.'/../'), '/'));

function __autoload($class)
{
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        include_once $file;
    } else {
        return false;
    }
}
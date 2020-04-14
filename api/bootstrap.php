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

$parts = explode("?", $_SERVER['REQUEST_URI']);

$paths = explode('/', trim(array_shift($parts), '/'));
array_shift($paths);

$method = array_pop($paths)?:'index';
$controller = 'controllers\\'.(implode('\\', $paths)?:'main');
<?php
include_once 'bootstrap.php';

$uri = $_SERVER['REQUEST_URI'];
$page = false;
$request = explode("?", $uri);
$request = explode("/", trim($request[0], "/"));

if (stristr($uri, '/page/') !== false) {
    $key = array_search('page', $request);
    if (is_numeric($request[$key + 1])) {
        $uri = str_replace('page/'.$request[$key + 1].'/', '', $uri);
        $page = $request[$key + 1];
    }
}

if (count($request)>=2) {
    $controller = 'controllers\\'.$request[0];
    $method = $request[1];
} else {
    $controller = 'controllers\\main';
    $method = 'index';
}

if (class_exists($controller)) {
    $controller = new $controller();
} else {
    notFound($uri);
}

if (method_exists($controller, $method)) {
    $controller->$method();
} else {
    notFound($uri);
}

function notFound ($uri)
{
    header('HTTP/1.1 404 Not Found');
    $controller = 'controllers\\notFound';
    $controller = new $controller();
    $controller->index();
    exit();
}

<?php
include_once 'bootstrap.php';

if (class_exists($controller)) {
    $controller = new $controller();
    if (method_exists($controller, $method)) {
        $controller->request = (object)$_REQUEST;
        $controller->files = (object)$_FILES;
        try {
            header("Content-Type: application/json;charset=utf-8");
            echo json_encode($controller->{$method}());
        } catch (\Exception $e) {
            print_r($e);
        }
    } else {
        header("HTTP/1.0 404 Not Found");
    }
} else {
    header("HTTP/1.0 404 Not Found");
}

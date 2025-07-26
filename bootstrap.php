<?php
include 'kernel/Assets.php';
include 'kernel/Dd.php';
include 'kernel/RoutesFn.php';
include 'kernel/GuardFn.php';
use Routes\Web;
use Routes\Api;
use Kernel\Server;
use Kernel\Storage;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();
$whoops->pushHandler(function($exception, $inspector, $run) {
    Storage::log('error.log', sprintf(
        "[%s] %s\nFile: %s\nLine: %s\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(), 
        $exception->getTraceAsString()
    ));
});


setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

$webRoutes = new Web();
$apiRoutes = new Api();

$route = getRoute();

if ($route) {
    $controller = 'App\\Controller\\' . $route->controller;

    if (class_exists($controller)) {
        call_user_func_array([new $controller, $route->controllerMethod], $route->args);
    } else {
        throw new Exception('Class ' . $route->controller . ' not found');
    }
} else {
    header("HTTP/1.0 404 Not Found");
    include_once Server::getRealBasePath() . '/resources/views/errors/404.php';
}


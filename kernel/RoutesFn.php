<?php

use Kernel\Server;

function getRoute() {
    $routeFiles = glob(__DIR__ . '/../routes/*.php');
    $route = null;

    foreach ($routeFiles as $routeFile) {
        $className = 'Routes\\' . pathinfo($routeFile, PATHINFO_FILENAME);

        if (class_exists($className)) {
            $routes = new $className();
            $route = $routes->getRoute(Server::getRequestMethod(), Server::getRequestURI());
            if ($route) {
                break;
            }
        }
    }

    return $route;
}

function isRoute($path)
{
    $requestUrl = Server::getRequestURI();

    return $path === $requestUrl;
}

function activeRoute($path)
{
    $requestUrl = Server::getRequestURI();
    if ($requestUrl == $path) {
        echo 'active';
    }
}

function routeContains($path)
{
    $requestUrl = Server::getRequestURI();
    if (str_contains($requestUrl, $path)) {
        echo 'active';
    }
}

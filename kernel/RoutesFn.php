<?php

use Kernel\Server;

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

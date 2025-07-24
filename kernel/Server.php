<?php
namespace Kernel;
class Server
{
    public static function getRequestMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getRequestURI(){
        $requestURI = (isset($_SERVER['PATH_INFO']))?$_SERVER['PATH_INFO']:$_SERVER['REQUEST_URI'];
        if(\str_contains($requestURI, '?'))
            return explode('?',$requestURI)[0];
        return $requestURI;
    }

    public static function getBasePath(){
        if(Env::get('APP_ENV') == 'local')
            return 'http://'.$_SERVER['HTTP_HOST'];

        return 'https://'.$_SERVER['HTTP_HOST'];
    }

    public static function getRealBasePath()
    {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

    public static function httpReferer()
    {
        if(isset($_SERVER['HTTP_REFERER']))
            return $_SERVER['HTTP_REFERER'];
        return self::getBasePath();
    }

    public static function jsonRequest()
    {
        if(isset($_SERVER['CONTENT_TYPE']) == 'application/json')
            return true;
        return false;
    }

    public static function requestInfo() {
        if( isset($_SERVER['argv']) ) {
            return false;
        }


        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }


        return (object)[
            'remote_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'http_referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'http_host' => $_SERVER['HTTP_HOST'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'path_info' => $_SERVER['PATH_INFO'] ?? '',
            'remote_ip' => $ip
        ];
    }

    public static function verifySecurityConnection() {
        if(Env::get('APP_ENV') == 'production') {
            if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
                $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $location);
                exit;
            }
        } 
    }
}
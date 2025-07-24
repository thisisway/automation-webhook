<?php
namespace Kernel;
class Redirect
{

    public static function to($path, $args = [])
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            header('Location: ' . $path);
            exit();
        }

        $base_path = Server::getBasePath();
        $url = $base_path.$path;

        if(count($args) > 0){
            $url .= '?'.http_build_query($args);
        }
        
        header('Location:'.$url);
        die();
    }

    public static function flash($path, $args)
    {
        $base_path = Server::getBasePath();
        $url = $base_path.$path;

        Session::setFlash($args);
        Redirect::to($url);
    }

    public static function flashBack( $args )
    {
        Session::setFlash($args);
        Redirect::to(Server::httpReferer());
    }

    public static function back()
    {
        Redirect::to(Server::httpReferer());
    }
}
<?php

namespace Kernel;

use Kernel\Env;

class Session
{
    public static function start()
    {
        if (Env::get('APP_SESSION') == 'redis') {

            $tcp = "tcp://" . Env::get('REDIS_HOST') . ":" . Env::get('REDIS_PORT');
            if ($redis_password = Env::get('REDIS_PASSWORD')) {
                $tcp .= "/?auth=" . $redis_password;
            }

            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', $tcp);
        }
        session_start();
    }

    public static function all()
    {
        return $_SESSION;
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function setMultiple($array)
    {
        foreach ($array as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public static function delete($key)
    {
        unset($_SESSION[$key]);
    }

    public static function get($key)
    {
        if (isset($_SESSION[$key]))
            return $_SESSION[$key];
        return false;
    }

    public static function has($key)
    {
        if (isset($_SESSION[$key]))
            return true;
        return false;
    }

    public static function setError($key, $value)
    {
        if (!isset($_SESSION['error']))
            $_SESSION['error'] = [];
        $_SESSION['error'][$key] = $value;
    }

    public static function hasError($key)
    {
        if (isset($_SESSION['error'][$key]))
            return true;
        return false;
    }

    public static function getError($key)
    {
        $error = $_SESSION['error'][$key];
        unset($_SESSION['error'][$key]);
        return $error;
    }

    public static function setFlash($value)
    {
        $_SESSION['flash'] = $value;
    }

    public static function hasFlash($key)
    {
        if (isset($_SESSION['flash']) && isset($_SESSION['flash'][$key]))
            return true;
        return false;
    }

    public static function getFlash($key)
    {
        if(isset($_SESSION['flash'][$key])) {   
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return '';
    }


    public static function hasErrors()
    {
        if (isset($_SESSION['error']) && count($_SESSION['error']) > 0) {
            return true;
        }
        return false;
    }

    public static function getErrors()
    {
        return $_SESSION['error'];
    }

    public static function clearErrors()
    {
        unset($_SESSION['error']);
    }

    public static function close()
    {
        session_write_close();
    }
}

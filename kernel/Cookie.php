<?php

namespace Kernel;

class Cookie
{
    public static function set($name, $value, $options = [])
    {
        if (is_numeric($options)) {
            $options = ['expires' => $options];
        }
        
        $defaults = [
            'expires' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        $options = array_merge($defaults, $options);
        
        setcookie(
            $name,
            $value,
            $options
        );
        
        $_COOKIE[$name] = $value; // Importante: atualizar também o array $_COOKIE
    }

    public static function delete($key)
    {
        unset($_COOKIE[$key]);
    }

    public static function get($key)
    {
        return $_COOKIE[$key] ?? null;
    }

    public static function has($key)
    {
        if (isset($_COOKIE[$key]))
            return true;
        return false;
    }
}
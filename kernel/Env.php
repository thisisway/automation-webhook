<?php
namespace Kernel;
class Env
{
    public static function get($key)
    {
        $value = getenv($key);
        $env_path = (dirname(__FILE__,2).'/.env');
        if (!$value && file_exists($env_path)){
            $env = parse_ini_file($env_path);
            $value = $env[$key] ?? null;
        }

        return $value;
    }
}
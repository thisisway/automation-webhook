<?php
namespace App\Services\Domains;
class Domains
{
    public static function makeDomain($name)
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name); // Remove múltiplos hífens
        $name = trim($name, '-'); // Remove hífens no início e no fim

        

        return $name;
    }
}
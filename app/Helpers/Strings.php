<?php
namespace App\Helpers;

class Strings 
{
    public static function clearPhone($value)
    {
        return str_replace(['(', ')', ' ', '-'], '', $value);
    }


    public static function clearCpfCnpj($value)
    {
        return str_replace(['.', '/', '-'], '', $value);
    }

    public static function clearCep($value)
    {
        return str_replace(['.', '-'], '', $value);
    }

    public static function onlyNumbers($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }

    public static function clean($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    public static function onlyLetters($string) 
    {
        return preg_replace("/[^a-zA-Z]/", "", $string);
    }

    public static function onlyLettersAndNumbers($string)
    {
        return preg_replace("/[^a-zA-Z0-9]/", "", $string);
    }

    public static function removeAccents($string)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $string);
    }

    public static function slug($string)
    {
        $string = self::removeAccents($string);
        $string = strtolower($string);
        $string = preg_replace("/[^a-z0-9-]/", "-", $string);
        $string = preg_replace("/-+/", "-", $string);
        return trim($string, "-");
    }
}
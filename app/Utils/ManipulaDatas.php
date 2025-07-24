<?php
namespace App\Utils;

class ManipulaDatas
{
    public static function formatarISO($data)
    {
        if(strpos($data, '/') !== false){
            return implode('-', array_reverse(explode('/', $data)));
        }
        return $data;
    }

    public static function formatarBR($data)
    {
        if(!$data)
            return '';
        return date('d/m/Y', strtotime($data));
    }

    public static function hora($datahora)
    {
        if(!$datahora)
            return '';
        return date('H:i:s', strtotime($datahora));
    }

    public static function isBR($data)
    {
        return preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data);
    }

    public static function isISO($data)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $data);
    }

    public static function formatarDataHoraISO($datahora)
    {
        [$data, $hora] = explode(' ', $datahora);
        $data = self::formatarISO($data);
        return $data.' '.$hora;
    }

    public static function formatarDataHoraBR($datahora)
    {
        [$data, $hora] = explode(' ', $datahora);
        $data = self::formatarBR($data);
        return $data.' '.$hora;
    }
}
<?php
namespace App\Helpers;

class Money
{
    public static function amountToCents($amount) 
    {
        return str_replace(['.',','],'',$amount);
    }

    public static function centsToBRL($cents) 
    {
        $amount = $cents / 100;
        return number_format($amount, 2, ',', '.');
    }

    public static function centsToFloat($cents) 
    {
        $cents = $cents ?? 0; //if null received 0

        return round($cents / 100, 2);
    }
}
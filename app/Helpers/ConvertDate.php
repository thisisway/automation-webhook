<?php

namespace App\Helpers;

class ConvertDate
{
    public static function dateBRtoISO( $date )
    {
        return implode('-', array_reverse(explode('/', $date)));
    }

    public static function dateISOToBR( $date ) 
    {
        return implode('/', array_reverse(explode('-', $date)));
    }
}

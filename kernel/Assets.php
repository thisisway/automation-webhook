<?php

use Kernel\Env;
use Kernel\Server;

function assets($file_path)
{
    return Server::getBasePath().$file_path;
}

function toMask($mask,$str)
{
    if($str == null)
        return null;

    $str = str_replace(" ","",$str);

    for($i=0;$i<strlen($str);$i++)
    {
        $pos = strpos($mask, '#');
        if ($pos !== false) {
            $mask = substr_replace($mask, $str[$i], $pos, 1);
        }
    }

    return $mask;
}

function imageToBase64($file_path)
{
    $file_path = Server::getRealBasePath().$file_path;
    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
    $file_data = file_get_contents($file_path);
    $file_data_base64 = base64_encode($file_data);
    
    return "data:image/{$file_type};base64,{$file_data_base64}";
}

function import($file_path, $params = null) {

    if($params)
        extract($params);
        
    $file = str_replace('//','/', dirname(__FILE__, 2).'/resources/views/'.$file_path.'.php');
    include $file;
}
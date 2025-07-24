<?php
function dd($args)
{
    $args = func_get_args();
    
    echo '<pre>';
    foreach ($args as $value){
        var_dump($value);
        echo '<br><br>';
    }
    echo '</pre>';
    die();
}
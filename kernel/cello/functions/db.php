<?php

use Database\Connection;
use Database\SwitchHub;
use Kernel\Env;

$root = dirname(__FILE__, 4);
$model = $root . '/app/Model/';

$colorGreen = "\033[32m"; // Verde
$colorRed = "\033[31m";   // Vermelho
$colorReset = "\033[0m";  // Reseta a cor

if ($command == 'db:update') {

}

if ($command == 'db:seed') {
    $reset = in_array('--reset', $argv);

    
}
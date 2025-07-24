<?php

//commands is file to register all files on folder functions
include dirname(__FILE__, 2) . '/Dd.php';

if(!isset($argv[1]) || $argv[1] == '') {
    echo 'command not found';
    die();
}

$command = $argv[1];
$args    = $argv;
array_shift($args);
array_shift($args);

$functions = array_filter(scandir(dirname(__FILE__).'/functions'), function ($file) {
    return $file !== '.' && $file !== '..' && str_contains($file, '.php');
});

foreach($functions as $execFunction) {
    include dirname(__FILE__).'/functions/'.$execFunction;
}
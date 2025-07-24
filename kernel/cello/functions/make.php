<?php

$root = dirname(__FILE__, 4);
$controller = $root . '/app/Controller/';
$model = $root . '/app/Models/';

if ($command == 'make:controller') {
    $fileName = $args[0];
    makeFile($fileName, 'controller', $controller);
}


if ($command == 'make:model') {
    $fileName = $args[0];
    makeFile($fileName, 'model', $model);
}

function makeFile($filename, $template, $path)
{
    $templates = dirname(__FILE__, 4) . '/kernel/cello/templates/';

    if (file_exists($path . $filename . '.php')) {
        echo 'Error: file already exists';
        exit;
    }

    $data = file_get_contents($templates . $template);
    $data = str_replace('classname', $filename, $data);

    file_put_contents($path . $filename . '.php', $data);
    echo 'File created successfully ' . $filename . '.php'.PHP_EOL;
}
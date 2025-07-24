<?php

use Database\Connection;
use Database\SwitchHub;
use Kernel\Env;

$root = dirname(__FILE__, 4);
$model = $root . '/app/Models/';
include "migrations/create_tables.php";
include "migrations/table_mapping.php";

$colorGreen = "\033[32m"; // Verde
$colorRed = "\033[31m";   // Vermelho
$colorReset = "\033[0m";  // Reseta a cor

if ($command == 'migrate') {
    $reset = in_array('--reset', $argv);
    try {
        $models = array_filter(scandir($model), function ($file) {
            return $file !== '.' && $file !== '..';
        });

        $db_connection = Env::get('DB_CONNECTION');
        $db_name = ($db_connection == 'mysql') ? Env::get('MYSQL_DATABASE') : Env::get('PG_DATABASE');
        $connections = SwitchHub::connections();
        $data = $connections[$db_connection];


        echo "Starting migration to database: " . PHP_EOL;
        echo "Connecting to {$colorGreen}{$db_connection}{$colorReset} in {$colorGreen}{$db_name}{$colorReset}" . PHP_EOL;
        $pdo = (new Connection($data))->pdo ?? false;

        if (!$pdo)
            throw new Exception("{$colorRed}Could not connect to {$db_connection}{$colorReset}");

        $success = 0;
        $failure = 0;
        foreach ($models as $modelFile) {
            $modelName = explode('.', $modelFile)[0];
            $className = '\\App\\Models\\' . $modelName;
            if (class_exists($className)) {
                $modelClass = new $className();
                $modelInfo = $modelClass->getTableSchema();
                echo $modelName . " ";
                if(migrate($pdo, $db_connection, $modelInfo, $modelName, $reset))
                    $success++;
                else
                    $failure++;
            } else {
                echo "{$colorRed} Error: model not found $modelName {$colorReset}" . PHP_EOL;
            }
        }

        echo "Finished! {$colorGreen}{$success}{$colorReset} created sucessfuly! {$colorRed}{$failure}{$colorReset} be failed." . PHP_EOL;
        die();
    } catch (Exception $e) {
        echo " {$colorRed}falha!{$colorReset}\n";
        echo "{$colorRed}Fail: {$e->getMessage()}.{$colorReset}\n";
    }
}

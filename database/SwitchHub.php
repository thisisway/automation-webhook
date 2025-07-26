<?php

namespace Database;

use Kernel\Env;

class SwitchHub
{
    public static function Connections()
    {
        return [
            'mysql' => [
                'connection' => 'mysql',
                'host' => Env::get('MYSQL_HOST') ?? 'localhost',
                'port' => Env::get('MYSQL_PORT') ?? '3306',
                'database' => Env::get('MYSQL_DATABASE') ?? 'mysql',
                'username' => Env::get('MYSQL_USERNAME') ?? 'root',
                'password' => Env::get('MYSQL_PASSWORD') ?? 'root',
                'options' => [
                    \PDO::ATTR_ERRMODE,
                    \PDO::ERRMODE_EXCEPTION
                ]
            ],
            'postgres' => [
                'connection' => 'pgsql',
                'host' => Env::get('PG_HOST') ?? 'localhost',
                'port' => Env::get('PG_PORT') ?? '5432',
                'database' => Env::get('PG_DATABASE') ?? 'postgres',
                'username' => Env::get('PG_USERNAME') ?? 'postgres',
                'password' => Env::get('PG_PASSWORD') ?? 'postgres',
                'options' => [
                    \PDO::ATTR_ERRMODE,
                    \PDO::ERRMODE_EXCEPTION
                ]
            ],
            'sqlite' => [
                'connection' => 'sqlite',
                'database' => Env::get('SQLITE_DATABASE') ?? 'storage/database/database.sqlite',
                'options' => [
                    \PDO::ATTR_ERRMODE,
                    \PDO::ERRMODE_EXCEPTION
                ]
            ],
            'redis' => [
                'scheme' => 'tcp',
                'host' => Env::get('REDIS_HOST') ?? 'localhost',
                'port' => Env::get('REDIS_PORT') ?? '6379',
                'username' => Env::get('REDIS_USERNAME') ?? NULL,
                'password' => Env::get('REDIS_PASSWORD') ?? NULL
            ],
            'mongodb' => [
                'host' => Env::get('MONGODB_HOST') ?? 'localhost',
                'port' => Env::get('MONGODB_PORT') ?? 27017,
                'database' => Env::get('MONGODB_DATABASE') ?? 'admin',
                'username' => Env::get('MONGODB_USERNAME') ?? NULL,
                'password' => Env::get('MONGODB_PASSWORD') ?? NULL,
            ]
        ];
    }
}

<?php

namespace App\Console;

use Database\Connection;

class Commands
{
    public function createTables()
    {
        $connection = new Connection(
            \Database\SwitchHub::Connections()['sqlite']
        );
        $pdo = $connection->pdo;
        $sql = "
            CREATE TABLE IF NOT EXISTS configurations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT NOT NULL UNIQUE,
                value TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ";
        $pdo->exec($sql);
    }
}

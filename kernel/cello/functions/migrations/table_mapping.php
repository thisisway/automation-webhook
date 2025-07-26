<?php

// Função para obter todas as tabelas do banco de dados MySQL
function getExistingTablesMySQL($pdo)
{
    $query = $pdo->query("SHOW TABLES");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);
    return $tables;
}

// Função para obter todas as tabelas do banco de dados PostgreSQL
function getExistingTablesPostgres($pdo)
{
    $query = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);
    return $tables;
}

// Função para criar tabelas que não existem no banco de dados MySQL
function migrateMySQL($pdo, $schema, $reset)
{
    global $colorGreen; // Verde
    global $colorRed;   // Vermelho
    global $colorReset;  // Reseta a cor
    $existingTables = getExistingTablesMySQL($pdo);

    try{
        $sql = createTableMySQL($schema, $reset);
        $pdo->exec($sql);
        echo "{$colorGreen} Success! {$colorReset}" . PHP_EOL;
        return true;
    }catch(PDOException $e){
        echo "{$colorRed} Failed: ".$e->getMessage() . PHP_EOL; 
        return false;  // Tabela já existe no PostgreSQL.
    }
}

// Função para criar tabelas que não existem no banco de dados PostgreSQL
function migratePostgres($pdo, $schema, $reset)
{
    global $colorGreen; // Verde
    global $colorRed;   // Vermelho
    global $colorReset;  // Reseta a cor
    try{
        $sql = createTablePostgres($schema, $reset);
        $pdo->exec($sql);
        echo "{$colorGreen} Success! {$colorReset}" . PHP_EOL;
        return true;
    }catch(PDOException $e){
        echo "{$colorRed} Failed: ".$e->getMessage() . PHP_EOL; 
        return false;  // Tabela já existe no PostgreSQL.
    }
}

function migrateSQLite($pdo, $schema, $reset)
{
    global $colorGreen; // Verde
    global $colorRed;   // Vermelho
    global $colorReset;  // Reseta a cor
    try{
        $sql = createTableSQLite($schema, $reset);
        $pdo->exec($sql);
        echo "{$colorGreen} Success! {$colorReset}" . PHP_EOL;
        return true;
    }catch(PDOException $e){
        echo "{$colorRed} Failed: ".$e->getMessage() . PHP_EOL; 
        return false;  // Tabela já existe no SQLite.
    }
}


function migrate($pdo, $dbType, $schema, $modelName, $reset)
{
    global $colorGreen;
    global $colorRed;
    global $colorReset;

    $pointLimit = 40 - strlen($modelName);

    for($x = 0; $x < $pointLimit; $x++){
        echo ".";
        usleep(10000);
    }

    if(count($schema['fillable']) > count($schema['columns'])){
        echo "{$colorRed} Failed: Collumns does't match with fillables. {$colorReset}" . PHP_EOL;
        return false;
    }

    if ($dbType === 'mysql') {
        return migrateMySQL($pdo, $schema, $reset);
    } elseif ($dbType === 'postgres') {
        return migratePostgres($pdo, $schema, $reset);
    } elseif ($dbType === 'sqlite') {
        return migrateSQLite($pdo, $schema, $reset);
    }else {
        echo "{$colorRed} Tipo de banco de dados não suportado.{$colorReset}" . PHP_EOL;
        return false;
    }
}

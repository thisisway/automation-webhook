<?php
// Função para criar uma coluna MySQL
function createColumnMySQL($column, $attributes)
{
    $typeMap = [
        'integer' => 'INT',
        'small_integer' => 'SMALLINT',
        'big_integer' => 'BIGINT',
        'decimal' => 'DECIMAL',
        'float' => 'FLOAT',
        'double' => 'DOUBLE',
        'boolean' => 'TINYINT(1)',
        'string' => 'VARCHAR',
        'text' => 'TEXT',
        'date' => 'DATE',
        'datetime' => 'DATETIME',
        'timestamp' => 'TIMESTAMP',
        'time' => 'TIME',
        'json' => 'JSON'
    ];

    $sql = "`" . $column . "` " . $typeMap[strtolower($attributes['type'])];

    // Adiciona o comprimento se aplicável (ex: VARCHAR, DECIMAL)
    if (isset($attributes['length']) && in_array(strtolower($attributes['type']), ['string', 'decimal'])) {
        $sql .= "(" . $attributes['length'] . ")";
    }

    // Adiciona NOT NULL se não for nulo
    if (isset($attributes['nullable']) && !$attributes['nullable']) {
        $sql .= " NOT NULL";
    }

    // Adiciona valor padrão se estiver presente
    if (isset($attributes['default'])) {
        $sql .= " DEFAULT '" . $attributes['default'] . "'";
    }

    return $sql;
}

// Função para criar a tabela MySQL
function createTableMySQL($schema, $reset = false)
{
    $sql = "";
    if ($reset) {
        $sql .= "DROP TABLE IF EXISTS `" . $schema['table'] . "`;\n";
    }
    $sql .= "CREATE TABLE IF NOT EXISTS `" . $schema['table'] . "` (\n    `" . $schema['table_id'] . "` INT AUTO_INCREMENT PRIMARY KEY";

    foreach ($schema['columns'] as $column => $attributes) {
        $sql .= ",\n    " . createColumnMySQL($column, $attributes);
    }

    if ($schema['timestamp']) {
        $sql .= ",\n    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }

    $sql .= "\n);";

    return $sql;
}

// Função para criar uma coluna PostgreSQL
function createColumnPostgres($column, $attributes)
{
    $typeMap = [
        'integer' => 'INTEGER',
        'small_integer' => 'SMALLINT',
        'big_integer' => 'BIGINT',
        'decimal' => 'NUMERIC',
        'float' => 'REAL',
        'double' => 'DOUBLE PRECISION',
        'boolean' => 'BOOLEAN',
        'string' => 'VARCHAR',
        'text' => 'TEXT',
        'date' => 'DATE',
        'datetime' => 'TIMESTAMP',
        'time' => 'TIME',
        'json' => 'JSONB'
    ];

    $sql = "\"" . $column . "\" " . $typeMap[strtolower($attributes['type'])];

    // Adiciona o comprimento se aplicável (ex: VARCHAR, NUMERIC)
    if (isset($attributes['length']) && in_array(strtolower($attributes['type']), ['string', 'decimal'])) {
        $sql .= "(" . $attributes['length'] . ")";
    }

    // Adiciona NOT NULL se não for nulo
    if (isset($attributes['nullable']) && !$attributes['nullable']) {
        $sql .= " NOT NULL";
    }

    // Adiciona valor padrão se estiver presente
    if (isset($attributes['default'])) {
        $sql .= " DEFAULT '" . $attributes['default'] . "'";
    }

    return $sql;
}

// Função para criar a tabela PostgreSQL
function createTablePostgres($schema, $reset = false)
{
    $sql = "";
    if ($reset) {
        $sql .= "DROP TABLE IF EXISTS \"" . $schema['table'] . "\" CASCADE;\n";
    }
    $sql .= "CREATE TABLE IF NOT EXISTS \"" . $schema['table'] . "\" (\n    \"" . $schema['table_id'] . "\" SERIAL PRIMARY KEY";

    foreach ($schema['columns'] as $column => $attributes) {
        $sql .= ",\n    " . createColumnPostgres($column, $attributes);
    }

    if ($schema['timestamp']) {
        $sql .= ",\n    \"created_at\" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    \"updated_at\" TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    }

    $sql .= "\n);";

    return $sql;
}

function createTableSQLite($schema, $reset=false)
{
    $sql = "CREATE TABLE IF NOT EXISTS `" . $schema['table'] . "` (\n    `" . $schema['table_id'] . "` INTEGER PRIMARY KEY AUTOINCREMENT";

    foreach ($schema['columns'] as $column => $attributes) {
        $sql .= ",\n    " . createColumnSQLite($column, $attributes);
    }

    if ($schema['timestamp']) {
        $sql .= ",\n    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP";
    }

    $sql .= "\n);";

    return $sql;
    
}

function createColumnSQLite($column, $attributes)
{
    $typeMap = [
        'integer' => 'INTEGER',
        'small_integer' => 'SMALLINT',
        'big_integer' => 'BIGINT',
        'decimal' => 'DECIMAL',
        'float' => 'REAL',
        'double' => 'DOUBLE',
        'boolean' => 'BOOLEAN',
        'string' => 'TEXT',
        'text' => 'TEXT',
        'date' => 'DATE',
        'datetime' => 'DATETIME',
        'time' => 'TIME',
        'json' => 'JSON'
    ];

    $sql = "`" . $column . "` " . $typeMap[strtolower($attributes['type'])];

    // Adiciona o comprimento se aplicável (ex: TEXT, DECIMAL)
    if (isset($attributes['length']) && in_array(strtolower($attributes['type']), ['string', 'decimal'])) {
        $sql .= "(" . $attributes['length'] . ")";
    }

    // Adiciona NOT NULL se não for nulo
    if (isset($attributes['nullable']) && !$attributes['nullable']) {
        $sql .= " NOT NULL";
    }

    // Adiciona valor padrão se estiver presente
    if (isset($attributes['default'])) {
        $sql .= " DEFAULT '" . $attributes['default'] . "'";
    }

    return $sql;
}
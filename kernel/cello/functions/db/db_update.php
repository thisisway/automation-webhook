<?php
// Função para atualizar o esquema de uma tabela MySQL com base no modelo
function updateTableMySQL($pdo, $schema)
{
    $tableName = $schema['table'];
    $existingColumns = $pdo->query("SHOW COLUMNS FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumnsMap = [];

    foreach ($existingColumns as $column) {
        $existingColumnsMap[$column['Field']] = $column;
    }

    // Atualizar ou adicionar colunas conforme o modelo
    foreach ($schema['columns'] as $columnName => $attributes) {
        if (!isset($existingColumnsMap[$columnName])) {
            // Adicionar coluna ausente
            $sql = "ALTER TABLE `{$tableName}` ADD " . createColumnMySQL($columnName, $attributes);
            $pdo->exec($sql);
            echo "Coluna `{$columnName}` adicionada na tabela `{$tableName}`." . PHP_EOL;
        } else {
            // Verificar se os atributos da coluna precisam ser atualizados
            $existingColumn = $existingColumnsMap[$columnName];
            $columnDefinition = createColumnMySQL($columnName, $attributes);

            if (stripos($existingColumn['Type'], strtolower($attributes['type'])) === false ||
                ($attributes['nullable'] && $existingColumn['Null'] === 'NO') ||
                (!$attributes['nullable'] && $existingColumn['Null'] === 'YES')) {
                // Atualizar coluna se houver diferença
                $sql = "ALTER TABLE `{$tableName}` MODIFY " . $columnDefinition;
                $pdo->exec($sql);
                echo "Coluna `{$columnName}` modificada na tabela `{$tableName}`." . PHP_EOL;
            }
        }
    }

    // Remover colunas que não estão no modelo
    foreach ($existingColumnsMap as $existingColumnName => $existingColumn) {
        if (!isset($schema['columns'][$existingColumnName]) && $existingColumnName !== $schema['table_id']) {
            $sql = "ALTER TABLE `{$tableName}` DROP COLUMN `{$existingColumnName}`";
            $pdo->exec($sql);
            echo "Coluna `{$existingColumnName}` removida da tabela `{$tableName}`." . PHP_EOL;
        }
    }
}

// Função para atualizar o esquema de uma tabela PostgreSQL com base no modelo
function updateTablePostgres($pdo, $schema)
{
    $tableName = $schema['table'];
    $existingColumns = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = '{$tableName}'")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumnsMap = [];

    foreach ($existingColumns as $column) {
        $existingColumnsMap[$column['column_name']] = $column;
    }

    // Atualizar ou adicionar colunas conforme o modelo
    foreach ($schema['columns'] as $columnName => $attributes) {
        if (!isset($existingColumnsMap[$columnName])) {
            // Adicionar coluna ausente
            $sql = "ALTER TABLE \"{$tableName}\" ADD COLUMN " . createColumnPostgres($columnName, $attributes);
            $pdo->exec($sql);
            echo "Coluna \"{$columnName}\" adicionada na tabela \"{$tableName}\"." . PHP_EOL;
        } else {
            // Verificar se os atributos da coluna precisam ser atualizados
            $existingColumn = $existingColumnsMap[$columnName];
            $columnDefinition = createColumnPostgres($columnName, $attributes);

            if (stripos($existingColumn['data_type'], strtolower($attributes['type'])) === false ||
                ($attributes['nullable'] && $existingColumn['is_nullable'] === 'NO') ||
                (!$attributes['nullable'] && $existingColumn['is_nullable'] === 'YES')) {
                // Atualizar coluna se houver diferença
                $sql = "ALTER TABLE \"{$tableName}\" ALTER COLUMN \"{$columnName}\" TYPE " . createColumnPostgres($columnName, $attributes);
                $pdo->exec($sql);
                echo "Coluna \"{$columnName}\" modificada na tabela \"{$tableName}\"." . PHP_EOL;
            }
        }
    }

    // Remover colunas que não estão no modelo
    foreach ($existingColumnsMap as $existingColumnName => $existingColumn) {
        if (!isset($schema['columns'][$existingColumnName]) && $existingColumnName !== $schema['table_id']) {
            $sql = "ALTER TABLE \"{$tableName}\" DROP COLUMN \"{$existingColumnName}\"";
            $pdo->exec($sql);
            echo "Coluna \"{$existingColumnName}\" removida da tabela \"{$tableName}\"." . PHP_EOL;
        }
    }
}
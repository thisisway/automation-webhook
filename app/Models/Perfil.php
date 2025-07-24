<?php
namespace App\Models;
use Kernel\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $timestamp = false;
    protected $fillable = [
        'nome',
        'descricao',
        'nivel_hierarquico'
    ];

    protected $columns = [
        'nome' => ['type' => 'string', 'length' => 50, 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => true],
        'nivel_hierarquico' => ['type' => 'integer', 'nullable' => false]
    ];
}

/*
    Exemplos de perfis:
    - Supervisor (1)
    - Operador (2)
    - Administrador (3)
    - Secretário(a) (4)
*/
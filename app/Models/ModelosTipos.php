<?php
namespace App\Models;
use Kernel\Model;

class ModelosTipos extends Model
{
    protected $table = 'modelos_tipos';
    protected $fillable = [
        'modelo_tipo',
        'especificacoes',
        'fabricante',
        'categoria'
    ];

    protected $columns = [
        'modelo_tipo' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        'especificacoes' => ['type' => 'text', 'nullable' => true],
        'fabricante' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'categoria' => ['type' => 'string', 'length' => 100, 'nullable' => false]
    ];
}
<?php
namespace App\Models;
use Kernel\Model;

class Equipamentos extends Model
{
    protected $table = 'equipamentos';
    protected $fillable = [
        'nome',
        'descricao',
        'modelo_tipo_id',
        'numero_serie',
        'data_aquisicao',
        'status',
        'valor_aquisicao',
        'fornecedor',
        'observacoes'
    ];

    protected $columns = [
        'nome' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => true],
        'modelo_tipo_id' => ['type' => 'integer', 'nullable' => false],
        'numero_serie' => ['type' => 'string', 'length' => 50, 'nullable' => true],
        'data_aquisicao' => ['type' => 'date', 'nullable' => true],
        'status' => ['type' => 'string', 'length' => 20, 'nullable' => false, 'default' => 'ativo'],
        'valor_aquisicao' => ['type' => 'decimal', 'length' => '10,2', 'nullable' => true],
        'fornecedor' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'observacoes' => ['type' => 'text', 'nullable' => true]
    ];
}
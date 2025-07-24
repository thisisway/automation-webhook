<?php
namespace App\Models;
use Kernel\Model;

class MaterialUtilizado extends Model
{
    protected $table = 'material_utilizado';
    protected $fillable = [
        'ordem_servico_id',
        'descricao',
        'quantidade',
        'unidade_medida',
        'observacoes'
    ];

    protected $columns = [
        'ordem_servico_id' => ['type' => 'integer', 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        'quantidade' => ['type' => 'decimal', 'length' => '10,2', 'nullable' => false],
        'unidade_medida' => ['type' => 'string', 'length' => 20, 'nullable' => false],
        'observacoes' => ['type' => 'text', 'nullable' => true]
    ];
}
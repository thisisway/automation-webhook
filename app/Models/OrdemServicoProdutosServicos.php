<?php

namespace App\Models;

use Kernel\Model;

class OrdemServicoProdutosServicos extends Model
{
    protected $table = 'ordem_servico_produtos_servicos';

    protected $fillable = [
        'ordem_servico_id',
        'descricao', 
        'quantidade',
        'valor_unitario',
        'desconto',
        'valor_total'
    ];

    protected $columns = [
        'ordem_servico_id' => ['type' => 'integer', 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        'quantidade' => ['type' => 'integer', 'nullable' => false],
        'valor_unitario' => ['type' => 'decimal', 'precision' => 10, 'scale' => 2, 'nullable' => false],
        'desconto' => ['type' => 'decimal', 'precision' => 10, 'scale' => 2, 'nullable' => false],
        'valor_total' => ['type' => 'decimal', 'precision' => 10, 'scale' => 2, 'nullable' => false]
    ];
}
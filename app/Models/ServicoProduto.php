<?php

namespace App\Models;

use Kernel\Model;

class ServicoProduto extends Model
{
    protected $table = 'servicos_produtos';
    
    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'tipo'
    ];

    protected $columns = [
        'nome' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => true],
        'preco' => ['type' => 'decimal', 'length' => '10,2', 'nullable' => true],
        'tipo' => ['type' => 'string', 'length' => 50, 'nullable' => false]
    ];
} 
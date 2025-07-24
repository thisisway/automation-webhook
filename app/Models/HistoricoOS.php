<?php
namespace App\Models;
use Kernel\Model;

class HistoricoOS extends Model
{
    protected $table = 'historico_os';
    protected $fillable = [
        'ordem_servico_id',
        'status_anterior',
        'status_novo',
        'usuario',
        'observacao'
    ];

    protected $columns = [
        'ordem_servico_id' => ['type' => 'integer', 'nullable' => false],
        'status_anterior' => ['type' => 'integer', 'nullable' => false],
        'status_novo' => ['type' => 'integer', 'nullable' => false],
        'usuario' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'observacao' => ['type' => 'text', 'nullable' => true]
    ];
}
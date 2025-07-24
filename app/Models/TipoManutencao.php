<?php
namespace App\Models;
use Kernel\Model;

class TipoManutencao extends Model
{
    protected $table = 'tipomanutencao';
    protected $fillable = [
        'descricao'
    ];

    protected $columns = [
        'descricao' => ['type' => 'string', 'length' => 100, 'nullable' => false],
    ];
}
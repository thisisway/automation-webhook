<?php
namespace App\Models;
use Kernel\Model;
class Permissoes extends Model
{
    protected $table     = 'permissoes';
    protected $timestamp = false;
    protected $fillable  = [
        'permissao',
        'descricao',
        'categoria'
    ];

    protected $columns = [
        'permissao' => ['type' => 'string', 'length' => 50, 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        'categoria' => ['type' => 'string', 'length' => 100, 'nullable' => true],
    ];
}
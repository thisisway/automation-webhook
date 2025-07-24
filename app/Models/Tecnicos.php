<?php
namespace App\Models;
use Kernel\Model;
class Tecnicos extends Model
{
    protected $table = 'tecnicos';
    protected $fillable = [
        'usuario_id',
        'nome',
        'funcao',
        'telefone',
        'email',
    ];

    protected $columns = [
        'usuario_id' => ['type' => 'integer', 'nullable' => false],
        'nome' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'funcao' => ['type' => 'string', 'length' => 50, 'nullable' => false],
        'telefone' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'email' => ['type' => 'string', 'length' => 100, 'nullable' => false]
    ];
}
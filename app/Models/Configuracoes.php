<?php
namespace App\Models;
use Kernel\Model;
class Configuracoes extends Model
{
    protected $table     = 'config';
    protected $fillable  = [
        'slug',
        'value'
    ];

    protected $columns = [
        'slug' => ['type' => 'text', 'length' => 255, 'nullable' => false],
        'value' => ['type' => 'text', 'length' => 255, 'nullable' => false]
    ];
}
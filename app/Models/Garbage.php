<?php
namespace App\Models;
use Kernel\Model;

class Garbage extends Model
{
    protected $table = 'garbage';
    protected $fillable = [
        'username',
        'table_name',
        'data',
        'action'
    ];

    protected $columns = [
        'username' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'table_name' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'data' => ['type' => 'json', 'nullable' => false],
        'action' => ['type' => 'string', 'length' => 50, 'nullable' => false],
    ];
}
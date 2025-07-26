<?php
namespace App\Models;

class Configs extends \Kernel\Model
{
    protected $table = 'configurations';
    protected $fillable = [
        'key',
        'value'
    ];
    protected $columns = [
        'key' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        'value' => ['type' => 'text', 'nullable' => false],
    ];
}
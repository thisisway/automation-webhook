<?php
namespace App\Models;
use Kernel\Model;
class FormasPagamento extends Model
{
    protected $timestamp = false;
    protected $table     = 'formas_pagamento';
    protected $fillable  = [
        'descricao'
    ];

    protected $columns = [
        'descricao' => ['type' => 'string', 'length' => 50, 'nullable' => false],
    ];
}
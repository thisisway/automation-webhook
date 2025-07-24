<?php

namespace App\Models;

use Kernel\Model;

class Congregacoes extends Model
{
    protected $table     = 'congregacoes';
    protected $fillable  = [
        'nome',
        'telefone_fixo',
        'telefone_celular',
        'data_abertura',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'ponto_referencia',
        'bairro',
        'cidade',
        'uf',
        'latitude',
        'longitude',
        'observacoes'
    ];

    protected $columns = [
        'nome' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'telefone_fixo' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'telefone_celular' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'data_abertura' => ['type' => 'date', 'nullable' => true],
        'cep' => ['type' => 'string', 'length' => 8, 'nullable' => false],
        'logradouro' => ['type' => 'string', 'length' => 150, 'nullable' => false],
        'numero' => ['type' => 'string', 'length' => 10, 'nullable' => false],
        'complemento' => ['type' => 'string', 'length' => 50, 'nullable' => true],
        'ponto_referencia' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'bairro' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'cidade' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'uf' => ['type' => 'string', 'length' => 2, 'nullable' => false],
        'latitude' => ['type' => 'decimal', 'length' => '10,6', 'nullable' => true],
        'longitude' => ['type' => 'decimal', 'length' => '10,6', 'nullable' => true],
        'observacoes' => ['type' => 'text', 'nullable' => true],
    ];
}
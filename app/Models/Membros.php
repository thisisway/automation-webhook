<?php
namespace App\Models;
use Kernel\Model;

class Membros extends Model
{
    protected $table = 'membros';
    protected $fillable = [
        'nome_completo',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'email',
        'data_nascimento',
        'data_filiacao',
        'data_batismo',
        'telefone',
        'contato_emergencia',
        'nome_contato_emergencia',
        'foto',
        'cargo_id',
        'congregacao_id',
        'data_cargo',
        'status'
    ];

    protected $columns = [
        'nome_completo' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'logradouro' => ['type' => 'string', 'length' => 150, 'nullable' => true],
        'numero' => ['type' => 'string', 'length' => 10, 'nullable' => true],
        'complemento' => ['type' => 'string', 'length' => 50, 'nullable' => true],
        'bairro' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'cidade' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'estado' => ['type' => 'string', 'length' => 2, 'nullable' => true],
        'cep' => ['type' => 'string', 'length' => 8, 'nullable' => true],
        'email' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'data_nascimento' => ['type' => 'date', 'nullable' => true],
        'data_filiacao' => ['type' => 'date', 'nullable' => true],
        'data_batismo' => ['type' => 'date', 'nullable' => true],
        'telefone' => ['type' => 'string', 'length' => 15, 'nullable' => false],
        'contato_emergencia' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'nome_contato_emergencia' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'foto' => ['type' => 'string', 'length' => 255, 'nullable' => true],
        'cargo_id' => ['type' => 'integer', 'nullable' => false],
        'congregacao_id' => ['type' => 'integer', 'nullable' => false],
        'data_cargo' => ['type' => 'date', 'nullable' => true],
        'status' => ['type' => 'boolean', 'default' => true]
    ];
}

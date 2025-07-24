<?php
namespace App\Models;
use Kernel\Model;

class Enderecos extends Model
{
    protected $table = 'enderecos';
    protected $fillable = [
        'titulo',
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
        'responsavel',
        'telefone',
        'telefone2',
        'email',
        'padrao'
    ];


    protected $columns = [
        'titulo' => ['type' => 'string', 'length' => 100, 'nullable' => false],
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
        'responsavel' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'telefone' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'telefone2' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'email' => ['type' => 'string', 'length' => 100, 'nullable' => true],
        'padrao' => ['type' => 'boolean', 'nullable' => false, 'default' => 0],
    ];

    public function mask()
    {
        //masks default PT-BR / BRAZIL
        $telefone   = (strlen($this->telefone) == 11)?"(##) #####-####":"(##) ####-####";
        $telefone2   = (isset($this->telefone2) && strlen($this->telefone2) == 11)?"(##) #####-####":"(##) ####-####";
        
        //mask application
        $this->telefoneMasked  = toMask($telefone, $this->telefone);
        $this->telefone2Masked = ($this->telefone2 != "")?toMask($telefone2, $this->telefone2):""; 

        $this->cep = toMask("#####-###", $this->cep);

        return $this;
    }
}
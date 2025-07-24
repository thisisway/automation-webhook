<?php
namespace App\Models;
use Kernel\Model;
class Empresa extends Model
{
    protected $table = 'empresa';
    protected $timestamp = false;
    protected $fillable = [
        'cnpj',
        'rsocial',
        'nfantasia',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'telefone',
        'telefone2',
        'email',
        'chave_conta'
    ];

    protected $columns = [
        'cnpj' => ['type' => 'string', 'length' => 14, 'nullable' => false],
        'rsocial' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'nfantasia' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'cep' => ['type' => 'string', 'length' => 8, 'nullable' => false],
        'logradouro' => ['type' => 'string', 'length' => 150, 'nullable' => false],
        'numero' => ['type' => 'string', 'length' => 10, 'nullable' => false],
        'complemento' => ['type' => 'string', 'length' => 50, 'nullable' => true],
        'bairro' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'cidade' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'estado' => ['type' => 'string', 'length' => 2, 'nullable' => false],
        'telefone' => ['type' => 'string', 'length' => 15, 'nullable' => false],
        'telefone2' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'email' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'chave_conta' => ['type' => 'string', 'length' => 255, 'nullable' => false],
    ];

    public function mask()
    {
        //masks default PT-BR / BRAZIL
        $telefone   = (strlen($this->telefone) == 11)?"(##) #####-####":"(##) ####-####";
        
        //mask application
        $this->telefoneMasked  = toMask($telefone, $this->telefone); 
        
        $this->cep = toMask("#####-###", $this->cep);
        $this->cnpj = toMask("##.###.###/####-##", $this->cnpj);

        return $this;
    }
}
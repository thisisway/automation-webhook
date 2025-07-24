<?php
namespace App\Rules;
use App\Rules\ValidationRules;
use Kernel\Request;

class MembrosInserirRules {

    public static function validate(Request $request)
    {
        $rules = [
            'nome_completo' => [
                'required' => 'Informe o nome completo do membro',
                'min:5' => 'Informe pelo menos 5 caracteres',
                'max:100' => 'Informe no máximo 100 caracteres'
            ],
            'telefone' => [
                'required' => 'Informe o telefone do membro',
                'min:10' => 'Informe pelo menos 10 dígitos',
                'max:11' => 'Informe no máximo 11 dígitos',
                'masked' => true
            ],
            'email' => [
                'email' => 'Informe um formato de email válido',
                'max:100' => 'Email deve ter no máximo 100 caracteres'
            ],
            'data_nascimento' => [
                'required' => 'Informe a data de nascimento',
                'date' => 'Data de nascimento inválida'
            ],
            'data_batismo' => [
                'date' => 'Data de batismo inválida'
            ],
            'data_cargo' => [
                'date' => 'Data de posse no cargo inválida'
            ],
            'cargo_id' => [
                'required' => 'Selecione o cargo do membro',
                'integer' => 'Cargo inválido'
            ],
            'nome_contato_emergencia' => [
                'required' => 'Informe o nome do contato de emergência',
                'min:5' => 'Informe pelo menos 5 caracteres',
                'max:100' => 'Informe no máximo 100 caracteres'
            ],
            'contato_emergencia' => [
                'required' => 'Informe o telefone de emergência',
                'min:10' => 'Informe pelo menos 10 dígitos',
                'max:11' => 'Informe no máximo 11 dígitos',
                'masked' => true
            ],
            'cep' => [
                'required' => 'Informe o CEP',
                'equals:8' => 'CEP deve conter 8 dígitos',
                'masked' => true
            ],
            'logradouro' => [
                'required' => 'Informe o logradouro',
                'min:5' => 'Informe pelo menos 5 caracteres',
                'max:150' => 'Informe no máximo 150 caracteres'
            ],
            'numero' => [
                'required' => 'Informe o número',
                'min:1' => 'Informe pelo menos 1 dígito',
                'max:10' => 'Informe no máximo 10 dígitos'
            ],
            'complemento' => [
                'max:50' => 'Informe no máximo 50 caracteres'
            ],
            'bairro' => [
                'required' => 'Informe o bairro',
                'min:4' => 'Informe no mínimo 4 caracteres',
                'max:50' => 'Informe no máximo 50 caracteres'
            ],
            'cidade' => [
                'required' => 'Informe a cidade',
                'min:3' => 'Informe no mínimo 3 caracteres', 
                'max:50' => 'Informe no máximo 50 caracteres'
            ],
            'estado' => [
                'required' => 'Informe o estado',
                'equals:2' => 'Estado deve conter 2 caracteres'
            ],
            'foto' => [
                'image' => 'O arquivo deve ser uma imagem',
                'max:2048' => 'A imagem deve ter no máximo 2MB'
            ]
        ];
        ValidationRules::formExec($request, $rules);
    }
} 
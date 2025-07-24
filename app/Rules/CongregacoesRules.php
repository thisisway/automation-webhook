<?php

namespace App\Rules;

use App\Rules\ValidationRules;
use Kernel\Request;

class CongregacoesRules
{
    public static function validate(Request $request)
    {
        $rules = [
            'congregation' => [
                'required' => 'Informe o nome da congregação',
                'min:3'    => 'Informe pelo menos 3 caracteres',
                'max:100'  => 'Informe no máximo 100 caracteres'
            ],
            'fixed_number' => [
                'min:10'  => 'Informe pelo menos 10 dígitos',
                'max:11'  => 'Informe no máximo 11 dígitos',
                'masked'  => true
            ],
            'mobile_number' => [
                'min:10'  => 'Informe pelo menos 10 dígitos',
                'max:11'  => 'Informe no máximo 11 dígitos',
                'masked'  => true
            ],
            'join_date' => [
                'required' => 'Informe a data de abertura'
            ],
            'cep' => [
                'required'  => 'Informe um CEP',
                'equals:8'  => 'Informe um CEP com 8 dígitos',
                'masked'    => true
            ],
            'logradouro' => [
                'required'  => 'Informe um logradouro',
                'min:4'     => 'Informe no mínimo 4 caracteres',
                'max:100'   => 'Informe no máximo 100 caracteres'
            ],
            'numero' => [
                'required'  => 'Informe um número',
                'min:1'     => 'Informe pelo menos 1 dígito',
                'max:10'    => 'Informe no máximo 10 dígitos'
            ],
            'bairro' => [
                'required'  => 'Informe um bairro',
                'min:4'     => 'Informe no mínimo 4 caracteres',
                'max:50'    => 'Informe no máximo 50 caracteres'
            ],
            'cidade' => [
                'required'  => 'Informe uma cidade',
                'min:3'     => 'Informe no mínimo 3 caracteres',
                'max:50'    => 'Informe no máximo 50 caracteres'
            ],
            'uf' => [
                'required'  => 'Informe o estado',
                'min:2'     => 'Informe no mínimo 2 caracteres',
                'max:2'     => 'Informe no máximo 2 caracteres'
            ],
            'ponto_referencia' => [
                'max:150' => 'Informe no máximo 150 caracteres'
            ]
        ];

        ValidationRules::formExec($request, $rules);
    }
}
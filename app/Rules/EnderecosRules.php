<?php

namespace App\Rules;

use Kernel\Request;
use App\Rules\ValidationRules;

class EnderecosRules
{
    public static function validate(Request $request)
    {
        $rules = [
            'cep' => [
                'required'  => 'Informe um CEP',
                'equals:8'  => 'Infome um CEP com 8 dígitos',
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
                'max:50'   => 'Informe no máximo 50 caracteres'
            ],
            'cidade' => [
                'required'  => 'Informe um bairro',
                'min:4'     => 'Informe no mínimo 4 caracteres',
                'max:50'    => 'Informe no máximo 50 caracteres'
            ],
            'uf' => [
                'required'  => 'Informe o estado',
                'min:2'     => 'Informe no mínimo 2 caracteres',
                'max:2'     => 'Informe no máximo 2 caracteres'
            ]
        ];

        ValidationRules::formExec($request, $rules);
    }
}

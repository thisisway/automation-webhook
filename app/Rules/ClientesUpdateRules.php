<?php

namespace App\Rules;

use App\Rules\ValidationRules;
use Kernel\Request;

class ClientesUpdateRules
{

    public static function validate(Request $request)
    {

        $rules = [
            'tipo' => [
                'required' => 'Informe o tipo de cliente'
            ],
            'telefone_principal' => [
                'required' => 'Informe o telefone do cliente',
                'min:10'   => 'Informe pelo menos 10 dígitos',
                'max:11'   => 'Informe no máximo 11 dígitos',
                'masked'   => true
            ],
            'telefone_secundario' => [
                'min:10'   => 'Informe pelo menos 10 dígitos',
                'max:11'   => 'Informe no máximo 11 dígitos',
                'masked'   => true
            ],
        ];

        if($request->tipo == 'cnpj')
        {
            $rules = array_merge($rules,[
                'cpf_cnpj' => [
                    'required'  => 'Informe o CNPJ da empresa',
                    'equals:14' => 'Informe o CNPJ com 14 dígitos',
                    'masked'    => true
                ], 
                'nome_rsocial'  => [
                    'required'  => 'Informe a razão social da empresa',
                    'min:5'    => 'Informe pelo menos 5 caracteres',
                    'max:100'   => 'Informe no máximo 100 caracteres'
                ], 
                'nfantasia'     => [
                    'required'  => 'Informe o nome fantasia da empresa',
                    'min:5'    => 'Informe pelo menos 5 caracteres',
                    'max:100'   => 'Informe no máximo 100 caracteres'
                ], 
                'responsavel'    => [
                    'required'  => 'Informe o nome do responsável pela empresa',
                    'min:5'     => 'Informe pelo menos 5 caracteres',
                    'max:100'   => 'Informe no máximo 100 caracteres'
                ],
                'email' => [
                    'required'  => 'Informe um email',
                    'email'     => 'informe um formato de email válido'
                ],
            ]);
        }

        if($request->tipo == 'cpf')
        {
            $rules = array_merge($rules,[
                'cpf_cnpj' => [
                    'equals:11' => 'Informe o CPF com 11 dígitos',
                    'masked'    => true
                ], 
                'nome_rsocial'  => [
                    'required'  => 'Informe o nome completo do cliente',
                    'min:5'     => 'Informe pelo menos 5 caracteres',
                    'max:100'   => 'Informe no máximo 100 caracteres'
                ],
                'email' => [
                    'email'     => 'informe um formato de email válido'
                ],
                'responsavel'    => [
                    'min:5'     => 'Informe pelo menos 5 caracteres',
                    'max:100'   => 'Informe no máximo 100 caracteres'
                ],
            ]);
        }

        ValidationRules::formExec($request, $rules);
    }
}

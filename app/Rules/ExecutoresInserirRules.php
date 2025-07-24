<?php

namespace App\Rules;

use App\Rules\ValidationRules;
use Kernel\Request;

class ExecutoresInserirRules
{

    public static function validate(Request $request)
    {

        $rules = [
            'nome'  => [
                'required' => "Campo obrigatório",
                'min:5' => "Campo deve ter 5 caracteres ou mais",
                'max:100' => "Campo deve ter no máximo 100 caracteres",
            ],
            'email'  => [
                'required' => "Campo obrigatório",
                'email' => "Campo deve ser um email válido",
                'min:15' => "Campo deve ter 15 caracteres ou mais",
                'max:100' => "Campo deve ter no máximo 255 caracteres",
            ],
            'password' => [
                'min:8' => "Senha deve ter no mínimo 8 caracteres",
                "max:20" => "Senha deve ter no máximo 20 caracteres",
                "confirm" => "Senha de confirmação não confere"
            ],
            'congregacoes' => [
                'required' => "Campo obrigatório"
            ]
        ];

        ValidationRules::formExec($request, $rules);
    }
}

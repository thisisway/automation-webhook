<?php

namespace App\Rules;

use App\Rules\ValidationRules;
use Kernel\Request;

class UsuariosUpdateRules
{

    public static function validate(Request $request)
    {

        $rules = [
            'nome'  => [
                'required' => "Campo obrigatório",
                'min:5' => "Campo deve ter 5 caracteres ou mais",
                'max:100' => "Campo deve ter no máximo 100 caracteres",
            ],
            'username'  => [
                'required' => "Campo obrigatório",
                'min:5' => "Campo deve ter 5 caracteres ou mais",
                'max:100' => "Campo deve ter no máximo 50 caracteres",
            ],
            'email'  => [
                'required' => "Campo obrigatório",
                'min:15' => "Campo deve ter 15 caracteres ou mais",
                'max:100' => "Campo deve ter no máximo 255 caracteres",
            ],
            'pass' => [
                'min:8' => "Senha deve ter no mínimo 8 caracteres",
                "max:20" => "Senha deve ter no máximo 20 caracteres",
                "confirm" => "Senha de  confirmação não conhecide"
            ]
        ];

        ValidationRules::formExec($request, $rules);
    }
}

<?php

namespace App\Rules;

use App\Rules\ValidationRules;
use Kernel\Request;

class VinculacaoRules
{
    public static function validate(Request $request)
    {
        $rules = [
            'usuario_id' => [
                'required' => "Campo obrigatório",
                'exists:usuarios,id' => "Usuário não encontrado"
            ],
            'perfil_id' => [
                'required' => "Campo obrigatório"
            ],
            'congregacao_id' => [
                'required' => "Campo obrigatório"
            ],
        ];

        ValidationRules::formExec($request, $rules);
    }
}
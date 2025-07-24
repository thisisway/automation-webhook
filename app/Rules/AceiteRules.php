<?php

namespace App\Rules;
use App\Rules\ValidationRules;
use Kernel\Request;

class AceiteRules{

    public static function validate(Request $request)
    {
        $rules = [
            'assinatura' => [
                'required'  => 'Informe o nome do represetante responsável',
                'min:4'     => 'Informe pelo menos 4 caracteres',
                'max:100'   => 'Informe no máximo 100 caracteres'
            ]
        ];

        return ValidationRules::formExec($request, $rules);
    }

}

<?php

namespace App\Rules;

use Kernel\Request;
use App\Rules\ValidationRules;

class ContasAReceberRules
{
    public static function validate(Request $request)
    {
        $rules = [
            'os' => [
                'required'  => 'Informe o número da OS',
                'exists|ordemservico|id' => 'OS não encontrada'
            ],
            'valor' => [
                'required'  => 'Informe valor válido'
            ],
            'vencimento' => [
                'required'  => 'Informe uma data de vencimento'
            ]
        ];

        ValidationRules::formExec($request, $rules);
    }
}

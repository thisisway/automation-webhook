<?php

namespace App\Rules;
use App\Rules\ValidationRules;
use Kernel\Request;

class OrdemServicosExecRules{

    public static function validate(Request $request)
    {
        $rules = [
            'servicos_realizados'      => [
                'required'     => 'Marque pelo menos 1 serviço',
                'multiple'     => true
            ],
            'pragas_combatidas'        => [
                'required'     => 'Marque pelo menos 1 praga combatida',
                'multiple'     => true
            ],
            'produtos_usados'      => [
                'required'     => 'Marque pelo menos 1 produto utilizado',
                'multiple'     => true
            ]
        ];

        return ValidationRules::formExec($request, $rules);
    }

}

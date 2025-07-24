<?php
namespace App\Rules;
use App\Rules\ValidationRules;
use Kernel\Request;

class OrdemServicoUpdateRules{

    public static function validate(Request $request)
    {
        $rules = [
            'equipamento' => [
                'min:3' => 'O equipamento deve ter pelo menos 3(três) caracteres'
            ],
            'modelo_tipo' => [
                'min:3' => 'O modelo/tipo deve ter pelo menos 3(três) caracteres'
            ],
            'observacoes' => [
                'min:3' => 'As observações devem ter pelo menos 3(três) caracteres'
            ],
            'problemas' => [
                'min:3' => 'As observações devem ter pelo menos 3(três) caracteres',
                'required' => 'Informe o problema'
            ],
            'tipomanutencao' => [
                'required' => 'Selecione o tipo de manutenção'
            ]
        ];
        ValidationRules::formExec($request, $rules);
    }

} 
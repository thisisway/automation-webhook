<?php

namespace App\Repositories;

use App\Models\CongregacaoMembros;

class PerfilRepository
{
    public function getPerfils($user_id)
    {
        return (new CongregacaoMembros)
            ->select('perfil.nome', 'congregacao_membros.congregacao_id')
            ->join('perfil', 'perfil.id', 'congregacao_membros.perfil_id')
            ->where('congregacao_membros.usuario_id', $user_id)
            ->get()
            ->pluck('nome','congregacao_id');
    }
}

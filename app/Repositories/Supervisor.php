<?php

namespace App\Repositories;

use App\Models\CongregacaoMembros;
use App\Models\Usuarios;
use Kernel\Cookie;
use Kernel\Session;

class Supervisor
{
    public function getSupervisores()
    {
        $supervisores = [];
        if(Session::get('perfil_id') == (new CongregacaoMembros)->ADMINISTRADOR) {
            $getUsuariosAdministrador = (new Usuarios)
            ->select('usuarios.id', 'usuarios.nome')
            ->where('perfil_id', (new CongregacaoMembros)->ADMINISTRADOR)
            ->where('usuarios.id', '!=', 1)
            ->get()
            ->toArray();
            $supervisores = array_merge($supervisores, $getUsuariosAdministrador);
        }

        $getUsuariosSupervisor = (new CongregacaoMembros)
        ->select('usuarios.id', 'usuarios.nome')
        ->join('usuarios', 'usuarios.id', 'congregacao_membros.usuario_id')
        ->where('congregacao_membros.perfil_id', (new CongregacaoMembros)->SUPERVISOR);
        
        // caso supervisor ou operador estejam abrindo OS, aparece somente os supervisores ligados a congregação
        if(in_array(Session::get('perfil'), ['Supervisor', 'Operador']))
            $getUsuariosSupervisor->where('congregacao_membros.congregacao_id', Cookie::get('congregacao_id'));

        $getUsuariosSupervisor = $getUsuariosSupervisor->get()
        ->toArray();

        $supervisores = array_merge($supervisores, $getUsuariosSupervisor);
        return (object)$supervisores;
    }

    public function getSupervisor($congregacao_id)
    {
        return (new CongregacaoMembros)
        ->select(
            'usuarios.id',
            'congregacao_membros.usuario_id',
            'perfil.nome as perfil',
            'usuarios.nome',
        )
        ->join('usuarios', 'usuarios.id', 'congregacao_membros.usuario_id')
        ->join('perfil', 'perfil.id', 'congregacao_membros.perfil_id')
        ->where('congregacao_membros.congregacao_id', $congregacao_id)
        ->where('congregacao_membros.perfil_id', (new CongregacaoMembros)->SUPERVISOR)
        ->first();
    }
}
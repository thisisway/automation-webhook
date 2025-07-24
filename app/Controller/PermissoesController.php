<?php

namespace App\Controller;

use App\Models\Permissoes;
use App\Models\PermissoesUsuario;
use App\Models\Usuarios;
use Kernel\Request;
use Kernel\Redirect;

class PermissoesController extends Controller
{
    public function index()
    {
        return $this->view('viewfolder/index');
    }

    public function create()
    {
        return $this->view('viewfolder/create');
    }

    public function store(Request $request)
    {

        return Redirect::flashBack([
            'success' => true,
            'message' => 'success'
        ]);
    }

    public function edit($id)
    {

        $usuario = (new Usuarios)->find($id);
        $permissoes = (new Permissoes)->all();
        $permissoesUsuario = (new PermissoesUsuario)->where('usuario_id', $id)->get()->pluck('permissao');

        $permissoesCategorizadas = [];
        foreach ($permissoes as $permissao) {
            if (!isset($permissoesCategorizadas[$permissao->categoria]))
                $permissoesCategorizadas[$permissao->categoria] = [];

            $permissoesCategorizadas[$permissao->categoria][] = $permissao;
        }

        return $this->view(
            'permissions/update',
            compact('usuario', 'permissoesCategorizadas', 'permissoesUsuario')
        );
    }

    public function update(Request $request, $id)
    {
        //guard to verify permissions

        $permissoesChecadas = $request->get('permissoes');

        $permissoesUsuario = (new PermissoesUsuario)->where('usuario_id', $id)->get()->pluck('permissao');

        $listaPermissoes = (new Permissoes)->all()->pluck('permissao');

        foreach ($permissoesChecadas as $permissao) {
            if (!in_array($permissao, $permissoesUsuario)) {
                (new PermissoesUsuario)->create([
                    'permissao' => $permissao,
                    'usuario_id' => $id
                ]);
            }
        }

        foreach ($listaPermissoes as $permissao) {
            if (!in_array($permissao, $permissoesChecadas)) {
                (new PermissoesUsuario)->where('permissao', $permissao)->where('usuario_id', $id)->delete();
            }
        }

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Permissões atualizadas com sucesso!'
        ]);
    }
}

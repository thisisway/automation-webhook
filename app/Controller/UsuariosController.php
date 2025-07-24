<?php

namespace App\Controller;

use App\Models\CongregacaoMembros;
use App\Models\Congregacoes;
use App\Models\OrdemServico;
use App\Models\Perfil;
use App\Models\Usuarios;
use App\Rules\UsuariosInserirRules;
use App\Rules\UsuariosUpdateRules;
use App\Rules\VinculacaoRules;
use Kernel\Collection;
use Kernel\Cookie;
use Kernel\Request;
use Kernel\Redirect;
use Kernel\Session;
use stdClass;

class UsuariosController extends Controller
{

    public function registerPermissions()
    {
        $this->registerFunctionPermission('index', 'usuarios.listar');
        $this->registerFunctionPermission('create', 'usuarios.inserir');
        $this->registerFunctionPermission('store', 'usuarios.inserir');
        $this->registerFunctionPermission('edit', 'usuarios.editar');
        $this->registerFunctionPermission('update', 'usuarios.editar');
        $this->registerFunctionPermission('delete', 'usuarios.excluir');
        $this->registerFunctionPermission('vincular', 'usuarios.vincular');
        $this->registerFunctionPermission('storeVinculo', 'usuarios.vincular');
        $this->registerFunctionPermission('removerVinculo', 'usuarios.remover-vinculo');

        $this->registerFunctionPerfils('index', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('create', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('store', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('edit', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('update', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('delete', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('vincular', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('storeVinculo', ['Administrador', 'Supervisor']);
        $this->registerFunctionPerfils('removerVinculo', ['Administrador', 'Supervisor']);
    }

    public function index()
    {
        $usuarios = (new Usuarios)
            ->select('usuarios.*', 'perfil.nome as perfil', 'congregacoes.nome as congregacao')
            ->join('perfil', 'usuarios.perfil_id', 'perfil.id')
            ->leftJoin('congregacao_membros', 'usuarios.id', 'congregacao_membros.usuario_id')
            ->leftJoin('congregacoes', 'congregacao_membros.congregacao_id', 'congregacoes.id');

        // Se for administrador
        if (Session::get('perfil') === 'Administrador') {
            // Se tiver congregação selecionada, filtra por ela
            if (Cookie::get('congregacao_id')) {
                $usuarios = $usuarios
                    ->where('congregacao_membros.congregacao_id', Cookie::get('congregacao_id'));
            }
        } else {
            // Se não for admin, obrigatoriamente filtra pelas congregações que tem acesso
            // Filtra apenas pela congregação atual selecionada
            $usuarios = $usuarios
                ->where('congregacao_membros.congregacao_id', Cookie::get('congregacao_id'));
        }

        $usuarios = $usuarios->get();
        // Processa os dados para mostrar "Várias congregações" quando necessário
        $usuariosFiltrados = [];
        foreach ($usuarios as $usuario) {

            if(isset($usuariosFiltrados[$usuario->id])){
                $usuariosFiltrados[$usuario->id]->congregacao = "Várias congregações";
            }else{
                $usuariosFiltrados[$usuario->id] = (object)[
                    'id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'username' => $usuario->username,
                    'email' => $usuario->email,
                    'perfil' => $usuario->perfil,
                    'perfil_id' => $usuario->perfil_id,
                    'congregacao' => $usuario->congregacao,
                ];
            }
        }

        $usuarios = $usuariosFiltrados;

        return $this->enableModule('datatable')->view(
            'users/index',
            compact('usuarios')
        );
    }

    public function create()
    {
        $perfis = (new Perfil)->orderBy('nivel_hierarquico', 'asc');
        if (Session::get('perfil') == 'Supervisor') {
            $perfis = $perfis->where('nivel_hierarquico', '>', 2);
        }
        $perfis = $perfis->get();

        return $this->view('users/create', compact('perfis'));
    }

    public function store(Request $request)
    {
        UsuariosInserirRules::validate($request);

        $usuario = (new Usuarios)->create([
            'nome' => $request->get('nome'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => password_hash($request->get('pass'), PASSWORD_DEFAULT),
            'perfil_id' => $request->get('perfil')
        ]);

        if ($request->get('perfil') != "Administrador") {
            if (Cookie::get('congregacao_id')) {
                (new CongregacaoMembros)->create([
                    'usuario_id' => $usuario->id,
                    'perfil_id' => $request->get('perfil'),
                    'congregacao_id' => Cookie::get('congregacao_id')
                ]);
            }
        }

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Usuário criado com sucesso!'
        ]);
    }

    public function edit($id)
    {
        $usuario = (new Usuarios)->find($id);
        return $this->view('users/update', compact('usuario'));
    }

    public function update(Request $request, $id)
    {

        UsuariosUpdateRules::validate($request);

        $usuario = (new Usuarios)->find($id);

        $usuario->nome = $request->get('nome');
        $usuario->username = $request->get('username');
        $usuario->email = $request->get('email');

        if ($request->get('pass')) {
            $usuario->password = password_hash($request->get('pass'), PASSWORD_DEFAULT);
        }

        $usuario->save();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso! '
        ]);
    }

    public function delete($id)
    {
        if ($id == 1) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Usuário admin não pode ser removido!'
            ]);
        }

        (new Usuarios)->find($id)->delete();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Usuário removido com sucesso!'
        ]);
    }

    public function vincular($usuario_id)
    {
        // Busca o usuário específico
        $usuario = (new Usuarios)->find($usuario_id);
        if (!$usuario) {
            return Redirect::flashBack([
                'success' => false,
                'message' => 'Usuário não encontrado!'
            ]);
        }

        // Busca todas as congregações
        $congregacoes = (new Congregacoes)->all();

        // Busca as congregações já vinculadas ao usuário
        $congregacoesVinculadas = (new CongregacaoMembros)
            ->where('usuario_id', $usuario_id)
            ->join('congregacoes', 'congregacao_membros.congregacao_id', 'congregacoes.id')
            ->join('perfil', 'congregacao_membros.perfil_id', 'perfil.id')
            ->select('congregacoes.*', 'perfil.nome as perfil', 'congregacao_membros.*')
            ->get();

        $perfis = (new Perfil);
        if (Session::get('perfil') != 'Administrador') {
            $perfis = $perfis->where('nivel_hierarquico', '>', 2);
        }
        $perfis = $perfis->get();

        return $this->enableModule('datatable')->view('users/vinculate', compact('usuario', 'congregacoes', 'congregacoesVinculadas', 'perfis'));
    }

    public function storeVinculo(Request $request)
    {
        // Validação dos dados recebidos
        VinculacaoRules::validate($request);

        if ($request->get('congregacao_id') == 'all') {
            $congregacoes = (new Congregacoes)->all();

            foreach ($congregacoes as $cg) {
                $existeVinculo = (new CongregacaoMembros)
                    ->where('usuario_id', $request->get('usuario_id'))
                    ->where('perfil_id', $request->get('perfil_id'))
                    ->where('congregacao_id', $cg->id)
                    ->count();

                if ($existeVinculo == 0) {
                    (new CongregacaoMembros)->create([
                        'usuario_id' => $request->get('usuario_id'),
                        'congregacao_id' => $cg->id,
                        'perfil_id' => $request->get('perfil_id')
                    ]);
                }
            }

            return Redirect::flashBack([
                'success' => true,
                'message' => 'Usuário vinculado à todas as congregações com sucesso!'
            ]);
        }

        $vinculo = (new CongregacaoMembros)
            ->where('usuario_id', $request->get('usuario_id'))
            ->where('congregacao_id', $request->get('congregacao_id'))
            ->first();

        if ($vinculo) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Usuário já vinculado à congregação!'
            ]);
        }

        // Vincula o usuário à congregação
        (new CongregacaoMembros)->create([
            'usuario_id' => $request->get('usuario_id'),
            'congregacao_id' => $request->get('congregacao_id'),
            'perfil_id' => $request->get('perfil_id') // ou outro valor conforme a lógica
        ]);

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Usuário vinculado à congregação com sucesso!'
        ]);
    }

    public function atualizarVinculo(Request $request)
    {
        // Validação dos dados recebidos
        VinculacaoRules::validate($request);

        $vinculo = (new CongregacaoMembros)
            ->where('usuario_id', $request->get('usuario_id'))
            ->where('congregacao_id', $request->get('congregacao_id'))
            ->first();

        if ($vinculo) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Usuário já vinculado à congregação!'
            ]);
        }

        // Atualiza a vinculação
        (new CongregacaoMembros)
            ->where('usuario_id', $request->get('usuario_id'))
            ->where('congregacao_id', $request->get('congregacao_id'))
            ->update([
                'congregacao_id' => $request->congregacao_id,
                'perfil_id' => $request->perfil_id
            ]);

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Vinculação atualizada com sucesso!'
        ]);
    }

    public function removerVinculo(Request $request)
    {
        // Remove a vinculação
        (new CongregacaoMembros)
            ->where('usuario_id', $request->get('usuario_id'))
            ->where('congregacao_id', $request->get('congregacao_id'))
            ->delete();

        Session::setFlash(['success' => true, 'message' => 'Vinculação removida com sucesso!']);

        return $this->json(['success' => true, 'message' => 'Vinculação removida com sucesso!']);
    }
}

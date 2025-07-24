<?php

namespace App\Controller;

use App\Models\CongregacaoMembros;
use Kernel\Request;
use Kernel\Redirect;
use App\Models\Congregacoes;
use App\Models\Usuarios;
use App\Rules\ExecutoresInserirRules;
use App\Rules\SupervisoresInserirRules;
use Kernel\Cookie;

class CriarContaExecutoresController extends Controller
{
    public function create()
    {
        $congregacoes = (new Congregacoes())->orderBy('nome', 'asc')->get();
        $theme = Cookie::get('theme') ?? 'light';
        $this->enableDefaultLayout = false;

        return $this->view('auth/signin-executors', compact('congregacoes', 'theme'));
    }

    public function store(Request $request)
    {
        ExecutoresInserirRules::validate($request);

        $nome = $request->get('nome');
        $email = $request->get('email');
        $password = $request->get('password');
        $congregacoes = $request->get('congregacoes');

        $user = (new Usuarios())->create([
            'nome' => $nome,
            'username' => $this->createUsername($nome),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'perfil_id' => 5
        ]);

        if ($congregacoes[0] == 999) {
            $congregacoes = (new Congregacoes())->orderBy('nome', 'asc')->get()->pluck('id');
        }

        foreach ($congregacoes as $congregacao) {
            if (!(new CongregacaoMembros())->where('usuario_id', $user->id)->where('congregacao_id', $congregacao)->first()) {
                (new CongregacaoMembros())->create([
                    'usuario_id' => $user->id,
                    'congregacao_id' => $congregacao,
                    'perfil_id' => 5
                ]);
            }
        }

        return Redirect::flash(
            '/login',
            [
                'success' => true,
                'message' => 'Conta criada com sucesso! Faça login para continuar.'
            ]
        );
    }

    private function createUsername($nome)
    {
        $nome = explode(' ', $nome);
        $username = $nome[0];
        if (isset($nome[1])) {
            $username .= $nome[1];
        }
        $username = strtolower($username);
        return $username;
    }
}

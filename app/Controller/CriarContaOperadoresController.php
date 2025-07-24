<?php

namespace App\Controller;

use App\Models\CongregacaoMembros;
use Kernel\Request;
use Kernel\Redirect;
use App\Models\Congregacoes;
use App\Models\Usuarios;
use App\Rules\SupervisoresInserirRules;
use Kernel\Cookie;

class CriarContaOperadoresController extends Controller
{
    public function create() {
        $congregacoes = (new Congregacoes())->orderBy('nome', 'asc')->get();
        $theme = Cookie::get('theme') ?? 'light';
        $this->enableDefaultLayout = false;

        return $this->view('auth/signin-operators', compact('congregacoes', 'theme'));
    }

    public function store(Request $request) {
        SupervisoresInserirRules::validate($request);

        $nome = $request->get('nome');
        $email = $request->get('email');
        $password = $request->get('password');
        $congregacao = $request->get('congregacao');

        $user = (new Usuarios())->create([
            'nome' => $nome,
            'username' => $this->createUsername($nome),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'perfil_id' => 4
        ]);

        (new CongregacaoMembros())->create([
            'usuario_id' => $user->id,
            'congregacao_id' => $congregacao,
            'perfil_id' => 4
        ]);
        
        return Redirect::flash(
            '/login',
            [
                'success' => true,
                'message' => 'Conta criada com sucesso! Faça login para continuar.'
            ]
        );
    }

    private function createUsername($nome) {
        $nome = explode(' ', $nome);
        $username = $nome[0];
        if(isset($nome[1])) {
            $username .= $nome[1];
        }
        $username = strtolower($username);
        return $username;
    }
}
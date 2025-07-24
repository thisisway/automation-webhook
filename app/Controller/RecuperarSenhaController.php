<?php

namespace App\Controller;

use App\Models\Usuarios;
use App\Services\SendEmail;
use Kernel\Cookie;
use Kernel\Request;
use Kernel\Redirect;
use Kernel\Redis;

class RecuperarSenhaController extends Controller
{
    public function step1() {
        $this->enableDefaultLayout = false;
        $theme = Cookie::get('theme') ?? 'light';
        return $this->view('auth/recover-password-step-1', compact('theme'));
    }

    public function step2(Request $request) {

        $email = $request->email;
        $usuario = (new Usuarios)->where('email', $request->email)->first();
        if(!$usuario) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Email não encontrado'
            ]);
        }

        $code = rand(100000, 999999);
        SendEmail::send($usuario->email, 'Recuperação de senha', 'recover-password-email', ['code' => $code]);

        $redis = new Redis;
        $redis->set('recover_password_code_' . $usuario->email, $code, 60*10);

        $this->enableDefaultLayout = false;
        $theme = Cookie::get('theme') ?? 'light';
        return $this->view('auth/recover-password-step-2', compact('theme', 'email'));
    }

    public function step3(Request $request) {
        $redis = new Redis;
        $code = $redis->get('recover_password_code_' . $request->email);
        if($code != $request->codigo) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Código de recuperação inválido ou expirado'
            ]);
        }

        $usuario = (new Usuarios)->where('email', $request->email)->first();
        $usuario->password = password_hash($request->password, PASSWORD_DEFAULT);
        $usuario->save();

        return Redirect::flash('/login',[
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ]);
    }
}
<?php

namespace App\Controller;

use App\Controller\Controller;
use App\Guard\AccessControl;
use Kernel\Redirect;
use Kernel\Request;
use Kernel\Session;
use App\Models\Usuarios;
use Kernel\Cookie;
use App\Models\CongregacaoMembros;
use App\Models\Empresa;
use App\Models\PermissoesUsuario;
use App\Repositories\PerfilRepository;
use Kernel\Redis;

class AuthController extends Controller
{

    public function index()
    {
        if ($this->checkRememberSession()) {
            Redirect::to('/dashboard');
        }

        Session::delete('user');
        Session::delete('remember');
        $this->enableDefaultLayout = false;
        
        $cookie = new Cookie;
        $theme = $cookie->get('theme') ?? 'light';
        
        return $this->view('auth/login', compact('theme'));
    }
    

    public function authenticate(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $remember = (isset($request->remember)) ? true : false;

        [$logged, $msg, $user] = (new Usuarios())->attempt($email, $password);
        if ($logged) {            
            $sessionData = [
                'user_id' => $user->id,
                'name' => $user->nome,
                'username' => $user->username,
                'remember' => $remember,
                'perfil_id' => $user->perfil_id,
                'perfil' => $user->perfil,
                'foto_perfil' => $user->foto_perfil ? $user->foto_perfil : '/assets/images/user/avatar-1.jpg'
            ];

            $empresa = (new Empresa)->find(1)->original_values;
            array_shift($empresa);
            $sessionData = array_merge($sessionData, $empresa);

            // Adiciona o tema aos dados da sessão usando a chave específica do usuário
            $redis = new Redis;
            $sessionData['theme'] = $redis->get('theme_user_' . $user->id) ?? 'dark';

            Session::setMultiple($sessionData);

            $permissions = (new PermissoesUsuario())->where('usuario_id', $user->id)->get()->pluck('permissao');
            AccessControl::setPermissions($user->id, $permissions);
            AccessControl::setPerfils($user->id, (new PerfilRepository)->getPerfils($user->id));

            if(in_array($user->perfil_id, [2, 3, 4])) {
                $congregacao = (new CongregacaoMembros())
                    ->where('usuario_id', $user->id)
                    ->first();
                Cookie::set('congregacao_id', $congregacao->congregacao_id);
            }

            if ($remember) {
                $this->remember($user->id, $sessionData);
                Cookie::set('remember', true);
                Cookie::set('remember_user', $user->id);
            }
            
            Redirect::to('/dashboard');
        } else {
            Session::setError('login', $msg);
            Redirect::to('/login');
        }
    }

    private function remember($user_id, $userData)
    {
        $redis = new Redis;
        $redis->set('remember_'.$user_id, true);
        $redis->set('user_data_'.$user_id, json_encode($userData));
    }

    private function checkRememberSession()
    {
        if (!Cookie::has('remember') || !Cookie::get('remember')) {
            return false;
        }

        $redis = new Redis;
        $user_id = Cookie::get('remember_user');
        
        if (!$user_id || !$redis->get('remember_'.$user_id)) {
            return false;
        }

        $userData = json_decode($redis->get('user_data_'.$user_id), true);
        
        if (!$userData) {
            return false;
        }

        foreach ($userData as $key => $value) {
            Session::set($key, $value);
        }

        $permissions = (new PermissoesUsuario())->where('usuario_id', $user_id)->get()->pluck('permissao');
        AccessControl::setPermissions($user_id, $permissions);
        AccessControl::setPerfils($user_id, (new PerfilRepository)->getPerfils($user_id));

        return true;
    }

    public function switchCongregacao(Request $request)
    {
        $congregacao_id = $request->get('congregacao_id');
        if(!$congregacao_id && !in_array(Session::get('perfil'), ['Administrador', 'Executor']))
        {
            return $this->json([
                'success' => false, 
                'message' => 'Operação não permitida '
            ]);
        }

        if(in_array(Session::get('perfil'), ['Administrador', 'Executor'])){
            Cookie::set('congregacao_id', $congregacao_id);
            return $this->json(['success' => true]);
        }
        
        $temAcesso = (new CongregacaoMembros())
            ->where('usuario_id', Session::get('user_id'))
            ->where('congregacao_id', $congregacao_id)
            ->count();
        
        if ($temAcesso) {
            Cookie::set('congregacao_id', $congregacao_id);
            return $this->json(['success' => true]);
        }
        
        return $this->json(['success' => false, 'message' => 'Acesso não autorizado a esta congregação']);
    }

    public function logoff()
    {
        $user_id = Session::get('user_id');
        AccessControl::removePermissions($user_id);
        
        // Limpa dados do Redis (mantendo o tema)
        $redis = new Redis;
        $redis->del('remember_'.$user_id);
        $redis->del('user_data_'.$user_id);
        
        Session::destroy();
        Cookie::delete('congregacao_id');
        Cookie::delete('remember');
        Cookie::delete('remember_user');
        Redirect::to('/login');
    }
}

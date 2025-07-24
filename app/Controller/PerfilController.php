<?php

namespace App\Controller;

use App\Models\Usuarios;
use App\Utils\ManipulaImages;
use Kernel\Request;
use Kernel\Redirect;
use Kernel\Session;
use Kernel\Storage;

class PerfilController extends Controller
{
    public function edit() {
        $usuario = (new Usuarios)->where('id', Session::get('user_id'))->first();
        return $this->view('profile/edit', compact('usuario'));
    }

    public function update(Request $request) {

        $usuario = (new Usuarios)->where('id', Session::get('user_id'))->first();

        if($request->hasFile('foto_perfil')){
            $file = Storage::store($request->file('foto_perfil'));

            $file = ManipulaImages::optimize($file);

            $datetime = date('Ymdhis');
            $file->name = "{$usuario->username}{$datetime}.{$file->extension}";

            $fotoPerfil = Storage::storeS3($file, 'usuarios/perfil');

            $usuario->foto_perfil = $fotoPerfil->remote_path;
            $usuario->save();
            
            Session::set('foto_perfil', $usuario->foto_perfil);

            Storage::remove($file);
        }

        $senhaAtual = $request->get('senha_atual');
        $novaSenha = $request->get('nova_senha');
        $confirmarSenha = $request->get('confirmar_senha');

        if(password_verify($senhaAtual, $usuario->password)){
            if($novaSenha == $confirmarSenha){
                $usuario->password = password_hash($novaSenha, PASSWORD_DEFAULT);
            }else{
                return Redirect::flashBack([
                    'success' => false,
                    'message' => 'As novas senhas não coincidem'
                ]);
            }
        }else{
            return Redirect::flashBack([
                'success' => false,
                'message' => 'Senha atual incorreta'
            ]);
        }

        $usuario->nome = $request->get('nome');
        $usuario->email = $request->get('email');
        $usuario->save();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso!'
        ]);
    }
}
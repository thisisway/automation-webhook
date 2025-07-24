<?php

namespace App\Guard;

use Kernel\Cookie;
use Kernel\Redirect;
use Kernel\Redis;
use Kernel\Session;

class AccessControl
{
    public static function setPermissions($user_id, $permissions)
    {
        $redis = new Redis();
        $redis->set('permissions_user_'.$user_id, json_encode($permissions));
    }

    public static function setPerfils($user_id, $perfils)
    {
        $redis = new Redis();
        $redis->set('perfil_user_'.$user_id, json_encode($perfils));
    }

    public static function loadPermissions($user_id )
    {
        $redis = new Redis();
        $permissions = $redis->get('permissions_user_'.$user_id);
        if(!$permissions)
            Redirect::to('/login');
        return json_decode($permissions, true);
    }

    public static function loadPerfils($user_id)
    {
        $redis = new Redis();
        return json_decode($redis->get('perfil_user_'.$user_id), true);
    }

    public static function removePermissions($user_id   )
    {
        $redis = new Redis();
        $redis->del('permissions_user_'.$user_id);
    }

    public static function removePerfil($user_id)
    {
        $redis = new Redis();
        $redis->del('perfil_user_'.$user_id);
    }

    public static function checkAccess($permissao, $mensagem)
    {
        $user_permission = self::hasPermission($permissao);

        if(!$user_permission) {
            Redirect::flash('/acesso-negado?mensagem='.$mensagem, null);
        }
    }

    public static function hasPermission($permissao)
    {
        $permissions = self::loadPermissions(Session::get('user_id'));
        //caso permissões seja um array, verificar se o usuário tem pelo menos uma das permissões

        if(is_array($permissao)) {
            foreach($permissao as $p) {
                if(in_array($p, $permissions)) {
                    return true;
                }
            }
        }

        //caso permissões seja uma string, verificar se o usuário tem a permissão
        if(is_string($permissao)) {
            if(in_array($permissao, $permissions) || in_array('full_access', $permissions)) {
                return true;
            }
        }

        return false;
    }

    public static function hasPerfil($perfil)
    {
        if(Session::get('perfil') == 'Administrador') 
            return true;
        
        $perfils = self::loadPerfils(Session::get('user_id'));

        if(is_array($perfil)) {
            foreach($perfil as $p) {
                if(in_array($p, $perfils)) {
                    return true;
                }
            }
        }

        return in_array($perfil, $perfils);
    }

    public static function hasPermissionOrPerfil($permissao, $perfil)
    {
        return self::hasPermission($permissao) || self::hasPerfil($perfil);
    }
}

<?php

use App\Guard\AccessControl;

function hasPermission($permissao)
{
    return AccessControl::hasPermission($permissao);
}

function hasPerfil($perfil)
{
    return AccessControl::hasPerfil($perfil);
}   

function hasPermissionOrPerfil($permissao, $perfil)
{
    return AccessControl::hasPermissionOrPerfil($permissao, $perfil);
} 




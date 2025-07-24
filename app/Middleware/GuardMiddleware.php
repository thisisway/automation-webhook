<?php
namespace App\Middleware;

use App\Controller\Controller;
use App\Guard\AccessControl;
use Kernel\Server;

class GuardMiddleware implements Middleware {
    public function rules($args){

        $controller = 'App\Controller\\' . $args->controller;
        $classController = new $controller;
        if(method_exists($classController, 'registerPermissions')){
            $classController->registerPermissions();
            $permission = $classController->getFunctionPermission($args->controllerMethod);
            $perfils = $classController->getFunctionPerfils($args->controllerMethod);

            return AccessControl::hasPermissionOrPerfil($permission, $perfils);
        }

        return true;
    }
    
    public function failure(){
        $controller = new Controller;
        $controller->view('acessoNegado');
        die();
    }
}
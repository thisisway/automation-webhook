<?php
namespace Kernel;


trait Guard {
    private $controllerFunctionPermissions;
    private $controllerFunctionPerfils;

    public function registerFunctionPermission($function, $permission) {
        $this->controllerFunctionPermissions[$function] = $permission;
    }

    public function registerFunctionPerfils($function, $perfil) {
        $this->controllerFunctionPerfils[$function] = $perfil;
    }

    public function getFunctionPermission($function) {
        if(isset($this->controllerFunctionPermissions[$function]))
            return $this->controllerFunctionPermissions[$function];
        return false;
    }
    
    public function getFunctionPerfils($function) {
        if(isset($this->controllerFunctionPerfils[$function]))
            return $this->controllerFunctionPerfils[$function];
        return false;
    }
}
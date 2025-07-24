<?php
namespace App\Middleware;
use Kernel\Session;
use Kernel\Redirect;

class AuthMiddleware implements Middleware
{
    public function rules($args)
    {
        if(Session::get('user_id') || Session::get('remember'))
            return true;
        return false;    
    }

    public function failure(){
        Redirect::to('/login');
    }
}
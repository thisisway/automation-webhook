<?php

namespace App\Controller;
use Kernel\Request;
use Kernel\Redirect;

class AlterarSenhaController extends Controller
{
    public function index() {
        return $this->view('auth/alterar-senha');
    }

    public function update(Request $request) {
        
        return Redirect::flashBack([
            'success' => true,
            'message' => 'success'
        ]);
    }
}
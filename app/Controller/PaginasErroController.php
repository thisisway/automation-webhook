<?php

namespace App\Controller;
use Kernel\Request;
use Kernel\Redirect;

class PaginasErroController extends Controller
{
    public function acessoNegado( Request $request ) {
        $mensagem = $request->get('mensagem');
        return $this->view('acessoNegado', ['mensagem' => $mensagem]);
    }

}
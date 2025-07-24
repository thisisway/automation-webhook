<?php

namespace App\Controller;

use Kernel\Request;
use Kernel\Redirect;
use App\Repositories\OrdemServicoRepository;

class OrdemServicoImprimirController extends Controller
{
    public function registerPermissions()
    {
        $this->registerFunctionPerfils('index', ['Administrador', 'Supervisor', 'Operador', 'Executor']);
        $this->registerFunctionPerfils('imprimirPdf', ['Administrador', 'Supervisor', 'Operador', 'Executor']);
    }

    public function index(Request $request, $id)
    {
        // Verifica se o ID da OS foi passado
        if (!$id) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'ID da Ordem de Serviço não informado'
            ]);
        }

        $dados = (new OrdemServicoRepository)->getDadosCompletos($id);
        // Renderiza a view de impressão
        return $this->view('service_order/print', $dados);
    }
}
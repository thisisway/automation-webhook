<?php
namespace App\Controller;

use App\Models\OrdemServico;
use Kernel\Request;

class CalendarioController extends Controller
{

    public function registerPermissions(){
        $this->registerFunctionPerfils('index',['Administrador','Supervisor', 'Secretario', 'Operador', 'Executor']);
        $this->registerFunctionPerfils('calendar',['Administrador','Supervisor', 'Secretario', 'Operador', 'Executor']);
    }


    public function index()
    {
        return $this->view('calendar/index');
    }

    public function calendar(Request $request)
    {
        $startDate = (new \DateTime($request->start))->format('Y-m-d');
        $endDate = (new \DateTime($request->end))->format('Y-m-d');
        $ordensServicos = (new OrdemServico)
            ->select('ordemservico.*', 'clientes.nome_rsocial', 'status_servico')
            ->join('clientes', 'ordemservico.cliente_id', 'clientes.id')
            ->whereBetween('dtagendamento', [$startDate, $endDate])
            ->get();

        $payload = [];
        foreach ($ordensServicos as $ordem) {
            $payload[] = [
                'id' => $ordem->id,
                'title' => $ordem->nome_rsocial,
                'start' => $ordem->dtagendamento,
                'url' => '/ordem-servico/executar/' . $ordem->id
            ];
        }

        return $this->json($payload);
    }
}
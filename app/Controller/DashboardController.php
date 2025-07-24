<?php

namespace App\Controller;

use App\Models\Congregacoes;
use App\Models\OrdemServico;
use App\Models\OrdemServicoStatus;
use Kernel\Cookie;
use Kernel\Request;
use Kernel\Session;

class DashboardController extends Controller
{

    public function registerPermissions(){
        $this->registerFunctionPerfils('index', ['Adminstrador', 'Supervisor', 'Operador', 'Executor']);
        $this->registerFunctionPerfils('getEvolucaoDiaria', ['Adminstrador', 'Supervisor', 'Operador', 'Executor']);
        $this->registerFunctionPerfils('getStatusOS', ['Adminstrador', 'Supervisor', 'Operador', 'Executor']);
        $this->registerFunctionPerfils('getTiposManutencao', ['Adminstrador', 'Supervisor', 'Operador', 'Executor']);
    }

    public function index(Request $request)
    {
        $perfil = Session::get('perfil') ?? 'analytcs';
        $perfil = strtolower($perfil);

        if($perfil == 'administrador'){
            return $this->dashboardAdministrador($request);
        }
        if($perfil == 'supervisor'){
            return $this->dashboardSupervisor($request);
        }
        if($perfil == 'operador'){
            return $this->dashboardOperador($request);
        }
        if($perfil == 'executor'){
            return $this->dashboardExecutor($request);
        }
        
        throw new \Exception('Perfil não encontrado');
    }

    public function dashboardAdministrador($request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7; // Período padrão de 7 dias
        $selected_congregacao = Cookie::get('congregacao_id') ? Cookie::get('congregacao_id') : null;

        $data_inicio = date('Y-m-d', strtotime("-{$periodo} days"));
        // Query base com condição de congregação
        $query = (new OrdemServico)->where('dtagendamento', '>=', $data_inicio);
        if ($selected_congregacao) {
            $query->where('congregacao_id', $selected_congregacao);
        }

        // Contadores principais
        $total_os = (clone $query)->count();

        $os_em_andamento = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->EM_EXECUCAO)
            ->count();

        $os_concluidas = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        $os_atrasadas = (clone $query)
            ->where('dtagendamento', '<', date('Y-m-d'))
            ->where('status_id', '!=', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        extract($this->getCongregacoes());
        return $this->view('dashboards/administrador', compact(
            'total_os',
            'os_em_andamento',
            'os_concluidas',
            'os_atrasadas',
            'lista_congregacoes',
            'selected_congregacao',
            'periodo'
        ));
    }

    public function getStatusOS(Request $request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7;
        $selected_congregacao = Cookie::get('congregacao_id') ?? null;
        $data_inicio = date('Y-m-d', strtotime("-{$periodo} days"));
        
        $query = (new OrdemServico)->where('dtagendamento', '>=', $data_inicio);
        if ($selected_congregacao) {
            $query->where('congregacao_id', $selected_congregacao);
        }

        $status_os = $query
            ->select('ordemservico_status.descricao as status, count(*) as total')
            ->join('ordemservico_status', 'status_id', 'ordemservico_status.id')
            ->groupBy('ordemservico_status.descricao')
            ->get()
            ->toArray();

        return $this->json($status_os);
    }

    public function getTiposManutencao(Request $request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7;
        $selected_congregacao = Cookie::get('congregacao_id') ?? null;
        $data_inicio = date('Y-m-d', strtotime("-{$periodo} days"));
        
        $query = (new OrdemServico)->where('dtagendamento', '>=', $data_inicio);
        if ($selected_congregacao) {
            $query->where('congregacao_id', $selected_congregacao);
        }

        $os_por_tipo = $query
            ->select('tm.descricao as tipo, count(*) as total')
            ->join('tipomanutencao tm', 'ordemservico.tipomanutencao', 'tm.id')
            ->groupBy('tm.descricao')
            ->get()
            ->toArray();

        return $this->json($os_por_tipo);
    }

    public function getEvolucaoDiaria(Request $request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7;
        $selected_congregacao = Cookie::get('congregacao_id') ? Cookie::get('congregacao_id') : null;
        $data_inicio = new \DateTime(date('Y-m-d', strtotime("-{$periodo} days")));
        $data_fim = new \DateTime(date('Y-m-d'));
        
        $query = (new OrdemServico)->where('dtagendamento', '>=', $data_inicio->format('Y-m-d'));
        if ($selected_congregacao) {
            $query->where('congregacao_id', $selected_congregacao);
        }

        $ordens_servico = $query->get();

        $evolucao_diaria = [];

        //separa ordens de serviço por data
        while($data_inicio <= $data_fim){
            $evolucao_diaria[$data_inicio->format('Y-m-d')] = [
                'abertas' => 0,
                'atrasadas' => 0,
                'fechadas' => 0
            ];

            foreach($ordens_servico as $os){
                if((new \DateTime($os->created_at))->format('Y-m-d') == $data_inicio->format('Y-m-d')){
                    $evolucao_diaria[$data_inicio->format('Y-m-d')]['abertas']++;
                }
                if(!$os->dtrealizacao && (new \DateTime($os->dtagendamento))->format('Y-m-d') < $data_inicio->format('Y-m-d')){
                    $evolucao_diaria[$data_inicio->format('Y-m-d')]['atrasadas']++;
                }
                if($os->dtrealizacao &&(new \DateTime($os->dtrealizacao))->format('Y-m-d') == $data_inicio->format('Y-m-d')){
                    $evolucao_diaria[$data_inicio->format('Y-m-d')]['fechadas']++;
                }
            }

            $data_inicio->modify('+1 day');
        }

        return $this->json($evolucao_diaria);
    }

    public function dashboardSupervisor(Request $request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7; // Período padrão de 7 dias
        $selected_congregacao = Cookie::get('congregacao_id') ? Cookie::get('congregacao_id') : null;

        $data_inicio = date('Y-m-d', strtotime("-{$periodo} days"));
        // Query base com condição de congregação
        $query = (new OrdemServico)
            ->where('dtagendamento', '>=', $data_inicio)
            ->where('congregacao_id', $selected_congregacao);

        // Contadores principais
        $total_os = (clone $query)->count();

        $os_em_andamento = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->EM_EXECUCAO)
            ->count();

        $os_concluidas = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        $os_atrasadas = (clone $query)
            ->where('dtagendamento', '<', date('Y-m-d'))
            ->where('status_id', '!=', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        extract($this->getCongregacoes());
        return $this->view('dashboards/supervisor', compact(
            'total_os',
            'os_em_andamento',
            'os_concluidas',
            'os_atrasadas',
            'lista_congregacoes',
            'selected_congregacao',
            'periodo'
        ));
    }

    public function dashboardOperador(Request $request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7; // Período padrão de 7 dias
        $selected_congregacao = Cookie::get('congregacao_id') ? Cookie::get('congregacao_id') : null;

        $data_inicio = date('Y-m-d', strtotime("-{$periodo} days"));
        // Query base com condição de congregação
        $query = (new OrdemServico)
            ->where('dtagendamento', '>=', $data_inicio)
            ->where('congregacao_id', $selected_congregacao);

        // Contadores principais
        $total_os = (clone $query)->count();

        $os_em_andamento = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->EM_EXECUCAO)
            ->count();

        $os_concluidas = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        $os_atrasadas = (clone $query)
            ->where('dtagendamento', '<', date('Y-m-d'))
            ->where('status_id', '!=', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        extract($this->getCongregacoes());
        return $this->view('dashboards/operador', compact(
            'total_os',
            'os_em_andamento',
            'os_concluidas',
            'os_atrasadas',
            'lista_congregacoes',
            'selected_congregacao',
            'periodo'
        ));
    }

    public function dashboardExecutor(Request $request)
    {
        $periodo = $request->get('periodo') ? $request->get('periodo') : 7; // Período padrão de 7 dias
        $selected_congregacao = Cookie::get('congregacao_id') ? Cookie::get('congregacao_id') : null;

        $data_inicio = date('Y-m-d', strtotime("-{$periodo} days"));
        // Query base com condição de congregação
        $query = (new OrdemServico)->where('dtagendamento', '>=', $data_inicio)->where('operador_id', Session::get('user_id'));
        if ($selected_congregacao) {
            $query->where('congregacao_id', $selected_congregacao);
        }

        // Contadores principais
        $total_os = (clone $query)->count();

        $os_em_andamento = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->EM_EXECUCAO)
            ->count();

        $os_concluidas = (clone $query)
            ->where('status_id', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        $os_atrasadas = (clone $query)
            ->where('dtagendamento', '<', date('Y-m-d'))
            ->where('status_id', '!=', (new OrdemServicoStatus)->FINALIZADA)
            ->count();

        extract($this->getCongregacoes());
        return $this->view('dashboards/executor', compact(
            'total_os',
            'os_em_andamento',
            'os_concluidas',
            'os_atrasadas',
            'lista_congregacoes',
            'selected_congregacao',
            'periodo'
        ));
    }

    private function getCongregacoes()
    {
        $lista_congregacoes = (new Congregacoes)->select('congregacoes.*')
            ->join('congregacao_membros', 'congregacoes.id', 'congregacao_membros.congregacao_id')
            ->where('congregacao_membros.usuario_id', Session::get('user_id'))
            ->get()
            ->pluck('nome', 'id');
        $selected_congregacao = Cookie::get('congregacao_id') ?? array_key_first($lista_congregacoes);
        return [
            'lista_congregacoes' => $lista_congregacoes,
            'selected_congregacao' => $selected_congregacao
        ];
    }
}

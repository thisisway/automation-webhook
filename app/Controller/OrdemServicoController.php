<?php

namespace App\Controller;

use App\Models\CongregacaoMembros;
use App\Models\Congregacoes;
use App\Models\OrdemServico;
use App\Models\OrdemServicoStatus;
use App\Models\TipoManutencao;
use App\Models\Usuarios;
use App\Repositories\OrdemServicoRepository;
use App\Repositories\Supervisor;
use App\Rules\OrdemServicoRules;
use App\Rules\OrdemServicoUpdateRules;
use App\Utils\ManipulaDatas;
use Kernel\Cookie;
use Kernel\Redirect;
use Kernel\Request;
use Kernel\Session;


class OrdemServicoController extends Controller
{

    public function registerPermissions()
    {
        $this->registerFunctionPerfils('index', ['Administrador', 'Supervisor',  'Operador', 'Executor']);
        $this->registerFunctionPerfils('list', ['Administrador', 'Supervisor',  'Operador', 'Executor']);
        $this->registerFunctionPerfils('delete', ['Administrador', 'Supervisor', 'Operador']);
        $this->registerFunctionPerfils('create', ['Administrador', 'Supervisor', 'Operador']);
        $this->registerFunctionPerfils('store', ['Administrador', 'Supervisor', 'Operador']);
        $this->registerFunctionPerfils('edit', ['Administrador', 'Supervisor', 'Operador']);
        $this->registerFunctionPerfils('update', ['Administrador', 'Supervisor', 'Operador']);
        $this->registerFunctionPerfils('visualizar', ['Administrador', 'Supervisor', 'Operador', 'Executor']);
        $this->registerFunctionPerfils('imprimir', ['Administrador', 'Supervisor', 'Operador', 'Executor']);
    }

    public function index(Request $request)
    {
        $data_inicial = $request->get('data_inicial');
        $data_final = $request->get('data_final');
        $tipo_os = $request->get('tipo_os');
        $status = $request->get('status');
        $executor = $request->get('executor');

        $tipoManutencao = (new TipoManutencao)->get();
        $todosStatus = (new OrdemServicoStatus)->get();

        // Busca os executores
        $executores = (new Usuarios)
            ->select('usuarios.*')
            ->join('congregacao_membros', 'congregacao_membros.usuario_id', 'usuarios.id')
            ->whereIn('congregacao_membros.perfil_id', [(new CongregacaoMembros)->OPERADOR, (new CongregacaoMembros)->EXECUTOR])
            ->groupBy('usuarios.id');
        if (Cookie::get('congregacao_id')) {
            $executores->where('congregacao_membros.congregacao_id', Cookie::get('congregacao_id'));
        }
        $executores = $executores->get();

        // Busca estatísticas para os cards com os mesmos filtros da listagem
        $stats = $this->getStats($request);

        return $this->enableModule('datatable')->view('service_order/list', 
            compact(
                'stats', 
                'executores', 
                'tipoManutencao', 
                'todosStatus',
                'data_inicial',
                'data_final',
                'tipo_os',
                'status',
                'executor'
            )
        );
    }

    private function getStats(Request $request)
    {
        // Query base
        $query = $this->getBaseQuery($request);

        $hoje = date('Y-m-d');
        return (object)[
            'aguardando_aprovacao' => (clone $query)->where('ordemservico.status_id', (new OrdemServicoStatus)->AGUARDANDO_APROVACAO)->count(),
            'abertas' => (clone $query)->where('ordemservico.status_id', (new OrdemServicoStatus)->AGENDADA)->count(),
            'execucao' => (clone $query)->where('ordemservico.status_id', (new OrdemServicoStatus)->EM_EXECUCAO)->count(),
            'finalizadas' => (clone $query)->where('ordemservico.status_id', (new OrdemServicoStatus)->FINALIZADA)->count(),
            'atrasadas' => (clone $query)
                ->where('ordemservico.status_id', (new OrdemServicoStatus)->AGENDADA)
                ->count()
        ];
    }

    private function getBaseQuery(Request $request)
    {
        // Query base
        $query = (new OrdemServico)
            ->select(
                'ordemservico.id',
                'ordemservico.created_at as dtabertura',
                'ordemservico.dtagendamento',
                'ordemservico.equipamento',
                'congregacoes.nome as congregacao',
                'usuarios.nome as operador',
                'ordemservico_status.descricao as status'
            )
            ->join('congregacoes', 'congregacoes.id', 'ordemservico.congregacao_id')
            ->leftJoin('usuarios', 'usuarios.id', 'ordemservico.operador_id')
            ->join('ordemservico_status', 'ordemservico_status.id', 'ordemservico.status_id');
        
        $congregacao_id = Cookie::get('congregacao_id') ? Cookie::get('congregacao_id') : null;
        if($congregacao_id){
            $query->where('ordemservico.congregacao_id', $congregacao_id);
        }

        if(Session::get('perfil') == 'Executor'){
            $query->where('ordemservico.operador_id', Session::get('user_id'));
        }

        // aplicar filtro de data
        if($request->get('data_inicial')){
            $data_inicial = ManipulaDatas::formatarISO($request->get('data_inicial'));
            if($request->get('classificacao_periodo') == 1){
                $query->where('ordemservico.created_at', '>=', $data_inicial.' 00:00:00');
            }else{
                $query->where('ordemservico.dtagendamento', '>=', $data_inicial.' 00:00:00');
            }
        }
        if($request->get('data_final')){
            $data_final = ManipulaDatas::formatarISO($request->get('data_final'));
            if($request->get('classificacao_periodo') == 1){
                $query->where('ordemservico.created_at', '<=', $data_final.' 23:59:59');
            }else{
                $query->where('ordemservico.dtagendamento', '<=', $data_final.' 23:59:59');
            }
        }
        if($request->get('tipo_os')){
            $query->where('ordemservico.tipomanutencao', $request->get('tipo_os'));
        }
        if($request->get('status')){
            $query->where('ordemservico.status_id', $request->get('status'));
        }
        if($request->get('executor')){
            $query->where('ordemservico.operador_id', $request->get('executor'));
        }

        return $query;
    }

    public function list(Request $request, $returnStats = false)
    {
        // Parâmetros do DataTables
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search') ? $request->get('search')['value'] : null;
        $order = $request->get('order') ? $request->get('order')[0] : null;
        $columns = $request->get('columns');

        // Colunas ordenáveis
        $orderableColumns = [
            0 => 'ordemservico.id',
            1 => 'congregacao',
            2 => 'operador',
            3 => 'ordemservico.dtagendamento',
            4 => 'ordemservico.equipamento',
            5 => 'status'
        ];

        // Usar a query base sem filtros
        $query = $this->getBaseQuery($request);

        // Total de registros sem filtros
        $recordsTotal = (clone $query)->count();

        // aplicar filtro de busca
        if($search){
            $search = strtolower($search);
            

            if(ManipulaDatas::isBR($search)){
                $data = ManipulaDatas::formatarISO($search);
                $query->whereBetween('ordemservico.dtagendamento', [$data, $data]);
            }else{
                $query->where('lower(ordemservico.equipamento)', 'LIKE', "%{$search}%")
                ->orWhere('lower(congregacoes.nome)', 'LIKE', "%{$search}%")
                ->orWhere('lower(usuarios.nome)', 'LIKE', "%{$search}%")
                ->orWhere('lower(status)', 'LIKE', "%{$search}%");
            }
        }

        // Aplicar ordenação
        if (isset($orderableColumns[$order['column']])) {
            $query->orderBy($orderableColumns[$order['column']], $order['dir']);
        }
        // Total de registros com filtros
        $recordsFiltered = (clone $query)->count();

        // Aplicar paginação
        $query->offset($start)->limit($length);

        // Executar query
        $data = $query->get()->toArray();

        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function delete($id)
    {
        try {
            $os = new OrdemServico();
            $os = $os->find($id);

            if (!$os) {
                return Redirect::flashBack(['error' => true, 'message' => 'Ordem de serviço não encontrada']);
            }

            if (!in_array($os->status_id, [(new OrdemServicoStatus)->AGENDADA, (new OrdemServicoStatus)->AGUARDANDO_APROVACAO])) {
                return Redirect::flashBack(['error' => true, 'message' => 'Apenas ordens de serviço agendadas ou aguardando aprovação podem ser removidas']);
            }

            $os->status_id = (new OrdemServicoStatus)->CANCELADA;
            $os->ultimo_usuario = Session::get('username');
            $os->save();

            return Redirect::flashBack(['success' => true, 'message' => 'Ordem de serviço cancelada com sucesso']);
        } catch (\Exception $e) {
            return Redirect::flashBack(['error' => true, 'message' => 'Erro ao remover ordem de serviço: '.$e->getMessage()]);
        }
    }

    public function create()
    {
        $congregacao_id = Cookie::get('congregacao_id') ?? null;

        $congregacoes = [];
        $usuario = Session::get('user_id');
        $perfil = Session::get('perfil');
        $supervisores = (new Supervisor)->getSupervisores();
        $supervisorCongregacao   = (new Supervisor)->getSupervisor($congregacao_id);
        $tiposManutencao = (new TipoManutencao)->get();

        //lista de congragações de acordo com o perfil do usuário
        if ($perfil == 'Administrador') {
            $congregacoes = (new Congregacoes)->get();
        } else {
            $congregacoes = (new Congregacoes)
                ->select('congregacoes.*')
                ->join('congregacao_membros', 'congregacao_membros.congregacao_id', 'congregacoes.id')
                ->where('congregacao_membros.usuario_id', $usuario)
                ->get();
        }

        //retorna somente os operadores de acordo com a congregação, caso não tenha congregação, retorna todos os operadores
        if ($congregacao_id) {
            $operadores = (new Usuarios)
                ->select('usuarios.*')
                ->join('congregacao_membros', 'congregacao_membros.usuario_id', 'usuarios.id')
                ->where('congregacao_membros.congregacao_id', $congregacao_id)
                ->whereIn('congregacao_membros.perfil_id', [(new CongregacaoMembros)->OPERADOR, (new CongregacaoMembros)->EXECUTOR])
                ->get();
        } else {
            $operadores = (new Usuarios)->whereIn('perfil_id', [(new CongregacaoMembros)->OPERADOR, (new CongregacaoMembros)->EXECUTOR])->get();
        }

        return $this->view('service_order/create', compact(
            'congregacoes',
            'operadores',
            'congregacao_id',
            'supervisorCongregacao',
            'supervisores',
            'tiposManutencao'
        ));
    }

    public function store(Request $request)
    {
        try {
            OrdemServicoRules::validate($request);
            $supervisor_id = $request->supervisor_id ?? (new Supervisor)->getSupervisor($request->congregacao_id);

            $ordemServico = new OrdemServico();
            $data = [
                'congregacao_id' => $request->get('congregacao_id'),
                'equipamento' => $request->get('equipamento'),
                'modelo_tipo' => $request->get('modelo_tipo'),
                'observacoes' => $request->get('observacoes'),
                'problemas' => $request->get('problemas'),
                'operador_id' => $request->get('operador_id') ?: null,
                'status_id' => (new OrdemServicoStatus)->AGUARDANDO_APROVACAO,
                'dtagendamento' => $request->get('dtagendamento') ?: null,
                'supervisor_id' => $request->get('supervisor_id') ?: null,
                'tipomanutencao' => $request->get('tipomanutencao') ?: null,
                'usuario' => Session::get('username'),
                'ultimo_usuario' => Session::get('username')
            ];
            $ordemServico->create($data);

            return Redirect::to('/ordem-servico', [
                'success' => true,
                'message' => 'Ordem de serviço criada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Erro ao criar ordem de serviço: '.$e->getMessage()
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $tiposManutencao = (new TipoManutencao)->get();
        $ordemServico = (new OrdemServico)->find($id);
        if (!$ordemServico) {
            Session::setFlash(['error' => true, 'message' => 'Ordem de serviço não encontrada']);
            return Redirect::to('/ordem-servico');
        }

        // Define mensagens de status que não permitem alteração
        $statusMensagens = [
            1 => 'agendada',
            2 => 'em execução',
            3 => 'finalizada',
            4 => 'cancelada'
        ];

        // Verifica se o status atual está entre os que não permitem alteração
        if (array_key_exists($ordemServico->status_id, $statusMensagens)) {
            Session::setFlash([
                'error' => true,
                'message' => "Não é possível alterar uma ordem de serviço que está {$statusMensagens[$ordemServico->status_id]}"
            ]);
            return Redirect::to('/ordem-servico');
        }

        $congregacao_id = $ordemServico->congregacao_id;
        $congregacao = (new Congregacoes)->find($congregacao_id);
        $congregacoes = [];
        $supervisores = (new Supervisor)->getSupervisores();
        $supervisorCongregacao   = (new Supervisor)->getSupervisor($congregacao_id);

        //lista de congragações de acordo com o perfil do usuário
        if (Session::get('perfil') == 'Administrador') {
            $congregacoes = (new Congregacoes)->get();
        } else {
            $congregacoes = (new Congregacoes)
                ->select('congregacoes.*')
                ->join('congregacao_membros', 'congregacao_membros.congregacao_id', 'congregacoes.id')
                ->where('congregacao_membros.usuario_id', Session::get('user_id'))
                ->get();
        }

        //retorna somente os operadores de acordo com a congregação, caso não tenha congregação, retorna todos os operadores
        if ($congregacao_id) {
            $operadores = (new Usuarios)
                ->select('usuarios.*')
                ->join('congregacao_membros', 'congregacao_membros.usuario_id', 'usuarios.id')
                ->where('congregacao_membros.congregacao_id', $congregacao_id)
                ->whereIn('congregacao_membros.perfil_id', [(new CongregacaoMembros)->OPERADOR, (new CongregacaoMembros)->EXECUTOR])
                ->get();
        } else {
            $operadores = (new Usuarios)->whereIn('perfil_id', [(new CongregacaoMembros)->OPERADOR, (new CongregacaoMembros)->EXECUTOR])->get();
        }

        return $this->view('service_order/edit', compact(
            'ordemServico',
            'congregacao_id',
            'congregacoes',
            'congregacao',
            'operadores',
            'supervisorCongregacao',
            'supervisores',
            'tiposManutencao'
        ));
    }

    public function update(Request $request, $id)
    {
        try {
            OrdemServicoUpdateRules::validate($request);

            $ordemServico = (new OrdemServico)->find($id);
            if (!$ordemServico) {
                Session::setFlash(['error' => true, 'message' => 'Ordem de serviço não encontrada']);
                return Redirect::to('/ordem-servico');
            }

            // Define mensagens de status que não permitem alteração
            $statusMensagens = [
                2 => 'em execução',
                3 => 'finalizada',
                4 => 'cancelada'
            ];

            // Verifica se o status atual está entre os que não permitem alteração
            if (array_key_exists($ordemServico->status_id, $statusMensagens)) {
                Session::setFlash([
                    'error' => true,
                    'message' => "Não é possível alterar uma ordem de serviço que está {$statusMensagens[$ordemServico->status_id]}"
                ]);
                return Redirect::to('/ordem-servico');
            }

            $data = [
                'equipamento' => $request->get('equipamento'),
                'modelo_tipo' => $request->get('modelo_tipo'),
                'observacoes' => $request->get('observacoes'),
                'problemas' => $request->get('problemas'),
                'tipomanutencao' => $request->get('tipomanutencao'),
            ];

            if($request->get('operador_id') && $request->get('dtagendamento')){
                $data['status_id'] = (new OrdemServicoStatus)->AGENDADA;
            }

            $ordemServico->update($data);

            return Redirect::flashBack([
                'success' => true,
                'message' => 'Ordem de serviço atualizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            Session::setFlash($request->all());
            return Redirect::flashBack([
                'error' => true,
                'message' => 'Erro ao atualizar ordem de serviço. '.$e->getMessage()
            ]);
        }
    }

    public function visualizar(Request $request, $id)
    {
        if (!$id) {
            return $this->json([
                'error' => true,
                'message' => 'ID da Ordem de Serviço não informado'
            ], 400);
        }

        $dados = (new OrdemServicoRepository)->getDadosCompletos($id);

        $dados['ordemServico']->created_at = ManipulaDatas::formatarDataHoraBR($dados['ordemServico']->created_at);

        if(!is_null($dados['ordemServico']->dtagendamento))
            $dados['ordemServico']->dtagendamento = ManipulaDatas::formatarDataHoraBR($dados['ordemServico']->dtagendamento);

        if (!$dados) {
            return $this->json([
                'error' => true,
                'message' => 'Ordem de serviço não encontrada'
            ], 404);
        }

        // Retornar os dados da OS em formato JSON para o modal
        return $this->json([
            'success' => true,
            'ordem_servico' => $dados['ordemServico'],
            'congregacao' => $dados['empresa'],
            'operador' => [ 'nome' => $dados['ordemServico']->operador ],
            'supervisor' => [ 'nome' => $dados['ordemServico']->supervisor ]
        ]);
    }
}

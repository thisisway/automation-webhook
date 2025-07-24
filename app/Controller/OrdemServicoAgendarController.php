<?php

namespace App\Controller;

use App\Models\CongregacaoMembros;
use App\Models\Congregacoes;
use App\Models\OrdemServico;
use App\Utils\ManipulaDatas;
use Kernel\Request;
use Kernel\Redirect;

class OrdemServicoAgendarController extends Controller
{
    public function agendar($id) {
        $ordem_servico = (new OrdemServico())
            ->select('ordemservico.*', 
                'congregacoes.nome as congregacao', 
                'ordemservico_status.descricao as status', 
                'usuarios.nome as operador',
                'supervisor.nome as supervisor',
                'tipomanutencao.descricao as tipomanutencao'
            )
            ->join('congregacoes', 'congregacoes.id', 'ordemservico.congregacao_id')
            ->join('ordemservico_status', 'ordemservico_status.id', 'ordemservico.status_id')
            ->leftJoin('usuarios', 'usuarios.id', 'ordemservico.operador_id')
            ->leftJoin('usuarios as supervisor', 'supervisor.id', 'ordemservico.supervisor_id')
            ->join('tipomanutencao', 'tipomanutencao.id', 'ordemservico.tipomanutencao')
            ->where('ordemservico.id', $id)
            ->first();
        $ordem_servico->data_agendamento = ManipulaDatas::formatarBR($ordem_servico->dtagendamento);
        $ordem_servico->hora_agendamento = ManipulaDatas::hora($ordem_servico->dtagendamento);

        $executores = (new CongregacaoMembros())
            ->select('usuarios.id', 'usuarios.nome')
            ->join('usuarios', 'usuarios.id', 'congregacao_membros.usuario_id')
            ->where('congregacao_id', $ordem_servico->congregacao_id)
            ->where('congregacao_membros.perfil_id', (new CongregacaoMembros())->EXECUTOR)
            ->get();
        $congregacao = (new Congregacoes())->where('id', $ordem_servico->congregacao_id)->first();

        return $this->view('service_order/evaluation', [
            'ordem_servico' => $ordem_servico,
            'executores' => $executores,
            'congregacao' => $congregacao
        ]);
    }

    public function store(Request $request, $id) {

        $data = ManipulaDatas::formatarISO($request->get('data_agendamento'));
        $hora = $request->get('hora_agendamento');
        $data_agendamento = $data . ' ' . $hora;
        $executor = $request->get('executor');

        $ordem_servico = (new OrdemServico())->find($id);
        $ordem_servico->dtagendamento = $data_agendamento;
        $ordem_servico->operador_id = $executor;
        $ordem_servico->status_id = 1;
        $ordem_servico->save();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Ordem de serviço agendada com sucesso'
        ]);
    }

    public function edit($id) {
        return $this->view('viewfolder/update');
    }

    public function update(Request $request) {
        
        return Redirect::flashBack([
            'success' => true,
            'message' => 'success'
        ]);
    }
}
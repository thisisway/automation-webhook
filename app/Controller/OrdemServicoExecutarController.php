<?php

namespace App\Controller;

use App\Models\OrdemServico;
use App\Models\OrdemServicoProdutosServicos;
use App\Models\OrdemServicoStatus;
use Kernel\Request;
use Kernel\Redirect;
use Kernel\Session;

class OrdemServicoExecutarController extends Controller
{
    public function execute($id)
    {
        // Busca a ordem de serviço
        $ordem = (new OrdemServico)->select(
            'ordemservico.*',
            'congregacoes.nome as cliente_nome'
        )
            ->join('congregacoes', 'congregacoes.id', 'ordemservico.congregacao_id')
            ->where('ordemservico.id', $id)
            ->first();

        if (!$ordem) {
            Session::setFlash(['error' => true, 'message' => 'Ordem de serviço não encontrada']);
            return Redirect::to('/ordem-servico');
        }

        

        // Verifica se a OS já não está em execução ou finalizada
        if (in_array($ordem->status_id, [(new OrdemServicoStatus)->FINALIZADA])) {
            Session::setFlash([
                'error' => true,
                'message' => 'Esta ordem de serviço não pode ser executada pois já está finalizada'
            ]);
            return Redirect::to('/ordem-servico');
        }

        $statusMensagens = [
            (new OrdemServicoStatus)->CANCELADA => 'cancelada',   
            (new OrdemServicoStatus)->FINALIZADA => 'finalizada',
            (new OrdemServicoStatus)->AGUARDANDO_APROVACAO => 'aguardando aprovação'
        ];

        // Verifica se o status atual está entre os que não permitem alteração
        if (array_key_exists($ordem->status_id, $statusMensagens)) {
            Session::setFlash([
                'error' => true,
                'message' => "Não é possível executar uma ordem de serviço que está {$statusMensagens[$ordem->status_id]}"
            ]);
            return Redirect::to('/ordem-servico');
        }

        // Atualiza o status para Em Execução
        $ordemUpdate = (new OrdemServico)->find($id)->update([
            'status_id' => (new OrdemServicoStatus)->EM_EXECUCAO
        ]);

        return $this->view('service_order/execute', compact('ordem'));
    }

    public function buscarProdutosServicos(Request $request)
    {
        try {
            $termo = $request->get('termo');

            if (empty($termo)) {
                return $this->json([]);
            }

            $produtos = (new OrdemServicoProdutosServicos)
                ->select('descricao')
                ->where('descricao', 'LIKE', "%{$termo}%")
                ->groupBy('descricao')
                ->limit(10)
                ->get();

            $sugestoes = [];
            foreach ($produtos as $produto) {
                $sugestoes[] = $produto->descricao;
            }

            return $this->json($sugestoes);
        } catch (\Exception $e) {
            return $this->json([
                'error' => true,
                'message' => 'Erro ao buscar produtos/serviços: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request, $id)
    {
        try {
            // Busca a ordem de serviço
            $ordem = (new OrdemServico)->find($id);
            if (!$ordem) {
                throw new \Exception('Ordem de serviço não encontrada');
            }

            // Verifica se a OS está em execução
            if ($ordem->status_id != (new OrdemServicoStatus)->EM_EXECUCAO) {
                throw new \Exception('Esta ordem de serviço não está em execução');
            }

            // Salva os produtos
            $produto_servico = $request->get('itens');
            foreach ($produto_servico as $ps) {
                (new OrdemServicoProdutosServicos)->create([
                    'ordem_servico_id' => $id,
                    'descricao' => $ps['descricao'],
                    'quantidade' => $ps['quantidade'],
                    'desconto' => str_replace(['.', ','], ['', '.'], $ps['desconto']),
                    'valor_unitario' => str_replace(['.', ','], ['', '.'], $ps['valor']),
                    'valor_total' => $ps['quantidade'] * str_replace(['.', ','], ['', '.'], $ps['valor'])
                ]);
            }

            // Atualiza o status da OS para Finalizada
            $ordem->status_id = (new OrdemServicoStatus)->FINALIZADA;
            $ordem->dtrealizacao = date('Y-m-d H:i:s');
            $ordem->save();

            Session::setFlash([
                'success' => true,
                'message' => 'Ordem de serviço finalizada com sucesso!'
            ]);
            return Redirect::to('/ordem-servico');
        } catch (\Exception $e) {

            Session::setFlash([
                'error' => true,
                'message' => 'Erro ao finalizar ordem de serviço: ' . $e->getMessage()
            ]);

            return Redirect::to("/ordem-servico/executar/$id");
        }
    }
}

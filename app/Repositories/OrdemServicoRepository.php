<?php

namespace App\Repositories;

use App\Models\Empresa;
use App\Models\OrdemServico;
use App\Models\OrdemServicoProdutosServicos;

class OrdemServicoRepository
{
    public function getOrdemServico($id)
    {
        return (new OrdemServico)
        ->select('ordemservico.*', 
            'congregacoes.nome as congregacao', 
            'operadores.nome as operador',
            'supervisores.nome as supervisor',
            'ordemservico_status.descricao as status'
        )
        ->join('congregacoes', 'congregacoes.id', 'ordemservico.congregacao_id')
        ->leftJoin('usuarios as operadores', 'operadores.id', 'ordemservico.operador_id')
        ->join('usuarios as supervisores', 'supervisores.id', 'ordemservico.supervisor_id')
        ->join('ordemservico_status', 'ordemservico_status.id', 'ordemservico.status_id')
        ->where('ordemservico.id', $id)
        ->first();
    }

    public function getCongregacao($ordemServicoId)
    {
        return (new OrdemServico)
            ->select('congregacao.*')
            ->join('congregacao', 'congregacao.id', 'ordemservico.congregacao_id')
            ->where('ordemservico.id', $ordemServicoId)
            ->first();
    }

    public function getProdutosServicos($ordemServicoId)
    {
        return (new OrdemServicoProdutosServicos)->where('ordem_servico_id', $ordemServicoId)->get();
    }

    public function getEmpresa()
    {
        return (new Empresa)->first();
    }

    public function getDadosCompletos($id)
    {
        return [
            'empresa' => $this->getEmpresa(),
            'ordemServico' => $this->getOrdemServico($id),
            'produtos_servicos' => $this->getProdutosServicos($id)
        ];
    }
}
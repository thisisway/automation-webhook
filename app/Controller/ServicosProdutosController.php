<?php

namespace App\Controller;

use App\Models\Equipamentos;
use App\Models\ModelosTipos;
use App\Models\OrdemServico;
use App\Models\ServicoProduto;
use Kernel\Request;
use Kernel\Redirect;

class ServicosProdutosController extends Controller
{
    public function index()
    {
        $servicosProdutos = (new ServicoProduto)->all();
        return $this->view('servicos_produtos/index', compact('servicosProdutos'));
    }

    public function create()
    {
        return $this->view('servicos_produtos/create');
    }

    public function store(Request $request)
    {
        // Validação dos dados
        // ServicosProdutosRules::validate($request);

        (new ServicoProduto)->create([
            'nome' => $request->get('nome'),
            'descricao' => $request->get('descricao'),
            'preco' => $request->get('preco'),
            'tipo' => $request->get('tipo') // 'servico' ou 'produto'
        ]);

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Serviço/Produto criado com sucesso!'
        ]);
    }

    public function edit($id)
    {
        $servicoProduto = (new ServicoProduto)->find($id);
        return $this->view('servicos_produtos/edit', compact('servicoProduto'));
    }

    public function update(Request $request, $id)
    {
        // Validação dos dados
        // ServicosProdutosRules::validate($request);

        $servicoProduto = (new ServicoProduto)->find($id);
        $servicoProduto->update([
            'nome' => $request->get('nome'),
            'descricao' => $request->get('descricao'),
            'preco' => $request->get('preco'),
            'tipo' => $request->get('tipo')
        ]);

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Serviço/Produto atualizado com sucesso!'
        ]);
    }

    public function delete($id)
    {
        $servicoProduto = (new ServicoProduto)->find($id);
        $servicoProduto->delete();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Serviço/Produto removido com sucesso!'
        ]);
    }

    public function buscarEquipamentos()
    {
        $search = $_GET['q'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $equipamentos = (new OrdemServico)
            ->select('equipamento as nome')
            ->where('LOWER(equipamento)', 'LIKE', "%".strtolower($search)."%") 
            ->groupBy('equipamento')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = (new OrdemServico())
            ->where('LOWER(equipamento)', 'LIKE', "%".strtolower($search)."%")
            ->count();

        $response = [
            'results' => $equipamentos->toArray(),
            'pagination' => [
                'more' => ($offset + $limit) < $total
            ]
        ];

        echo json_encode($response);
    }

    public function buscarModelos()
    {
        $search = $_GET['q'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $modelos = (new OrdemServico)
            ->select('modelo_tipo as nome')
            ->where('LOWER(modelo_tipo)', 'LIKE', "%".strtolower($search)."%")
            ->groupBy('modelo_tipo')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $total = (new OrdemServico)
            ->where('LOWER(modelo_tipo)', 'LIKE', "%".strtolower($search)."%")
            ->count();

        $response = [
            'results' => $modelos->toArray(),
            'pagination' => [
                'more' => ($offset + $limit) < $total
            ]
        ];

        echo json_encode($response);
    }
}

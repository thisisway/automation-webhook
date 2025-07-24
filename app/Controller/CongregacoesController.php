<?php

namespace App\Controller;

use App\Models\Congregacoes;
use App\Rules\CongregacoesRules;
use App\Rules\CongregacoesUpdateRules;
use Kernel\Request;
use Kernel\Redirect;

class CongregacoesController extends Controller
{
    public function registerPermissions()
    {
        $this->registerFunctionPermission('index', 'congregacoes.listar');
        $this->registerFunctionPermission('create', 'congregacoes.inserir');
        $this->registerFunctionPermission('store', 'congregacoes.inserir');
        $this->registerFunctionPermission('edit', 'congregacoes.editar');
        $this->registerFunctionPermission('update', 'congregacoes.editar');
        $this->registerFunctionPermission('info', 'congregacoes.listar');
    }

    public function index()
    {
        $congregacoes = (new Congregacoes)->all();
        return $this->enableModule('datatable')->view('congregations/list', compact('congregacoes'));
    }

    public function create()
    {
        return $this->view('congregations/create');
    }

    public function store(Request $request)
    {
        // Validação dos campos da Congregação
        CongregacoesRules::validate($request);

        // Modelos de Congregações e Endereços
        $congregacoesModel = new Congregacoes();

        // Criação do registro da Congregação
        $congregacao = $congregacoesModel->create([
            'nome'              => $request->congregation,
            'telefone_fixo'     => $this->clearPhone($request->fixed_number),
            'telefone_celular'  => $this->clearPhone($request->mobile_number),
            'data_abertura'     => $request->join_date,
            'observacoes'       => $request->observacoes,
            'cep'               => $this->clearCep($request->cep),
            'logradouro'        => $request->logradouro,
            'numero'            => $request->numero,
            'complemento'       => $request->complemento,
            'ponto_referencia'  => $request->ponto_referencia,
            'bairro'            => $request->bairro,
            'cidade'            => $request->cidade,
            'uf'                => $request->uf,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude
        ]);

        // Redirecionamento com mensagem de sucesso
        Redirect::flash('/congregacoes', [
            'success' => true,
            'message' => 'Congregação ' . $congregacao->nome . ' inserida com sucesso!'
        ]);
    }

    public function edit($id)
    {
        $congregacao = (new Congregacoes)->find($id);
        return $this->view('congregations/edit', compact('congregacao'));
    }

    public function update(Request $request, $id)
    {
        // Validação dos campos da Congregação
        CongregacoesUpdateRules::validate($request);
        
        $congregacao = (new Congregacoes)->find($id);
        $congregacao->nome = $request->get('nome');
        $congregacao->telefone_fixo = $this->clearPhone($request->fixed_number);
        $congregacao->mobile_number = $this->clearPhone($request->mobile_number);
        $congregacao->data_abertura = $request->get('join_date');
        $congregacao->cep = $this->clearCep($request->get('cep'));
        $congregacao->logradouro = $request->get('logradouro');
        $congregacao->numero = $request->get('numero');
        $congregacao->complemento = $request->get('complemento');
        $congregacao->ponto_referencia = $request->get('ponto_referencia');
        $congregacao->bairro = $request->get('bairro');
        $congregacao->cidade = $request->get('cidade');
        $congregacao->uf = $request->get('uf');
        $congregacao->latitude = $request->get('latitude');
        $congregacao->longitude = $request->get('longitude');
        $congregacao->observacoes = $request->get('observacoes');
        $congregacao->save();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Congregação atualizada com sucesso!'
        ]);
    }

    public function info($id) {
        $congregacao = (new Congregacoes)->find($id);
        return $this->json($congregacao);
    }

    private function clearPhone($value)
    {
        return str_replace(['(', ')', ' ', '-'], '', $value);
    }

    private function clearCep($value)
    {
        return str_replace(['.', '-'], '', $value);
    }
}

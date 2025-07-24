<?php
namespace App\Controller;

use App\Guard\AccessControl;
use App\Helpers\Strings;
use App\Models\Empresa;
use Kernel\Redirect;
use Kernel\Request;

class MinisterioController extends Controller 
{

    public function registerPermissions(){
        $this->registerFunctionPermission('edit', 'configuracoes.ministerio.edit');
        $this->registerFunctionPermission('update', 'configuracoes.ministerio.edit');
    }

    public function edit(Request $request) 
    {
        $empresa = (new Empresa)->first();
        return $this->view('ministerio/edit', compact('empresa'));
    }

    public function update(Request $request) 
    {
        $empresa = (new Empresa)->find(1);

        $empresa->cnpj = Strings::clearCpfCnpj($request->cnpj);
        $empresa->rsocial = $request->rsocial;
        $empresa->nfantasia = $request->nfantasia;
        $empresa->cep = Strings::clearCep($request->cep);
        $empresa->logradouro = $request->logradouro;
        $empresa->numero = $request->numero;
        $empresa->complemento = $request->complemento;
        $empresa->bairro = $request->bairro;
        $empresa->cidade = $request->cidade;
        $empresa->estado = $request->estado;
        $empresa->telefone = Strings::clearPhone($request->telefone);
        $empresa->email = $request->email;
        $empresa->save();

        return Redirect::flashBack([
            'success' => true,
            'message' => 'Dados cadastrais atualizados com sucesso!'
        ]);
    }
}
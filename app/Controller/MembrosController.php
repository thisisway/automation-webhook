<?php

namespace App\Controller;

use App\Helpers\Strings;
use App\Models\Cargos;
use App\Models\Congregacoes;
use App\Models\Membros;
use App\Rules\MembrosInserirRules;
use App\Utils\ImageManipulator;
use Kernel\Cookie;
use Kernel\Request;
use Kernel\Redirect;
use Kernel\Session;
use Kernel\Storage;

class MembrosController extends Controller
{
    public function registerPermissions() {
        $this->registerFunctionPermission('index', 'membros.listar');
        $this->registerFunctionPermission('list', 'membros.listar');
        $this->registerFunctionPermission('create', 'membros.inserir');
        $this->registerFunctionPermission('store', 'membros.inserir');
        $this->registerFunctionPermission('edit', 'membros.editar');
        $this->registerFunctionPermission('update', 'membros.editar');
        $this->registerFunctionPermission('delete', 'membros.remover');
        $this->registerFunctionPermission('carteirinha', 'membros.carteirinha');
    }

    
    public function index() {
        $congregacoes = (Session::get('perfil') !== 'administrador') ? 
            (new Congregacoes)->where('supervisor_id', Session::get('user_id')) : 
            (new Congregacoes)->all();
        
        return $this->enableModule('datatable')->view('members/list', compact('congregacoes'));
    }

    public function list(Request $request) {
        $membrosModel = new Membros();
        
        // Parâmetros do DataTables
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'];
        $order = $request->get('order')[0];
        $columns = $request->get('columns');
        
        // Colunas ordenáveis
        $orderableColumns = [
            0 => 'nome_completo',
            1 => 'telefone',
            2 => 'congregacao',
            3 => 'cargo',
            4 => 'bairro'
        ];
        
        // Query base
        $query = (new Membros)->select(
            'membros.id',
            'membros.nome_completo',
            'membros.email',
            'membros.telefone',
            'congregacoes.nome as congregacao',
            'membros.bairro',
            'membros.cidade',
            'membros.foto',
            'cargos.nome as cargo'
        )
        ->join('congregacoes', 'congregacoes.id', 'membros.congregacao_id')
        ->join('cargos', 'cargos.id', 'membros.cargo_id');

        if(Cookie::get('congregacao_id')) {
           $query->where('congregacoes.id', Cookie::get('congregacao_id'));
        }
        
        // Pesquisa
        if ($search) {
            $query->where('nome_completo', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('telefone', 'LIKE', "%{$search}%")
                  ->orWhere('cidade', 'LIKE', "%{$search}%");
        }
        // Total de registros sem filtro
        $totalRecords = (new Membros)->count();
        
        // Total de registros com filtro
        $totalDisplayRecords = (clone $query)->count();
        
        // Ordenação
        if (isset($orderableColumns[$order['column']])) {
            $query->orderBy($orderableColumns[$order['column']], $order['dir']);
        }

        // Paginação
        $membros = $query->limit($length)->offset($start)->get();
        
        $response = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalDisplayRecords,
            'data' => []
        ];
        
        foreach ($membros as $membro) {
            $response['data'][] = [
                'nome' => [
                    'nome_completo' => $membro->nome_completo,
                    'email' => $membro->email,
                    'foto' => $membro->foto ?? '../assets/images/user/avatar-1.jpg'
                ],
                'telefone' => $membro->telefone,
                'congregacao' => $membro->congregacao,
                'cargo' => $membro->cargo,
                'bairro' => $membro->bairro,
                'cidade' => $membro->cidade,
                'acoes' => $membro->id
            ];
        }
        
        echo json_encode($response);
    }

    public function info($id) {
        $membrosModel = new Membros();
        $membro = $membrosModel->find($id);
        
        if (!$membro) {
            http_response_code(404);
            echo json_encode(['error' => 'Membro não encontrado']);
            return;
        }

        echo json_encode($membro);
    }

    public function create() {
        $cargos = (new Cargos)->orderBy('nivel_hierarquico', 'ASC')->get();
        if (Session::get('perfil') == 'Administrador') {
            $congregacoes = (new Congregacoes)->orderBy('nome', 'ASC')->get();
        } else if (Session::get('perfil') == 'Supervisor') {
            $congregacoes = (new Congregacoes)
                ->where('supervisor_id', Session::get('user_id'))
                ->orderBy('nome', 'ASC')
                ->get();
        } else {
            $congregacoes = [];
        }
        return $this->view('members/form', [
            'cargos' => $cargos,
            'congregacoes' => $congregacoes
        ]);
    }

    public function store(Request $request) {
        
        MembrosInserirRules::validate($request);
        // Tratamento da foto
        if ($request->hasFile('foto')) {
            $fotoTemp = Storage::store($request->files->foto);

            $fotoResized = (new ImageManipulator($fotoTemp))->resize(600)->save();
            
            //guarda no S3
            $foto = Storage::storeS3($fotoResized, '/membros/perfil');
            $data['foto'] = $foto->path;

            //remove o arquivo temporário
            Storage::remove($fotoTemp);

        }
        
        try {
            $dadosMembro = [
                'nome_completo' => Strings::clean($request->get('nome_completo')),
                'email' => filter_var($request->get('email'), FILTER_SANITIZE_EMAIL),
                'telefone' => Strings::clearPhone($request->get('telefone')),
                'data_nascimento' => $request->get('data_nascimento'),
                'data_batismo' => $request->get('data_batismo'), 
                'data_cargo' => $request->get('data_cargo'),
                'cargo_id' => (int)$request->get('cargo_id'),
                'nome_contato_emergencia' => Strings::clean($request->get('nome_contato_emergencia')),
                'contato_emergencia' => Strings::clearPhone($request->get('contato_emergencia')),
                'foto' => isset($data['foto']) ? $data['foto'] : null,
                'cep' => Strings::clearCep($request->get('cep')),
                'logradouro' => Strings::clean($request->get('logradouro')),
                'numero' => Strings::clean($request->get('numero')),
                'complemento' => Strings::clean($request->get('complemento')),
                'bairro' => Strings::clean($request->get('bairro')),
                'cidade' => Strings::clean($request->get('cidade')),
                'estado' => Strings::clean($request->get('estado')),
                'congregacao_id' => (int)$request->get('congregacao_id')
            ];

            (new Membros)->create($dadosMembro);
            return Redirect::flashBack([
                'success' => true,
                'message' => 'Membro cadastrado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return Redirect::flashBack([
                'success' => false,
                'message' => 'Erro ao cadastrar membro: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id) {
        
        $membro = (new Membros)->find($id);
        if (!$membro) {
            return $this->view('errors/404');
        }
        
        $cargos = (new Cargos)->where('status', '1')->orderBy('nivel_hierarquico', 'ASC')->get();
        
        return $this->view('members/form', [
            'membro' => $membro,
            'cargos' => $cargos
        ]);
    }

    public function update(Request $request, $id) {
        $data = [
            'nome_completo' => Strings::clean($request->get('nome_completo')),
            'email' => filter_var($request->get('email'), FILTER_SANITIZE_EMAIL),
            'telefone' => Strings::clearPhone($request->get('telefone')),
            'data_nascimento' => $request->get('data_nascimento'),
            'data_batismo' => $request->get('data_batismo'),
            'data_filiacao' => $request->get('data_filiacao'),
            'data_cargo' => $request->get('data_cargo'),
            'cargo_id' => (int)$request->get('cargo_id'),
            'nome_contato_emergencia' => Strings::clean($request->get('nome_contato_emergencia')),
            'contato_emergencia' => Strings::clearPhone($request->get('contato_emergencia')),
            'cep' => Strings::clearCep($request->get('cep')),
            'logradouro' => Strings::clean($request->get('logradouro')),
            'numero' => Strings::clean($request->get('numero')),
            'complemento' => Strings::clean($request->get('complemento')),
            'bairro' => Strings::clean($request->get('bairro')),
            'cidade' => Strings::clean($request->get('cidade')),
            'estado' => Strings::clean($request->get('estado'))
        ];
        
        // Tratamento da foto
        if ($request->hasFile('foto')) {
            $fotoTemp = Storage::store($request->files->foto);

            $fotoResized = (new ImageManipulator($fotoTemp))->resize(600)->save();
            
            //guarda no S3
            $foto = Storage::storeS3($fotoResized, '/membros/perfil');
            $data['foto'] = $foto->path;
            
            // Remove foto antiga
            $membro = (new Membros)->find($id);
            if ($membro && $membro->foto && Storage::fileExistsS3($membro->foto)) {
                Storage::removeS3($membro->foto);
            }

            //remove o arquivo temporário
            Storage::remove($fotoTemp);
        }
        
        try {
            $membro = (new Membros)->find($id);
            $membro->update($data);
            return Redirect::flashBack([
                'success' => true,
                'message' => 'Membro atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return Redirect::flashBack([
                'success' => false,
                'message' => 'Erro ao atualizar membro: ' . $e->getMessage()
            ]);
        }
    }

    public function carteirinha($id) {
        $membrosModel = new Membros();
        $membro = $membrosModel->find($id);
        
        if (!$membro) {
            return $this->view('errors/404');
        }
        
        Storage::log('cards.log', sprintf(
            "[%s] Card printed for member %s (ID: %d)\n",
            date('Y-m-d H:i:s'),
            $membro->nome_completo,
            $membro->id
        ));
        
        return $this->view('members/card', [
            'membro' => $membro
        ]);
    }

    public function delete($id) {
        $membrosModel = new Membros();
        $membrosModel->delete($id);
        return Redirect::flashBack([
            'success' => true,
            'message' => 'Membro removido com sucesso!'
        ]);
    }

    private function handleFoto($foto, $oldFoto = null) {
        if ($foto) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($foto->getMimeType(), $allowedTypes)) {
                throw new \Exception('Tipo de arquivo não permitido');
            }
            
            // Remove foto antiga se existir
            if ($oldFoto && file_exists($oldFoto)) {
                unlink($oldFoto);
            }
            
            // ... resto do código de upload
        }
    }
}
<?php

namespace App\Console;

use App\Models\Cargos;
use App\Models\Congregacoes;
use App\Models\Empresa;
use App\Models\OrdemServicoStatus;
use App\Models\Perfil;
use App\Models\Permissoes;
use App\Models\PermissoesUsuario;
use App\Models\TipoManutencao;
use App\Models\Usuarios;

/*
    to use commands follow next command
    php cello console functionName
*/


class Commands
{
    public function HelloWorld()
    {
        echo "Hello world" . PHP_EOL;
    }

    public function seed()
    {
        echo "Seeding the database..." . PHP_EOL;
        if ((new Usuarios)->where('username', 'admin')->count() == 0) {
            (new Usuarios)->create([
                'nome' => 'Administrador',
                'username' => 'admin',
                'password' => password_hash('P@ss.4321', PASSWORD_DEFAULT),
                'email' => 'suporte@reobote.tec.br',
                'perfil_id' => 1,
                'telefone' => '85987228494',
                'status' => 'active'
            ]);
            echo "Admin user created successfully" . PHP_EOL;
        }

        if ((new Empresa)->where('cnpj', '08741007000176')->count() == 0) {
            (new Empresa)->create([
                'cnpj' => '08741007000176',
                'rsocial' => 'IGREJA EVANGELICA ASSEMBLEIA DE DEUS DE MESSEJANA',
                'nfantasia' => 'IEADEME',
                'cep' => '60871684',
                'logradouro' => 'ESTRADA BARAO DE AQUIRAZ',
                'numero' => '2150',
                'complemento' => '',
                'bairro' => 'COACU',
                'cidade' => 'FORTALEZA',
                'estado' => 'CE',
                'telefone' => '8532761284',
                'telefone2' => '85999691461',
                'email' => 'ieademe@hotmail.com',
                'chave_conta' => ''
            ]);
            echo "Enterprise created successfully" . PHP_EOL;
        }

        $permissoes = [
            ['permissao' => 'dashboard.admin', 'descricao' => 'Dashboard de administrador', 'categoria' => 'Dashboard'],
            ['permissao' => 'dashboard.operador', 'descricao' => 'Dashboard de operador', 'categoria' => 'Dashboard'],
            ['permissao' => 'ordem.servico.listar', 'descricao' => 'Listar ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.inserir', 'descricao' => 'Criar ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.executar', 'descricao' => 'Executar ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.assinar', 'descricao' => 'Assinar ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.imprimir', 'descricao' => 'Imprimir ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.editar', 'descricao' => 'Editar ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.cancelar', 'descricao' => 'Cancelar ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'ordem.servico.excluir', 'descricao' => 'Excluir ordens de serviço', 'categoria' => 'Ordens de serviço'],
            ['permissao' => 'congregacoes.listar', 'descricao' => 'Listar congregações', 'categoria' => 'Congregações'],
            ['permissao' => 'congregacoes.inserir', 'descricao' => 'Inserir novas congregações', 'categoria' => 'Congregações'],
            ['permissao' => 'congregacoes.editar', 'descricao' => 'Editar congregações', 'categoria' => 'Congregações'],
            ['permissao' => 'congregacoes.excluir', 'descricao' => 'Excluir congregações', 'categoria' => 'Congregações'],
            ['permissao' => 'membros.listar', 'descricao' => 'Listar membros', 'categoria' => 'Membros'],
            ['permissao' => 'membros.inserir', 'descricao' => 'Cadastrar novos membros', 'categoria' => 'Membros'],
            ['permissao' => 'membros.editar', 'descricao' => 'Editar membros', 'categoria' => 'Membros'],
            ['permissao' => 'membros.excluir', 'descricao' => 'Excluir membros', 'categoria' => 'Membros'],
            ['permissao' => 'calendario.listar', 'descricao' => 'Ver todos os eventos no calendário', 'categoria' => 'Calendário'],
            ['permissao' => 'usuarios.listar', 'descricao' => 'Listar usuários', 'categoria' => 'Usuários'],
            ['permissao' => 'usuarios.inserir', 'descricao' => 'Criar novos usuários', 'categoria' => 'Usuários'],
            ['permissao' => 'usuarios.editar', 'descricao' => 'Editar usuários', 'categoria' => 'Usuários'],
            ['permissao' => 'usuarios.excluir', 'descricao' => 'Excluir usuários', 'categoria' => 'Usuários'],
            ['permissao' => 'permissoes.listar', 'descricao' => 'Listar permissões existentes', 'categoria' => 'Permissões'],
            ['permissao' => 'configuracoes.ministerio.edit', 'descricao' => 'Editar informações do ministério', 'categoria' => 'Configurações']
        ];

        foreach ($permissoes as $permissao) {
            if ((new Permissoes)->where('permissao', $permissao['permissao'])->count() == 0) {
                (new Permissoes)->create($permissao);
                echo "Permission created successfully: " . $permissao['permissao'] . PHP_EOL;
            }
        }

        echo "Set permissions to administrator...".PHP_EOL;
        foreach ($permissao as $permissao) {
            if(!(new PermissoesUsuario)->where('permissao', '=', 'full_access')->where('usuario_id', 1)->count()) {
                (new PermissoesUsuario)->create([
                    'permissao' => 'full_access',
                    'usuario_id' => 1
                ]);
            }
        }

        echo "Full access set to administrator.".PHP_EOL;


        $cargos = [
            ['nome' => 'Membro', 'descricao' => 'Membro da igreja', 'nivel_hierarquico' => 1],
            ['nome' => 'Auxiliar', 'descricao' => 'Auxiliar de ministério', 'nivel_hierarquico' => 2],
            ['nome' => 'Diácono', 'descricao' => 'Diácono da igreja', 'nivel_hierarquico' => 3],
            ['nome' => 'Presbítero', 'descricao' => 'Presbítero da igreja', 'nivel_hierarquico' => 4],
            ['nome' => 'Evangelista', 'descricao' => 'Evangelista da igreja', 'nivel_hierarquico' => 5],
            ['nome' => 'Pastor', 'descricao' => 'Pastor da igreja', 'nivel_hierarquico' => 6]
        ];

        foreach ($cargos as $cargo) {
            if ((new Cargos)->where('nome', $cargo['nome'])->count() == 0) {
                (new Cargos)->create($cargo);
                echo "Cargo criado com sucesso: " . $cargo['nome'] . PHP_EOL;
            }
        }

        echo "Cargos configurados com sucesso." . PHP_EOL;


        $perfis = [
            ['nome' => 'Administrador', 'descricao' => 'Acesso total ao sistema', 'nivel_hierarquico' => 1],
            ['nome' => 'Supervisor', 'descricao' => 'Supervisiona uma ou mais congregações', 'nivel_hierarquico' => 2],
            ['nome' => 'Secretário', 'descricao' => 'Responsável por tarefas administrativas', 'nivel_hierarquico' => 3],
            ['nome' => 'Operador', 'descricao' => 'Operador do sistema com acesso limitado', 'nivel_hierarquico' => 4],
            ['nome' => 'Executor', 'descricao' => 'Executa as ordens de serviço', 'nivel_hierarquico' => 5] 
        ];

        foreach ($perfis as $perfil) {
            if ((new Perfil)->where('nome', $perfil['nome'])->count() == 0) {
                (new Perfil)->create($perfil);
                echo "Perfil criado com sucesso: " . $perfil['nome'] . PHP_EOL;
            }
        }

        echo "Perfis configurados com sucesso." . PHP_EOL;

        $ordemservico_status = [
            ['descricao' => 'Agendada'],
            ['descricao' => 'Em execução'],
            ['descricao' => 'Concluída'],
            ['descricao' => 'Não executada'],
            ['descricao' => 'Cancelada'],
            ['descricao' => 'Aguardando aprovação']
        ];

        foreach ($ordemservico_status as $status) {
            if ((new OrdemServicoStatus)->where('descricao', $status['descricao'])->count() == 0) {
                (new OrdemServicoStatus)->create($status);
            }
        }

        echo "Status de OS configurados com sucesso." . PHP_EOL;

        echo "Configurando congregações".PHP_EOL;
        $congregacoes = [
            [
                "Nome" => "Sede",
                "Logradouro" => "Estrada Barão de Aquiraz",
                "Numero" => "2150",
                "Bairro" => "Messejana",
                "CEP" => "60871-684"
            ],
            [
                "Nome" => "Ágape",
                "Logradouro" => "Tv. Margarida Alves",
                "Numero" => "204",
                "Bairro" => "Messejana (Pôr do Sol)",
                "CEP" => "60872-413"
            ],
            [
                "Nome" => "Água Viva I",
                "Logradouro" => "Av. Odilon Guimarães",
                "Numero" => "4463",
                "Bairro" => "Lagoa Redonda",
                "CEP" => "60831-295"
            ],
            [
                "Nome" => "Água Viva II",
                "Logradouro" => "Rua José Rodrigues",
                "Numero" => "321",
                "Bairro" => "Lagoa Redonda",
                "CEP" => "60832-420"
            ],
            [
                "Nome" => "Água Viva III",
                "Logradouro" => "Rua José Alberto Araújo",
                "Numero" => "38",
                "Bairro" => "Lagoa Redonda",
                "CEP" => "60831-272"
            ],
            [
                "Nome" => "Água Viva IV",
                "Logradouro" => "Tv. Precabura",
                "Numero" => "98",
                "Bairro" => "Lagoa Redonda",
                "CEP" => "60831-372"
            ],
            [
                "Nome" => "Alto Alegre",
                "Logradouro" => "Av. Contorno Sul",
                "Numero" => "244",
                "Bairro" => "São Bento (Conj Alto Alegre)",
                "CEP" => "60873-320"
            ],
            [
                "Nome" => "Antioquia",
                "Logradouro" => "Rua Darcy Ribeiro",
                "Numero" => "21",
                "Bairro" => "José de Alencar (Alagadiço Novo)",
                "CEP" => "60830-635"
            ],
            [
                "Nome" => "Atalaia",
                "Logradouro" => "Rua das Papoulas",
                "Numero" => "150",
                "Bairro" => "Coaçu",
                "CEP" => "61760-000"
            ],
            [
                "Nome" => "Belenzinho I",
                "Logradouro" => "Rua Labibe Belém",
                "Numero" => "272",
                "Bairro" => "Jangurussu",
                "CEP" => "60751-735"
            ],
            [
                "Nome" => "Belenzinho II",
                "Logradouro" => "Rua Izaquias",
                "Numero" => "55",
                "Bairro" => "Jangurussu",
                "CEP" => "60110-000"
            ],
            [
                "Nome" => "Berseba",
                "Logradouro" => "Rua Acari",
                "Numero" => "2265",
                "Bairro" => "Alameda das Palmeiras",
                "CEP" => "61880-000"
            ],
            [
                "Nome" => "Betel I",
                "Logradouro" => "Rua Jonas Sampaio",
                "Numero" => "96",
                "Bairro" => "José de Alencar (São Miguel)",
                "CEP" => "60830-465"
            ],
            [
                "Nome" => "Betel II",
                "Logradouro" => "Av. Volta Redonda",
                "Numero" => "513",
                "Bairro" => "Lagoa Redonda (São Miguel)",
                "CEP" => "60830-516"
            ],
            [
                "Nome" => "Betel III",
                "Logradouro" => "Rua Cajazeiras",
                "Numero" => "993",
                "Bairro" => "Lagoa Redonda (São Miguel)",
                "CEP" => "60831-310"
            ],
            [
                "Nome" => "Cidade",
                "Logradouro" => "Nobre Rua Manuel Raventos",
                "Numero" => "24",
                "Bairro" => "Jangurussu",
                "CEP" => "60865-011"
            ],
            [
                "Nome" => "Ebenézer",
                "Logradouro" => "Rua Cecília Meireles",
                "Numero" => "694",
                "Bairro" => "Messejana (São Bernardo)",
                "CEP" => "60841-710"
            ],
            [
                "Nome" => "Efraim",
                "Logradouro" => "Rua Capitão Hermínio",
                "Numero" => "637",
                "Bairro" => "Ancuri (Santa Maria)",
                "CEP" => "60873-095"
            ],
            [
                "Nome" => "Efrata",
                "Logradouro" => "Rua Jovêntino Caetano",
                "Numero" => "326",
                "Bairro" => "Coaçu",
                "CEP" => "60831-735"
            ],
            [
                "Nome" => "Elim",
                "Logradouro" => "Rua José dos Reis",
                "Numero" => "131",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-245"
            ],
            [
                "Nome" => "El Shaday",
                "Logradouro" => "Rua Janete Clair",
                "Numero" => "04",
                "Bairro" => "Jangurussu (Maria Tomásia)",
                "CEP" => "60877-260"
            ],
            [
                "Nome" => "El Shamah",
                "Logradouro" => "Rua 01 Lot Parque Verde",
                "Numero" => "73",
                "Bairro" => "Jangurussu (Parque Verde)",
                "CEP" => "60876-785"
            ],
            [
                "Nome" => "Emaús",
                "Logradouro" => "Rua Adalberto Neto",
                "Numero" => "92",
                "Bairro" => "Jagurussu (Quatro de Julho)",
                "CEP" => "60866-643"
            ],
            [
                "Nome" => "Filadélfia I",
                "Logradouro" => "Rua Da Cajazeira",
                "Numero" => "962",
                "Bairro" => "Jangurussu (Pq. Filomena)",
                "CEP" => "60870-664"
            ],
            [
                "Nome" => "Filadélfia II",
                "Logradouro" => "Rua Francisco Lima da Silva",
                "Numero" => "641",
                "Bairro" => "Jangurussu (Pq. Filomena)",
                "CEP" => "60865-150"
            ],
            [
                "Nome" => "Filadélfia III",
                "Logradouro" => "Rua São João do Jangurussu",
                "Numero" => "2056",
                "Bairro" => "Jangurussu (Pq. Filomena)",
                "CEP" => "60870-750"
            ],
            [
                "Nome" => "Galileia",
                "Logradouro" => "Rua Raimundo Matias",
                "Numero" => "300",
                "Bairro" => "Pedras",
                "CEP" => "61760-001"
            ],
            [
                "Nome" => "Getsêmani",
                "Logradouro" => "Rua Nossa Senhora do Consolo",
                "Numero" => "1078",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-470"
            ],
            [
                "Nome" => "Gileade I",
                "Logradouro" => "Rua Helena Ferreira",
                "Numero" => "985",
                "Bairro" => "Paupina",
                "CEP" => "60875-605"
            ],
            [
                "Nome" => "Gileade II",
                "Logradouro" => "Rua Manoel Ferreira Oriá",
                "Numero" => "21",
                "Bairro" => "Paupina",
                "CEP" => "60872-438"
            ],
            [
                "Nome" => "Gilgal I",
                "Logradouro" => "Rua São José do Multirão",
                "Numero" => "938",
                "Bairro" => "Guajerú",
                "CEP" => "60843-105"
            ],
            [
                "Nome" => "Gilgal II",
                "Logradouro" => "Tv. Wilson Pereira",
                "Numero" => "700",
                "Bairro" => "Guajerú",
                "CEP" => "60843-150"
            ],
            [
                "Nome" => "Gilgal III",
                "Logradouro" => "Rua Caio Facó",
                "Numero" => "1834",
                "Bairro" => "Lagoa Redonda (Guajerú)",
                "CEP" => "60843-120"
            ],
            [
                "Nome" => "Hebrom I",
                "Logradouro" => "Rua Adelaide Paulino",
                "Numero" => "607",
                "Bairro" => "60873-830",
                "CEP" => "Paupina"
            ],
            [
                "Nome" => "Hebrom II",
                "Logradouro" => "Rua 25 de Dezembro",
                "Numero" => "260",
                "Bairro" => "Cs A (Elizabeth II)",
                "CEP" => "60873-740"
            ],
            [
                "Nome" => "Itamaraty",
                "Logradouro" => "Rua Porto Feliz",
                "Numero" => "197",
                "Bairro" => "Paupina - (Pq. Itamaraty)",
                "CEP" => "60873-805"
            ],
            [
                "Nome" => "Jerusalém",
                "Logradouro" => "Rua José Pereira",
                "Numero" => "630",
                "Bairro" => "Paupina",
                "CEP" => "60874-380"
            ],
            [
                "Nome" => "Lírio dos Vales I",
                "Logradouro" => "Rua Edésio Monteiro",
                "Numero" => "1148",
                "Bairro" => "Ancuri (Santa Fé)",
                "CEP" => "60874-110"
            ],
            [
                "Nome" => "Lírio dos Vales II",
                "Logradouro" => "Rua Flôr de Santana",
                "Numero" => "1048",
                "Bairro" => "Ancuri (Santa Fé)",
                "CEP" => "60874-231"
            ],
            [
                "Nome" => "Manancial",
                "Logradouro" => "Rua Maisa",
                "Numero" => "1245",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-250"
            ],
            [
                "Nome" => "Maanaim",
                "Logradouro" => "Rua Olimpio Leite",
                "Numero" => "531",
                "Bairro" => "José de Alencar (Alagadiço Novo)",
                "CEP" => "60830-680"
            ],
            [
                "Nome" => "Maranata",
                "Logradouro" => "Rua Guilherme de Almeida",
                "Numero" => "230",
                "Bairro" => "Ancuri (Santa Maria)",
                "CEP" => "60873-120"
            ],
            [
                "Nome" => "Monte Carmelo",
                "Logradouro" => "Rua Edinalda Santos",
                "Numero" => "65",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60877-135"
            ],
            [
                "Nome" => "Monte das Oliveiras",
                "Logradouro" => "Rua Cantinho Verde",
                "Numero" => "1582",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-450"
            ],
            [
                "Nome" => "Monte Hermom I",
                "Logradouro" => "Rua Jorge Figueiredo",
                "Numero" => "3890",
                "Bairro" => "Ancuri",
                "CEP" => "60874-765"
            ],
            [
                "Nome" => "Monte Hermom II",
                "Logradouro" => "Rua Jardim São Francisco",
                "Numero" => "187",
                "Bairro" => "Ancuri",
                "CEP" => "61880-000"
            ],
            [
                "Nome" => "Monte Hermom III",
                "Logradouro" => "Rua Safira",
                "Numero" => "150",
                "Bairro" => "Pedras",
                "CEP" => "61880-000"
            ],
            [
                "Nome" => "Monte Horebe I",
                "Logradouro" => "Rua Verde 42 (Conj. São João)",
                "Numero" => "347",
                "Bairro" => "Jangurussu",
                "CEP" => "60876-650"
            ],
            [
                "Nome" => "Monte Horebe II",
                "Logradouro" => "Rua E",
                "Numero" => "77",
                "Bairro" => "Conj Patativa do Assaré",
                "CEP" => "60877-225"
            ],
            [
                "Nome" => "Monte Líbano",
                "Logradouro" => "Rua Elisiário Mendes",
                "Numero" => "1049",
                "Bairro" => "Cs B (Pq. Iracema)",
                "CEP" => "60830-250"
            ],
            [
                "Nome" => "Monte Sinai I",
                "Logradouro" => "Rua 321",
                "Numero" => "nº  99",
                "Bairro" => "Jangurussu (São Cristóvão)",
                "CEP" => "60866-410"
            ],
            [
                "Nome" => "Monte Sinai II",
                "Logradouro" => "Av. Contorno Leste",
                "Numero" => "670",
                "Bairro" => "Jangurussu (São Cristovão)",
                "CEP" => "60866-581"
            ],
            [
                "Nome" => "Moriá",
                "Logradouro" => "Tv. José Marques",
                "Numero" => "151",
                "Bairro" => "Ancuri (Santa Fé)",
                "CEP" => "60874-220"
            ],
            [
                "Nome" => "Nova Sião",
                "Logradouro" => "Rua Nova Portuguesa /R. Beija Flor",
                "Numero" => "89",
                "Bairro" => "Jangurussu",
                "CEP" => "60877-501"
            ],
            [
                "Nome" => "Betânia",
                "Logradouro" => "Rua Newton Rebouças",
                "Numero" => "1001 Cs A",
                "Bairro" => "Jangurussu (Betânia)",
                "CEP" => "60870-765"
            ],
            [
                "Nome" => "Parque Pampulha",
                "Logradouro" => "Rua Almira",
                "Numero" => "241 Cs A",
                "Bairro" => "Messejana",
                "CEP" => "60842-280"
            ],
            [
                "Nome" => "Peniel",
                "Logradouro" => "Rua Francisca Martins",
                "Numero" => "S/N",
                "Bairro" => "Pedras (Santo Antônio)",
                "CEP" => "61787-870"
            ],
            [
                "Nome" => "Ramá I",
                "Logradouro" => "Rua 2 Res. Curió",
                "Numero" => "187",
                "Bairro" => "Lagoa Redonda (Curió)",
                "CEP" => "60110-000"
            ],
            [
                "Nome" => "Ramá II",
                "Logradouro" => "Rua Ester de Melo",
                "Numero" => "316",
                "Bairro" => "Lagoa Redonda (Curió)",
                "CEP" => "60831-402"
            ],
            [
                "Nome" => "Refidim",
                "Logradouro" => "Rua Castro Alves",
                "Numero" => "200",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-005"
            ],
            [
                "Nome" => "Reobote",
                "Logradouro" => "Rua Coronel Ernesto Matos",
                "Numero" => "204",
                "Bairro" => "Messejana",
                "CEP" => "60840-350"
            ],
            [
                "Nome" => "Salém",
                "Logradouro" => "Rua Codô",
                "Numero" => "412",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-430"
            ],
            [
                "Nome" => "Salmom",
                "Logradouro" => "Rua Salmão",
                "Numero" => "07",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-160"
            ],
            [
                "Nome" => "Sarom",
                "Logradouro" => "Rua D",
                "Numero" => "88",
                "Bairro" => "Coaçu",
                "CEP" => "60872-130"
            ],
            [
                "Nome" => "Shekinah",
                "Logradouro" => "Rua Angela Diniz",
                "Numero" => "370",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-230"
            ],
            [
                "Nome" => "Siloé",
                "Logradouro" => "Rua Doca Sales",
                "Numero" => "505",
                "Bairro" => "Messejana",
                "CEP" => "60871-380"
            ],
            [
                "Nome" => "Siquém",
                "Logradouro" => "Rua Leirice Porto",
                "Numero" => "452",
                "Bairro" => "Paupina (Esse II)",
                "CEP" => "60874-365"
            ],
            [
                "Nome" => "Valparaiso",
                "Logradouro" => "Av. Valparaíso",
                "Numero" => "1341",
                "Bairro" => "Conjunto Palmeiras",
                "CEP" => "60870-440"
            ],
        ];

        foreach ($congregacoes as $congregacao) {
            if ((new Congregacoes)->where('nome', $congregacao['Nome'])->count() == 0) {
                echo "Criando congregação: " . $congregacao['Nome']. PHP_EOL;
                (new Congregacoes)->create([
                    'nome' => $congregacao['Nome'],
                    'telefone_fixo' => null,
                    'telefone_celular' => null,
                    'data_abertura' => null,
                    'cep' => str_replace('-','',$congregacao['CEP']),
                    'logradouro' => $congregacao['Logradouro'],
                    'numero' => $congregacao['Numero'],
                    'complemento' => null,
                    'ponto_referencia' => null,
                    'bairro' => $congregacao['Bairro'],
                    'cidade' => 'Fortaleza',
                    'uf' => 'CE',
                    'latitude' => null,
                    'longitude' => null,
                    'observacoes' => "Completar informações"
                ]);
                echo "Congregação criada com sucesso: " . $congregacao['Nome'] . PHP_EOL;
            }
        }

        $tipos_manutencao = [
            'Preventiva',
            'Corretiva',
            'Outros'
        ];

        foreach ($tipos_manutencao as $tipo) {
            if ((new TipoManutencao)->where('descricao', $tipo)->count() == 0) {
                echo "Criando tipo de manutenção: " . $tipo . PHP_EOL;
                (new TipoManutencao)->create(['descricao' => $tipo]);
            }
        }
    }
}

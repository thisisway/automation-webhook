<?php

namespace Routes;

use Kernel\Routes;

class Web
{
    use Routes;

    public function __construct()
    {

        //rotas não autenticadas
        $this->setRoute('GET', '/', 'AuthController@index');
        $this->setRoute('GET', '/login', 'AuthController@index');
        $this->setRoute('POST', '/auth/login', 'AuthController@authenticate');
        $this->setRoute('GET', '/auth/logoff', 'AuthController@logoff');
        $this->setRoute('GET', '/ordem-servico/finalizar/:id', 'AceiteOsController@show');
        $this->setRoute('POST', '/aceite/salvar/:id', 'AceiteOsController@update');
        $this->setRoute('GET', '/recuperar-senha', 'RecuperarSenhaController@step1');
        $this->setRoute('POST', '/recuperar-senha/verificar-codigo', 'RecuperarSenhaController@step2');
        $this->setRoute('POST', '/recuperar-senha/alterar-senha', 'RecuperarSenhaController@step3');

        // Nova rota para atualizar o tema
        $this->setRoute('POST', '/update-theme', 'TriggersController@updateTheme');

        //rotas autenticadas
        $this->setMiddlewares(['AuthMiddleware', 'GuardMiddleware'], function () {

            $this->setRoute('POST', '/auth/switch-congregacao', 'AuthController@switchCongregacao');

            $this->setRoute('GET', '/alterar-senha', 'AlterarSenhaController@edit');
            $this->setRoute('POST', '/alterar-senha', 'AlterarSenhaController@update');

            $this->setRoute('GET', '/dashboard', 'DashboardController@index');
            $this->setRoute('GET', '/dashboard/:perfil', 'DashboardController@index');
            
            // Endpoints para gráficos do dashboard
            $this->setRoute('GET', '/dashboard/chart/evolucao-diaria', 'DashboardController@getEvolucaoDiaria');
            $this->setRoute('GET', '/dashboard/chart/status-os', 'DashboardController@getStatusOS');
            $this->setRoute('GET', '/dashboard/chart/tipos-manutencao', 'DashboardController@getTiposManutencao');

            #congregações
            $this->setRoute('GET', '/congregacoes', 'CongregacoesController@index');
            $this->setRoute('POST', '/congregacoes/search', 'CongregacoesController@search');
            $this->setRoute('GET', '/congregacoes/adicionar', 'CongregacoesController@create');
            $this->setRoute('POST', '/congregacoes/adicionar', 'CongregacoesController@store');
            $this->setRoute('POST', '/congregacoes/osinfo', 'CongregacoesController@osinfo');
            $this->setRoute('GET', '/congregacoes/info/:id', 'CongregacoesController@info');
            $this->setRoute('GET', '/congregacoes/editar/:id', 'CongregacoesController@edit');
            $this->setRoute('POST', '/congregacoes/editar/:id', 'CongregacoesController@update');
            $this->setRoute('GET', '/congregacoes/:id', 'CongregacoesController@delete');

            #membros
            $this->setRoute('GET', '/membros', 'MembrosController@index');
            $this->setRoute('GET', '/membros/adicionar', 'MembrosController@create');
            $this->setRoute('POST', '/membros/adicionar', 'MembrosController@store');
            $this->setRoute('GET', '/membros/editar/:id', 'MembrosController@edit');
            $this->setRoute('POST', '/membros/editar/:id', 'MembrosController@update');
            $this->setRoute('GET', '/membros/remover/:id', 'MembrosController@delete');
            $this->setRoute('GET', '/membros/list', 'MembrosController@list');
            $this->setRoute('GET', '/membros/info/:id', 'MembrosController@info');

            #endereços
            $this->setRoute('GET', '/enderecos/adicionar/:id', 'EnderecosController@create');
            $this->setRoute('POST', '/enderecos/adicionar/:id', 'EnderecosController@store');
            $this->setRoute('GET', '/enderecos/editar/:id', 'EnderecosController@edit');
            $this->setRoute('POST', '/enderecos/editar/:id', 'EnderecosController@update');
            $this->setRoute('GET', '/enderecos/remover/:id', 'EnderecosController@delete');
            $this->setRoute('POST', '/enderecos/remover/selecionados', 'EnderecosController@deleteMany');

            #ordens de serviço - CRUD
            $this->setRoute('GET', '/ordem-servico', 'OrdemServicoController@index');
            $this->setRoute('GET', '/ordem-servico/listar', 'OrdemServicoController@list');
            $this->setRoute('GET', '/ordem-servico/adicionar', 'OrdemServicoController@create');
            $this->setRoute('POST', '/ordem-servico/adicionar', 'OrdemServicoController@store');
            $this->setRoute('GET', '/ordem-servico/editar/:id', 'OrdemServicoController@edit');
            $this->setRoute('POST', '/ordem-servico/editar/:id', 'OrdemServicoController@update');
            $this->setRoute('GET', '/ordem-servico/cancelar/:id', 'OrdemServicoController@delete');

            // apenas Administrador e Supervisor
            $this->setRoute('GET', '/ordem-servico/agendar/:id', 'OrdemServicoAgendarController@agendar');
            $this->setRoute('POST', '/ordem-servico/agendar/:id', 'OrdemServicoAgendarController@store');

            // apenas Administrador e Supervisor
            $this->setRoute('POST', '/ordem-servico/cancelar/:id', 'OrdemServicoCancelarController@cancelar');

            #ordens de serviço - visualizar
            $this->setRoute('GET', '/ordem-servico/visualizar/:id', 'OrdemServicoController@visualizar');

            #ordens de serviço - executar
            $this->setRoute('GET', '/ordem-servico/executar/:id', 'OrdemServicoExecutarController@execute');
            $this->setRoute('POST', '/ordem-servico/executar/:id', 'OrdemServicoExecutarController@store');

            #imprimir ordem de serviço
            $this->setRoute('GET', '/ordem-servico/imprimir/:id', 'OrdemServicoImprimirController@index');
            $this->setRoute('GET', '/ordem-servico/imprimir/pdf/:id', 'OrdemServicoImprimirController@imprimirPdf');

            #equipamentos e serviços e produtos
            $this->setRoute('GET', '/equipamentos/buscar', 'ServicosProdutosController@buscarEquipamentos');
            $this->setRoute('GET', '/modelos/buscar', 'ServicosProdutosController@buscarModelos');

            #calendário
            $this->setRoute('GET', '/calendario', 'CalendarioController@index');
            $this->setRoute('GET', '/calendario/compromissos', 'CalendarioController@calendar');

            #configurações gerais
            $this->setRoute('GET', '/configuracoes', 'ConfiguracoesController@index');

            #configurações dos usuários
            $this->setRoute('GET', '/configuracoes/usuarios', 'UsuariosController@index');
            $this->setRoute('GET', '/configuracoes/usuarios/novo', 'UsuariosController@create');
            $this->setRoute('POST', '/configuracoes/usuarios/novo', 'UsuariosController@store');
            $this->setRoute('GET', '/configuracoes/usuarios/editar/:id', 'UsuariosController@edit');
            $this->setRoute('POST', '/configuracoes/usuarios/editar/:id', 'UsuariosController@update');
            $this->setRoute('GET', '/configuracoes/usuarios/remover/:id', 'UsuariosController@delete');
            $this->setRoute('GET', '/configuracoes/usuarios/vincular-congregacoes/:usuario_id', 'UsuariosController@vincular');
            $this->setRoute('POST', '/configuracoes/usuarios/vincular-congregacoes', 'UsuariosController@storeVinculo');
            $this->setRoute('POST', '/configuracoes/usuarios/atualizar-vinculo', 'UsuariosController@atualizarVinculo');
            $this->setRoute('POST', '/configuracoes/usuarios/remover-vinculo', 'UsuariosController@removerVinculo');

            #configurações do perfil
            $this->setRoute('GET', '/configuracoes/meu-perfil', 'PerfilController@edit');
            $this->setRoute('POST', '/configuracoes/meu-perfil', 'PerfilController@update');

            #configuração de permissões
            $this->setRoute('GET', '/configuracoes/permissoes/usuario/:id', 'PermissoesController@edit');
            $this->setRoute('POST', '/configuracoes/permissoes/usuario/:id', 'PermissoesController@update');

            #configurações do estabelecimento
            $this->setRoute('GET', '/configuracoes/ministerio', 'MinisterioController@edit');
            $this->setRoute('POST', '/configuracoes/ministerio', 'MinisterioController@update');

            #acesso negado
            $this->setRoute('GET', '/acesso-negado', 'PaginasErroController@acessoNegado');
        });

        //rotas para criar contas
        $this->setRoute('GET', '/criar-conta-supervisor', 'CriarContaSupervisoresController@create');
        $this->setRoute('POST', '/criar-conta-supervisor', 'CriarContaSupervisoresController@store');

        $this->setRoute('GET', '/criar-conta-operador', 'CriarContaOperadoresController@create');
        $this->setRoute('POST', '/criar-conta-operador', 'CriarContaOperadoresController@store');

        $this->setRoute('GET', '/criar-conta-executor', 'CriarContaExecutoresController@create');
        $this->setRoute('POST', '/criar-conta-executor', 'CriarContaExecutoresController@store');
    }
}

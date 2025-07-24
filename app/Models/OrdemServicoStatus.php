<?php
namespace App\Models;
use Kernel\Model;
class OrdemServicoStatus extends Model
{
    public $AGENDADA = 1;
    public $EM_EXECUCAO = 2;
    public $FINALIZADA = 3;
    public $NAO_EXECUTADA = 4;
    public $CANCELADA = 5;
    public $AGUARDANDO_APROVACAO = 6;

    protected $table = 'ordemservico_status';
    protected $timestamp = false;
    protected $fillable = [
        'descricao'
    ];


    protected $columns = [
        'descricao' => ['type' => 'string', 'length' => 30, 'nullable' => false],
    ];
}

/*
    1 agendada
    2 em execução
    3 concluída
    4 não executada
    5 cancelada
    6 aguardando aprovação
*/
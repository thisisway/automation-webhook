<?php
namespace App\Models;
use Kernel\Model;

class CongregacaoMembros extends Model
{
    public $ADMINISTRADOR = 1;
    public $SUPERVISOR = 2;
    public $SECRETARIO = 3;
    public $OPERADOR = 4;
    public $EXECUTOR = 5;

    protected $table = 'congregacao_membros';
    protected $fillable = [
        'congregacao_id',
        'usuario_id',
        'perfil_id'
    ];

    protected $columns = [
        'congregacao_id' => ['type' => 'integer', 'nullable' => true],
        'usuario_id' => ['type' => 'integer', 'nullable' => false],
        'perfil_id' => ['type' => 'integer', 'nullable' => false],
    ];
}

/*
    Esta tabela faz o relacionamento entre congregações, usuários e perfis,
    permitindo que um usuário tenha diferentes funções em diferentes congregações.
    
    Exemplos de perfis:
    - Supervisor
    - Secretário
    - Operador
    - Executor
    Cada usuário pode estar em várias congregações com perfis distintos.
*/
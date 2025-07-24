<?php
namespace App\Models;
use Kernel\Model;

class PerfilPermissoes extends Model
{
    protected $table = 'perfil_permissoes';
    protected $fillable = [
        'perfil_id',
        'permissao_id'
    ];

    protected $columns = [
        'perfil_id' => ['type' => 'integer', 'nullable' => false],
        'permissao_id' => ['type' => 'integer', 'nullable' => false],
    ];
}

/*
    Esta tabela faz o relacionamento entre perfis e permissões,
    permitindo atribuir várias permissões a cada perfil.
*/
<?php
namespace App\Models;
use Kernel\Model;
class PermissoesUsuario extends Model
{
    protected $table     = 'permissoes_usuario';
    protected $fillable  = [
        'permissao',
        'usuario_id'
    ];

    protected $columns = [
        'permissao' => ['type' => 'string', 'length' => 50, 'nullable' => false],
        'usuario_id' => ['type' => 'integer', 'nullable' => false, 'foreing_key' => 'usuario.id'],
    ];
}
<?php
namespace App\Models;
use Kernel\Model;

class Servicos extends Model
{
    protected $table = 'servicos';
    protected $fillable = [
        'titulo',
        'descricao',
        'categoria',
        'ativo'
    ];

    protected $columns = [
        'titulo' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'descricao' => ['type' => 'text', 'nullable' => false],
        'categoria' => ['type' => 'string', 'length' => 50, 'nullable' => false],
        'ativo' => ['type' => 'boolean', 'nullable' => false, 'default' => 1]
    ];
}

/*
    Campos:
    - titulo: Título/nome do serviço
    - descricao: Descrição detalhada do serviço
    - categoria: Categoria do serviço (ex: Limpeza, Manutenção, etc)
*/
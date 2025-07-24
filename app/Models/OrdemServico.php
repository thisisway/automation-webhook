<?php
namespace App\Models;
use Kernel\Model;

class OrdemServico extends Model
{

    protected $table = 'ordemservico';
    protected $fillable = [
        'congregacao_id',
        'equipamento',
        'modelo_tipo',
        'observacoes',
        'problemas',
        'operador_id',
        'status_id',
        'dtagendamento',
        'dtrealizacao',
        'supervisor_id',
        'tipomanutencao',
        'usuario',
        'ultimo_usuario',
        'motivo_cancelamento'
    ];

    protected $columns = [
        'congregacao_id' => ['type' => 'integer', 'nullable' => false],
        'equipamento' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'modelo_tipo' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'observacoes' => ['type' => 'text', 'nullable' => true],
        'problemas' => ['type' => 'text', 'nullable' => true],
        'operador_id' => ['type' => 'integer', 'nullable' => true],
        'status_id' => ['type' => 'integer', 'nullable' => false],
        'dtagendamento' => ['type' => 'datetime', 'nullable' => true],
        'dtrealizacao' => ['type' => 'datetime', 'nullable' => true],
        'supervisor_id' => ['type' => 'integer', 'nullable' => true],
        'tipomanutencao' => ['type' => 'integer', 'nullable' => true],
        'usuario' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'ultimo_usuario' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'motivo_cancelamento' => ['type' => 'string', 'length' => 255, 'nullable' => true],
    ];

    public function decode()
    {
        $this->dtagendamento = new \DateTime($this->dtagendamento);
        $this->dtrealizacao = ($this->dtrealizacao) ? new \DateTime($this->dtrealizacao) : null;
        $this->created_at = new \DateTime($this->created_at);
        $this->updated_at = new \DateTime($this->updated_at);
        return $this;
    }
}

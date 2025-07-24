<?php
namespace App\Models;

use App\Helpers\ConvertDate;
use App\Helpers\Money;
use Kernel\Model;
class Faturamento extends Model
{
    protected $table     = 'faturamento';
    protected $fillable  = [
        "ordem_servico_id", 
        "vencimento", 
        "valor_total", 
        "observacoes", 
        "usuario"
    ];

    protected $columns = [
        'ordem_servico_id' => ['type' => 'integer', 'nullable' => false],
        'vencimento' => ['type' => 'date', 'nullable' => false],
        'valor_total' => ['type' => 'decimal', 'length' => '15,2', 'nullable' => false],
        'observacoes' => ['type' => 'text', 'nullable' => true],
        'usuario' => ['type' => 'string', 'length' => 100, 'nullable' => false],
    ];

    public function format()
    {
        $this->valor_total = Money::centsToBRL($this->valor_total);
        $this->vencimento = ConvertDate::dateISOToBR($this->vencimento);
        return $this;
    }
}
<?php
namespace App\Models;
use Kernel\Model;

class Cargos extends Model
{
    protected $table = 'cargos';
    protected $fillable = [
        'nome',
        'descricao',
        'nivel_hierarquico',
        'status'
    ];

    protected $columns = [
        'nome' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'descricao' => ['type' => 'string', 'length' => 255, 'nullable' => true],
        'nivel_hierarquico' => ['type' => 'integer', 'nullable' => false],
        'status' => ['type' => 'boolean', 'default' => true]
    ];

    // Constantes para níveis hierárquicos padrão
    public const NIVEL_MEMBRO = 1;
    public const NIVEL_AUXILIAR = 2;
    public const NIVEL_DIACONO = 3;
    public const NIVEL_PRESBITERO = 4;
    public const NIVEL_EVANGELISTA = 5;
    public const NIVEL_PASTOR = 6;

    // Método para inserir cargos padrão
    public function insertDefaultCargos()
    {
        $cargos = [
            ['nome' => 'Membro', 'descricao' => 'Membro da igreja', 'nivel_hierarquico' => self::NIVEL_MEMBRO],
            ['nome' => 'Auxiliar', 'descricao' => 'Auxiliar de ministério', 'nivel_hierarquico' => self::NIVEL_AUXILIAR],
            ['nome' => 'Diácono', 'descricao' => 'Diácono da igreja', 'nivel_hierarquico' => self::NIVEL_DIACONO],
            ['nome' => 'Presbítero', 'descricao' => 'Presbítero da igreja', 'nivel_hierarquico' => self::NIVEL_PRESBITERO],
            ['nome' => 'Evangelista', 'descricao' => 'Evangelista da igreja', 'nivel_hierarquico' => self::NIVEL_EVANGELISTA],
            ['nome' => 'Pastor', 'descricao' => 'Pastor da igreja', 'nivel_hierarquico' => self::NIVEL_PASTOR]
        ];

        foreach ($cargos as $cargo) {
            $this->create($cargo);
        }
    }
} 
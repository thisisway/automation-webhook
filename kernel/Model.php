<?php

namespace Kernel;

use Database\Connection;
use Database\SwitchHub;
use Kernel\Collection;
use Kernel\Garbage;

#[\AllowDynamicProperties]
class Model
{

    protected $connectiondb = '';
    protected $pdo;
    protected $timestamp = true;
    protected $fillable = [];
    protected $columns = [];
    protected $table = '';
    protected $table_id = 'id';
    protected $original_values = [];

    private $select = [];
    private $update = [];
    private $join   = [];
    private $leftJoin   = [];
    private $where  = [];
    private $where_in = [];
    private $where_between = [];
    private $orWhere = [];
    private $whereJson = [];
    private $orWhereJson = [];
    private $operators = ['=', '>', '>=', '<', '<=', 'LIKE', '<>', '!='];
    private $values = [];
    private $valuesToDelete = [];
    private $groupBy = [];
    private $orderBy = [];
    private $limit = false;
    private $offset = false;
    private $is_null = [];
    private $is_not_null = [];
    private $bulkInsert = [];

    public function __construct()
    {
        $this->connectiondb = Env::get('DB_CONNECTION') ?? 'mysql';
        $this->makeConnection();
        foreach ($this->fillable as $fillable) {
            $this->__set($fillable, null);
        }
    }

    private function makeConnection()
    {
        $connections = SwitchHub::Connections();

        if (!$connections[$this->connectiondb]) {
            dd("Connection $this->connectiondb not founded in SwitchHub databases");
        }

        $data = $connections[$this->connectiondb];
        $this->pdo = (new Connection($data))->pdo;
    }

    public function connection($keyConnectionOnSwitchHub)
    {
        $this->connectiondb = $keyConnectionOnSwitchHub;
        return $this;
    }

    public function __get($attribute)
    {
        return $this->{$attribute};
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }


    public function create($data, $debug = false)
    {
        $this->makeConnection();
        $sql = "INSERT INTO " . $this->table . "(";
        foreach (array_keys($data) as $column) {
            if (in_array($column, $this->fillable))
                $sql .= $column . ",";
        }

        if ($this->timestamp)
            $sql .= "created_at,updated_at";

        $sql = rtrim($sql, ",");

        $sql .= ") VALUES (";

        foreach (array_keys($data) as $column) {
            if (in_array($column, $this->fillable))
                $sql .= " :" . $column . ",";
        }

        if ($this->timestamp)
            $sql .= " :created_at, :updated_at";

        $sql = rtrim($sql, ", ");
        $sql .= ")";
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $column => $value) {
            $stmt->bindValue(":" . $column, $value);
        }

        if ($this->timestamp) {
            $datetime = date('Y-m-d H:i:s');
            $stmt->bindValue(":created_at", $datetime);
            $stmt->bindValue(":updated_at", $datetime);
        }

        if ($debug) {
            dd($sql, $stmt->debugDumpParams());
        }

        $stmt->execute();
        $lastId = $this->pdo->lastInsertId();
        $this->pdo->commit();

        //return inserted data

        return $this->find($lastId);
    }

    public function all()
    {
        return $this->fetchAll();
    }


    public function find($id)
    {
        $this->values = [];
        $this->where($this->table_id, $id);
        return $this->fetch();
    }

    public function select()
    {
        $args = func_get_args();
        foreach ($args as $col) {
            $this->select[] = $col;
        }
        return $this;
    }

    public function sum($collumn, $alias)
    {
        if ($this->connectiondb == 'postgres')
            $this->select[] = "SUM(CAST($collumn AS numeric)) as $alias";
        else
            $this->select[] = "SUM($collumn) as $alias";

        return $this;
    }

    public function join($table, $left, $right)
    {
        $this->join[] = compact('table', 'left', 'right');
        return $this;
    }

    public function leftJoin($table, $left, $right)
    {
        $this->leftJoin[] = compact('table', 'left', 'right');
        return $this;
    }

    public function groupBy($columns)
    {
        if (!is_array($columns)) {
            $this->groupBy[] = $columns;
        } else {
            foreach ($columns as $column) {
                $this->groupBy[] = $column;
            }
        }
        return $this;
    }

    public function orderBy($column, $order = 'ASC') 
    {
        $this->orderBy[] = compact('column', 'order');
        return $this;
    }

    public function where($column, $operator = '=', $value = null)
    {
        $value     = $value ?? $operator;
        $operator  = (\in_array($operator, $this->operators)) ? $operator : '=';

        $this->where[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function whereIn($column, array $array)
    {
        $this->where_in[] = compact('column', 'array');
        return $this;
    }

    public function whereBetween($column, array $array)
    {
        $this->where_between[$column] = $array;
        return $this;
    }

    public function whereDate($column, $date)
    {
        $this->where_between[$column] = [$date . " 00:00:00", $date . " 23:59:59"];
        return $this;
    }

    public function whereNotNull($column)
    {
        $this->is_not_null[] = $column;
        return $this;
    }

    public function isNull($column)
    {
        $this->is_null[] = $column;
        return $this;
    }

    public function orWhere($column, $operator = '=', $value = null)
    {
        $value     = $value ?? $operator;
        $operator  = (\in_array($operator, $this->operators)) ? $operator : '=';

        $this->orWhere[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function get()
    {
        return $this->fetchAll();
    }

    public function first()
    {
        return $this->fetch();
    }

    public function last()
    {
        $this->orderBy($this->table_id, 'DESC');
        return $this->fetch();
    }

    public function limit($range)
    {
        $this->limit = $range;
        return $this;
    }

    public function offset($start)
    {
        $this->offset = intval($start);
        return $this;
    }

    public function prepareSql()
    {
        $query = "SELECT ";

        if (count($this->select) > 0) {
            foreach ($this->select as $column) {
                $query .= "$column,";
            }
            $query = rtrim($query, ',');
        } else {
            $query .= $this->table_id . ",";
            foreach ($this->fillable as $column) {
                $query .= "$column,";
            }
            $query = rtrim($query, ',');
            if ($this->timestamp) {
                $query .= ",created_at,updated_at";
            }
        }

        $query .= " FROM " . $this->table;

        if (count($this->join) > 0) {
            foreach ($this->join as $join) {
                $query .= " INNER JOIN " . $join['table'] . " ON " . $join['left'] . " = " . $join['right'];
            }
        }

        if (count($this->leftJoin) > 0) {
            foreach ($this->leftJoin as $leftJoin) {
                $query .= " LEFT JOIN " . $leftJoin['table'] . " ON " . $leftJoin['left'] . " = " . $leftJoin['right'];
            }
        }

        if (count($this->where) > 0) {
            $query .= " WHERE ";
            foreach ($this->where as $qtd => $value) {
                if ($qtd > 0) {
                    $query .= " AND " . $value['column'] . " " . $value['operator'] . " ? ";
                } else {
                    $query .= $value['column'] . " " . $value['operator'] . " ? ";
                }
                $this->values[count($this->values) + 1] = $value['value'];
            }
        }

        if (count($this->orWhere) > 0) {
            foreach ($this->orWhere as $qtd => $value) {
                if ($qtd > 0 || preg_match('/WHERE/', $query))
                    $query .= " OR " . $value['column'] . " " . $value['operator'] . " ? ";
                else
                    $query .= " WHERE " . $value['column'] . " " . $value['operator'] . " ? ";
                $this->values[count($this->values) + 1] = $value['value'];
            }
        }

        if (count($this->where_in) > 0) {
            foreach ($this->where_in as $qtd => $value) {
                $binds = [];
                foreach ($value['array'] as $val) {
                    $binds[] = '?';
                    $this->values[count($this->values) + 1] = $val;
                }

                if ($qtd > 0 || count($this->where) > 0)
                    $query .= " AND " . $value['column'] . " IN (" . implode(',', $binds) . ")";
                elseif ($qtd == 0 and count($this->where) == 0)
                    $query .= " WHERE " . $value['column'] . " IN (" . implode(',', $binds) . ")";
            }
        }

        if (count($this->is_null) > 0) {

            foreach ($this->is_null as $index => $nullableColumn) {
                if ($index > 0 || preg_match('/WHERE/', $query))
                    $query .= " AND $nullableColumn IS NULL ";
                else
                    $query .= " WHERE $nullableColumn IS NULL ";
            }
        }

        if (count($this->is_not_null) > 0) {

            foreach ($this->is_not_null as $index => $nullableColumn) {
                if ($index > 0 || preg_match('/WHERE/', $query))
                    $query .= " AND $nullableColumn IS NOT NULL ";
                else
                    $query .= " WHERE $nullableColumn IS NOT NULL ";
            }
        }

        if (count($this->where_between) > 0) {
            foreach ($this->where_between as $column => $dates) {
                if (preg_match('/WHERE/', $query))
                    $query .= " AND $column BETWEEN ? AND ? ";
                else
                    $query .= " WHERE $column BETWEEN ? AND ? ";

                $this->values[count($this->values) + 1] = $dates[0] . " 00:00:00";
                $this->values[count($this->values) + 1] = $dates[1] . " 23:59:59";
            }
        }

        if (count($this->whereJson) > 0) {
            foreach ($this->whereJson as $data) {
                if (preg_match('/WHERE/', $query))
                    $query .= " AND JSON_SEARCH(" . $data['collumn'] . ", 'one', ?) IS NOT NULL ";
                else
                    $query .= " WHERE JSON_SEARCH(" . $data['collumn'] . ", 'one', ?) IS NOT NULL ";

                $this->values[count($this->values) + 1] = $data['value'];
            }
        }

        if (count($this->orWhereJson) > 0) {
            foreach ($this->orWhereJson as $data) {
                if (preg_match('/WHERE/', $query))
                    $query .= " OR JSON_SEARCH(" . $data['collumn'] . ", 'one', ?) IS NOT NULL ";
                else
                    $query .= " WHERE JSON_SEARCH(" . $data['collumn'] . ", 'one', ?) IS NOT NULL ";

                $this->values[count($this->values) + 1] = $data['value'];
            }
        }

        if (count($this->groupBy) > 0) {
            $query .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (count($this->orderBy) > 0) {
            $query .= " ORDER BY ";
            foreach ($this->orderBy as $key => $value) {
                if ($key > 0)
                    $query .= ', ' . $value['column'] . ' ' . $value['order'];
                else
                    $query .= $value['column'] . ' ' . $value['order'];
            }
        }

        if ($this->limit) {
            $query .= " LIMIT " . $this->limit;
        }

        if ($this->offset) {
            $query .= " OFFSET " . $this->offset;
        }
        return $query;
    }

    public function save($garbage = true)
    {
        if ($garbage)
            Garbage::log($this->table, $this, 'update');

        $this->makeConnection();
        $this->values   = [];
        $query          = "UPDATE " . $this->table . " SET ";

        foreach ($this->original_values as $key => $value) {
            if ($this->$key != $value) {
                $this->update[$key] = $this->$key;
            }
        }

        if (count($this->update) > 0) {

            foreach ($this->update as $collumn => $value) {
                if ($this->isset_value($collumn)) {
                    $query .= " $collumn = :$collumn,";
                    $this->values[":$collumn"] = $value;
                }
            }

            if ($this->timestamp) {
                $query .= " updated_at = :updated_at ";
                $this->values[":updated_at"] = date('Y-m-d H:i:s');
            }

            $query = rtrim($query, ',');

            //set ID
            if (count($this->where) > 0) {
                $query .= " WHERE ";
                foreach ($this->where as $position => $params) {
                    if ($position > 0) {
                        $query .= " AND " . $params['column'] . " " . $params['operator'] . " :" . $params['column'];
                    } else {
                        $query .= $params['column'] . " " . $params['operator'] . " :" . $params['column'];
                    }
                }
            }

            $stmt = $this->pdo->prepare($query);
            foreach ($this->values as $position => $value) {
                $stmt->bindValue($position, $value);
            }
            foreach ($this->where as $params) {
                $stmt->bindValue(":" . $params['column'], $params['value']);
            }
            $stmt->execute();
        }
        return $this;
    }

    public function update($data, $garbage = true)
    {
        //caso um objeto ele verifica se tem alteração, caso não, update normal
        if (property_exists($this, 'id')) {
            $diff = 0;
            foreach ($data as $key => $value) {
                if ($this->column_value($key) != $value) {
                    $this->update[$key] = $value;
                    $diff++;
                }
            }
            if ($diff > 0)
                return $this->save($garbage);
            return $this;
        } else {
            foreach ($data as $key => $value) {
                $this->update[$key] = $value;
            }
            return $this->save($garbage);
        }
    }

    private function fetch()
    {
        $stmt = $this->pdo->prepare($this->prepareSql());
        foreach ($this->values as $position => $value) {
            $stmt->bindValue($position, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($result) {
            foreach ($result as $collumn => $value) {
                $this->{$collumn} = $value;
                $this->original_values[$collumn] = $value;
            }
            unset($this->pdo);
            return $this;
        }

        return false;
    }

    private function fetchAll()
    {
        $stmt = $this->pdo->prepare($this->prepareSql());
        foreach ($this->values as $position => $value) {
            $stmt->bindValue($position, $value);
        }
        $stmt->execute();

        return new Collection($stmt->fetchAll(\PDO::FETCH_OBJ));
    }

    public function count()
    {
        $stmt = $this->pdo->prepare($this->prepareSql());
        foreach ($this->values as $position => $value) {
            $stmt->bindValue($position, $value);
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete($garbage = true)
    {
        if ($garbage)
            Garbage::log($this->table, $this, 'delete');

        $this->makeConnection();
        $query = "DELETE FROM " . $this->table;

        if (count($this->where) > 0) {
            $query .= " WHERE ";
            foreach ($this->where as $qtd => $value) {
                if ($qtd > 0) {
                    $query .= " AND " . $value['column'] . " " . $value['operator'] . " ? ";
                } else {
                    $query .= $value['column'] . " " . $value['operator'] . " ? ";
                }
                $this->valuesToDelete[count($this->valuesToDelete) + 1] = $value['value'];
            }
        }

        if (count($this->orWhere) > 0) {
            foreach ($this->orWhere as $qtd => $value) {
                if ($qtd > 0 || preg_match('/WHERE/', $query))
                    $query .= " OR " . $value['column'] . " " . $value['operator'] . " ? ";
                else
                    $query .= " WHERE " . $value['column'] . " " . $value['operator'] . " ? ";
                $this->valuesToDelete[count($this->valuesToDelete) + 1] = $value['value'];
            }
        }

        if (count($this->where_in) > 0) {
            foreach ($this->where_in as $qtd => $value) {
                $binds = [];
                foreach ($value['array'] as $val) {
                    $binds[] = '?';
                    $this->valuesToDelete[count($this->valuesToDelete) + 1] = $val;
                }

                if ($qtd > 0 || count($this->where) > 0)
                    $query .= " AND " . $value['column'] . " IN (" . implode(',', $binds) . ")";
                elseif ($qtd == 0 and count($this->where) == 0)
                    $query .= " WHERE " . $value['column'] . " IN (" . implode(',', $binds) . ")";
            }
        }

        if (count($this->is_null) > 0) {

            foreach ($this->is_null as $index => $nullableColumn) {
                if ($index > 0 || preg_match('/WHERE/', $query))
                    $query .= " AND $nullableColumn IS NULL ";
                else
                    $query .= " WHERE $nullableColumn IS NULL ";
            }
        }

        if (count($this->is_not_null) > 0) {

            foreach ($this->is_not_null as $index => $nullableColumn) {
                if ($index > 0 || preg_match('/WHERE/', $query))
                    $query .= " AND $nullableColumn IS NOT NULL ";
                else
                    $query .= " WHERE $nullableColumn IS NOT NULL ";
            }
        }

        if (count($this->where_between) > 0) {
            foreach ($this->where_between as $column => $dates) {
                if (preg_match('/WHERE/', $query))
                    $query .= " AND $column BETWEEN ? AND ? ";
                else
                    $query .= " WHERE $column BETWEEN ? AND ? ";

                $this->valuesToDelete[count($this->valuesToDelete) + 1] = $dates[0] . " 00:00:00";
                $this->valuesToDelete[count($this->valuesToDelete) + 1] = $dates[1] . " 23:59:59";
            }
        }

        $stmt = $this->pdo->prepare($query);
        foreach ($this->valuesToDelete as $position => $value) {
            $stmt->bindValue($position, $value);
        }
        $stmt->execute();
    }

    public function pluck($value, $index = null)
    {
        return array_column((array)$this, $value, $index);
    }

    public function isset_value($column)
    {
        if (property_exists($this, $column))
            return true;
        return false;
    }

    public function column_value($column)
    {
        if (property_exists($this, $column))
            return $this->{$column};
        return false;
    }

    public function whereJson($collumn, $value)
    {
        $this->whereJson[] = compact('collumn', 'value');
        return $this;
    }

    public function orWhereJson($collumn, $value)
    {
        $this->orWhereJson[] = compact('collumn', 'value');
        return $this;
    }

    public function buffCreateMany($data)
    {
        $this->bulkInsert[] = $data;
    }

    public function execCreateMany($debug = false)
    {
        try {
            if (count($this->bulkInsert) > 0) {
                $this->makeConnection();
                $sql = "INSERT INTO " . $this->table . "(";

                foreach ($this->fillable as $column) {
                    $sql .= $column . ",";
                }
                $sql = rtrim($sql, ',');
                if ($this->timestamp) {
                    $sql .= ", created_at, updated_at";
                }
                $sql .= ") VALUES ";

                foreach ($this->bulkInsert as $data) {
                    $sql .= "(";
                    foreach ($data as $column => $value) {
                        $sql .= "'$value',";
                    }
                    $sql = rtrim($sql, ',');

                    if ($this->timestamp) {
                        $sql .= ",'" . date('Y-m-d H:i:s') . "','" . date('Y-m-d H:i:s') . "'";
                    }

                    $sql .= "),";
                }

                $sql = rtrim($sql, ',');

                if ($debug)
                    dd($sql);

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
            } else {
                if ($debug)
                    dd('Nenhuma carga para inserção');
            }
        } catch (\PDOException $e) {
            dd($e);
        }

        return true;
    }



    public function createWithId($data, $debug = false)
    {
        $this->makeConnection();
        $sql = "INSERT INTO " . $this->table . "(";
        $sql .= '"' . $this->table_id . '",';
        foreach (array_keys($data) as $column) {
            if (in_array($column, $this->fillable))
                $sql .= '"' . $column . '",';
        }

        if ($this->timestamp)
            $sql .= '"created_at","updated_at"';

        $sql = rtrim($sql, ',');

        $sql .= ") VALUES (:" . $this->table_id . ",";

        foreach (array_keys($data) as $column) {
            if (in_array($column, $this->fillable))
                $sql .= " :" . $column . ",";
        }

        if ($this->timestamp)
            $sql .= " :created_at, :updated_at";

        $sql = rtrim($sql, ", ");
        $sql .= ")";
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $column => $value) {
            $stmt->bindValue(":" . $column, $value);
        }

        if ($this->timestamp) {
            $datetime = date('Y-m-d H:i:s');
            $stmt->bindValue(":created_at", $datetime);
            $stmt->bindValue(":updated_at", $datetime);
        }

        if ($debug) {
            dd($sql, $stmt->debugDumpParams());
        }

        $stmt->execute();
        $this->pdo->commit();

        //return inserted data

        return true;
    }

    public function getTableSchema(){
        return [
            'table' => $this->table,
            'table_id' => $this->table_id,
            'timestamp' => $this->timestamp,
            'fillable'  => $this->fillable,
            'columns' => $this->columns
        ];
    }

    public function getTable()
    {
        return $this->table;
    }
}

<?php
namespace Kernel;
use Kernel\Env;

class Connection
{
    public $pdo;
    
    public function __construct()
    {
        try 
        {
            $dns = Env::get('DB_CONNECTION').':dbname='.Env::get('DB_DATABASE').';host='.Env::get('DB_HOST').';port='.Env::get('DB_PORT').';charset=utf8mb4';
            $this->pdo = new \PDO($dns, Env::get('DB_USERNAME'), Env::get('DB_PASSWORD'));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        }catch(\PDOException $e)
        {
            echo '<pre>';
            var_dump($e);
            echo '</pre>';
        }
    }
}
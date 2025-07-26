<?php
namespace Database;

class Connection
{
    public $pdo;
    
    public function __construct($data)
    {
        try 
        {
            extract($data);

            if($connection === 'sqlite') {
                $dns = "$connection:$database";
                $this->pdo = new \PDO($dns, null, null, $options);
            } else {
                $dns = "$connection:host=$host;port=$port;dbname=$database";
                $this->pdo = new \PDO($dns, $username, $password, $options);
            }
            

        }catch(\PDOException $e)
        {
            echo '<pre>';
            var_dump($e);
            echo '</pre>';
            die();
        }
    }
}
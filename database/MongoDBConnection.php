<?php

namespace Database;

use MongoDB;

class MongoDBConnection
{
    private $client;
    private $database;

    public function __construct()
    {
        $mongoParams = SwitchHub::Connections();
        extract($mongoParams['mongodb']);

        $at = (isset($username) || isset($password)) ? '@' : '';
        $username = (isset($username)) ? $username : '';
        $password = (isset($password)) ? ':' . $password : '';
        $port = (isset($port)) ? ':' . $port : '';

        $uri = "mongodb://{$username}{$password}{$at}{$host}{$port}";

        $this->connect($uri);
        $this->database = $database;
    }

    private function connect($uri)
    {
        try {
            $this->client = new MongoDB\Client($uri);
        } catch (\Exception $error) {
            dd($error->getMessage());
        }
    }

    public function getCollection($collection)
    {
        $mongoDatabase = $this->client->selectDatabase($this->database);
        return $mongoDatabase->{$collection};
    }
}

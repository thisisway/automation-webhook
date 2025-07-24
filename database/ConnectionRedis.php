<?php

namespace Database;

use Predis;

class ConnectionRedis
{
    protected $redis;

    public function __construct()
    {
        try {
            $parameters = SwitchHub::Connections()['redis'];
            $this->redis = new Predis\Client([
                'scheme' => 'tcp',
                'host'   => $parameters['host'],
                'port'   => $parameters['port'],
                'password' => $parameters['password']
            ]);

            if ($parameters['username'] && $parameters['password'])
                $this->redis->auth($parameters['username'], $parameters['password']);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function getConnection()
    {
        return $this->redis;
    }
}

<?php

namespace Kernel;

use Database\ConnectionRedis;

class Redis extends ConnectionRedis
{
    public function set($key, $value, $expires = null)
    {
        $this->redis->set($key, $value);

        if ($expires)
            $this->redis->expire($key, $expires);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function del($key)
    {
        $this->redis->del($key);
    }
}

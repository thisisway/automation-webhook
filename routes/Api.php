<?php

namespace Routes;

use Kernel\Routes;

class Api
{
    use Routes;
    public function __construct()
    {
        $this->group('/api', function () {
            $this->setRoute('POST', '/create', 'Controllers\ApiController@createContainer');
        });
    }
}
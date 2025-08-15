<?php

namespace Routes;

use Kernel\Routes;

class Api
{
    use Routes;
    public function __construct()
    {
        $this->group('/api', function () {
            $this->setRoute('GET', '/list', 'ContainersController@listContainers');
            $this->setRoute('POST', '/create', 'ContainersController@createContainer');
            $this->setRoute('POST', '/set-domain', 'ConfigsController@setDomain');
        });
    }
}
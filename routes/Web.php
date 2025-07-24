<?php

namespace Routes;

use Kernel\Routes;

class Web
{
    use Routes;

    public function __construct()
    {
        $this->setRoute('GET', '/', 'HomeController@index');
    }
}

<?php

namespace App\Controller;
use Kernel\Request;
use Kernel\Redirect;

class LandPageController extends Controller
{
    public function index() {
        $this->enableDefaultLayout = false;
        return $this->view('landpage/index');
    }
}
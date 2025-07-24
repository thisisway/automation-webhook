<?php

namespace App\Controller;
use Kernel\Request;
use Kernel\Redirect;

class HomeController extends Controller
{
    public function index() {
        return $this->json([
            'message' => 'Api is running',
            'status' => '200'
        ]);
    }
}
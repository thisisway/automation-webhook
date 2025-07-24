<?php

namespace App\Controller;
use Kernel\Request;
use Kernel\Redirect;

class ContainersController extends Controller
{
    public function listContainers(Request $request)
    {
        $containers = (new \App\Services\DockerApi\GetContainers())();
        return $this->json($containers);
    }
}
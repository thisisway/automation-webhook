<?php

namespace App\Controller;

use App\Services\DockerApi\GetContainers;
use App\Services\Filesystem\FS;
use App\Services\Templates\MakeTemplate;
use Kernel\Request;

class ContainersController extends Controller
{
    public function listContainers()
    {
        $containers = (new GetContainers())->get();
        return $this->json($containers);
    }

    public function createContainer(Request $request)
    {
        \App\Validations\CreateContainerValidation::rules($request);

        $name = $request->get('name');
        $service = $request->get('service');
        $vcpus = $request->get('vcpus');
        $memory = $request->get('memory');

        $filesystem = new FS();
        $templates = new MakeTemplate($name, $service, $vcpus, $memory);

        $makeClientFolder = $filesystem->makeClientFolder($name);
        $makeTemplate = $templates->createTemplate();

    }
}
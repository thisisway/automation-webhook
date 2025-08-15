<?php

namespace App\Controller;

use App\Services\DockerApi\GetContainers;
use App\Services\DockerApi\RunServices;
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

        $name       = $request->get('name');
        $service    = $request->get('service');
        $vcpus      = $request->get('vcpus');
        $memory     = $request->get('memory');
        $uniqueId   = uniqid();
        $container  = $service.'-'.$name.'-'.$uniqueId;

        $filesystem = new FS();
        $folder     = $filesystem->makeFolder($name);
        $volume     = $filesystem->makeVolume($folder, $container);
        

        $templates = new MakeTemplate($name, $service, $vcpus, $memory, $volume, $container);

        $template     = $templates->createTemplate();
        $ymlFilePath  = $filesystem->createYml($folder, $container, $template);

        // run docker service
        (new RunServices)->run($ymlFilePath);
    }
}
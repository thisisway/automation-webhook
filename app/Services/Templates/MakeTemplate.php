<?php
namespace App\Services\Templates;

use App\Models\Configs;
use App\Services\Filesystem\FS;

class MakeTemplate
{
    private $name;
    private $service;
    private $vcpus;
    private $memory;
    private $volume;
    private $container;

    public function __construct($name, $service, $vcpus, $memory, $volume, $container)
    {
        $this->name = (new FS)->normalizeName($name);
        $this->service = strtolower(trim($service));
        $this->vcpus = (int)$vcpus;
        $this->memory = (int)$memory;
        $this->volume = $volume;
        $this->container = $container;
    }

    public function createTemplate()
    {
        switch($this->service) {
            case 'n8n':
                return $this->createN8nTemplate();
            default:
                throw new \Exception("Service not supported: " . $this->service);
        }      
    }

    private function createN8nTemplate(){
        $template = file_get_contents(dirname(__DIR__, 2) . '/Templates/n8n.yml');
        $n8nHost = $this->container . '.'.  (new Configs)->where('key','domain')->first()->value;

        $template = str_replace('{{CONTAINER_NAME}}', $this->container, $template);
        $template = str_replace('{{N8N_HOST}}', $n8nHost, $template);
        $template = str_replace('{{VOLUME_NAME}}', $this->container, $template);
        $template = str_replace('{{VOLUME_PATH}}', $this->volume, $template);
        $template = str_replace('{{NAME}}', $this->name, $template);
        $template = str_replace('{{CPU_LIMIT}}', $this->vcpus, $template);
        $template = str_replace('{{MEMORY_LIMIT}}', $this->memory.'MB', $template);

        echo $template;
        die();
        return $template;
    }
}
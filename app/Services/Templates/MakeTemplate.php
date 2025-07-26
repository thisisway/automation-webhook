<?php
namespace App\Services\Templates;
class MakeTemplate
{
    protected $name;
    protected $service;
    protected $vcpus;
    protected $memory;

    public function __construct($name, $service, $vcpus, $memory)
    {
        $this->name = strtolower(trim($name));
        $this->service = strtolower(trim($service));
        $this->vcpus = (int)$vcpus;
        $this->memory = (int)$memory;
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
        dd($template);
    }
}
<?php
namespace App\Services\Templates;

use App\Models\Configs;
use App\Services\Filesystem\FS;

class MakeTemplate
{
    protected $name;
    protected $service;
    protected $vcpus;
    protected $memory;
    protected $uniqueId;

    public function __construct($name, $service, $vcpus, $memory, $uniqueId)
    {
        $this->name = (new FS)->normalizeName($name);
        $this->service = strtolower(trim($service));
        $this->vcpus = (int)$vcpus;
        $this->memory = (int)$memory;
        $this->uniqueId = $uniqueId;
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

        $containerName = 'n8n-' . preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ','-',$this->name)) .'-'.$this->uniqueId;
        $n8nHost = $containerName . '.'.  (new Configs)->where('key','domain')->first()->value;
        $volumeName = str_replace('-','_',$containerName).'_data';
        $volumePath = str_replace('-','_',$containerName).'_data';

        $template = str_replace('{{CONTAINER_NAME}}', $containerName, $template);
        $template = str_replace('{{N8N_HOST}}', $n8nHost, $template);
        $template = str_replace('{{VOLUME_NAME}}', $volumeName, $template);
        $template = str_replace('{{VOLUME_PATH}}', $volumePath, $template);
        $template = str_replace('{{NAME}}', $this->name, $template);
        $template = str_replace('{{CPU_LIMIT}}', $this->vcpus, $template);
        $template = str_replace('{{MEMORY_LIMIT}}', $this->memory.'MB', $template);
        return $template;
    }
}
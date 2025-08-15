<?php

namespace App\Services\Filesystem;

class FS
{
    protected $basePath;

    public function __construct()
    {
        $this->basePath = "/etc/automation-webhook";
    }

    public function makeFolder($name)
    {
        try {
            $name = $this->normalizeName($name);

            $clientPath = $this->basePath . '/' . $name;
            if (!is_dir($clientPath)) {
                mkdir($clientPath, 0755, true);
            }
            
            return $clientPath;
        } catch (\Exception $e) {
            echo "Error when create client folder: " . $e->getMessage();
            return;
        }
    }

    public function makeVolume($folder, $container)
    {
        if(!is_dir($folder .'/' . $container . '/data'))
            mkdir($folder .'/' . $container . '/data', 0755, true);
        return $folder . '/' . $container . '/data';
    }

    public function createYml($folder, $container, $content = '')
    {
        $path = $folder . '/' . $container . '/docker-compose.yml';
        file_put_contents($path, $content);
        return $path;
    }

    public function normalizeName($name)
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name); // Remove múltiplos hífens
        $name = trim($name, '-'); // Remove hífens no início e no fim

        return $name;
    }
}

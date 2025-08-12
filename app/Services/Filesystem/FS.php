<?php

namespace App\Services\Filesystem;

class FS
{
    protected $basePath;

    public function __construct()
    {
        $this->basePath = "/etc/automation-webhook";
    }

    public function makeClientFolder($name)
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

    public function createYmlService($service, $name, $clientFolder, $uniqueId, $content = '')
    {
        $name = $this->normalizeName($name);
        $path = $this->basePath . $clientFolder . '/' . $service . '-' . $name . '-' . $uniqueId;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $ymlFile = $path . '/docker-compose.yml';
        file_put_contents($ymlFile, $content);
        return $path;
    }

    private function normalizeName($name)
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name); // Remove múltiplos hífens
        $name = trim($name, '-'); // Remove hífens no início e no fim

        return $name;
    }
}

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
            $name = strtolower(trim($name));
            $name = preg_replace('/[^a-z0-9-]/', '-', $name);
            $name = preg_replace('/-+/', '-', $name); // Remove múltiplos hífens
            $name = trim($name, '-'); // Remove hífens no início e no fim


            $clientPath = $this->basePath . '/' . $name;
            if (!file_exists($clientPath)) {
                mkdir($clientPath, 0755, true);
            }

            return $clientPath;
        } catch (\Exception $e) {
            echo "Error when create client folder: " . $e->getMessage();
            return;
        }
    }

    public function createYmlService($path, $content = '')
    {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        file_put_contents($fullPath, $content);
    }
}

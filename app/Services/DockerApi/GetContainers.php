<?php
namespace App\Services\DockerApi;
class GetContainers
{
    public function get()
    {
        exec('docker ps --format "{{.ID}}: {{.Names}}: {{.Image}}: {{.Status}}"', $output, $return_var);
        if ($return_var !== 0) {
            throw new \Exception('Failed to retrieve Docker containers: ' . implode("\n", $output));
        }
        $containers = [];
        foreach ($output as $line) {
            list($id, $name, $image, $status) = explode(': ', $line, 4);
            $containers[] = [
                'id' => $id,
                'name' => $name,
                'image' => $image,
                'status' => $status,
            ];
        }
        return $containers;
    }
}
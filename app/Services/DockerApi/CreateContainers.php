<?php
namespace App\Services\DockerApi;
class CreateContainers
{
    public function create($name, $image, $command = null, $env = [], $ports = [], $volumes = [], $labels = [])
    {
        $cmd = "docker run -d --name $name";
        
        if ($command) {
            $cmd .= " $command";
        }
        
        foreach ($env as $key => $value) {
            $cmd .= " -e \"$key=$value\"";
        }
        
        foreach ($ports as $hostPort => $containerPort) {
            $cmd .= " -p \"$hostPort:$containerPort\"";
        }
        
        foreach ($volumes as $hostPath => $containerPath) {
            $cmd .= " -v \"$hostPath:$containerPath\"";
        }
        
        foreach ($labels as $key => $value) {
            $cmd .= " --label \"$key=$value\"";
        }
        
        $cmd .= " $image";
        
        exec($cmd, $output, $return_var);
        
        if ($return_var !== 0) {
            throw new \Exception('Failed to create container: ' . implode("\n", $output));
        }
        
        return true;
    }
}
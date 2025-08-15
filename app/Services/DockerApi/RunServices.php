<?php
namespace App\Services\DockerApi;
class RunServices {
    public function run($ymlFilePath) {
        // cd in yml file path and run docker-compose up -d
        exec("docker stack deploy -c $ymlFilePath/docker-compose.yml", $output, $return_var);

        // Wait for the container to be ready
        sleep(5);
        // Check if the container is running
        $containers = (new GetContainers)->get();
        dd($containers);

    }
}
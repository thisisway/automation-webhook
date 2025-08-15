<?php
namespace App\Services\DockerApi;
class RunServices {
    public function run($ymlFilePath) {
        echo "cd $ymlFilePath && docker compose up -d";

        

        // Execute the command and capture output
        $command = "cd $ymlFilePath && docker compose up -d";
        $output = [];
        $return_var = 0;

        echo "\nExecuting command: $command\n";
        exec($command, $output, $return_var);

        // Display the command output
        echo "\nOutput:\n";
        echo implode("\n", $output);

        // Check if the command was successful
        if ($return_var !== 0) {
            echo "\nError: Command failed with exit code $return_var\n";
        } else {
            echo "\nCommand executed successfully\n";
        }

        // cd in yml file path and run docker-compose up -d
        // exec("cd $ymlFilePath && docker compose up -d", $output, $return_var);

        // Wait for the container to be ready
        // sleep(5);
        // Check if the container is running
        // $containers = (new GetContainers)->get();
        //dd($containers);
    }
}
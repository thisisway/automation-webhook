<?php

namespace App\Console;

use App\Models\Configs;
class Commands
{
    public function seed()
    {
        echo "setup configurations\n".PHP_EOL;
        $configs = [
            [
                'name' => 'domain',
                'value' => 'myapp.local',
            ]
        ];
        foreach ($configs as $config) {
            echo "Creating config: {$config['name']}\n".PHP_EOL;
            (new Configs())->create([
                'key' => $config['name'],
                'value' => $config['value'],
            ]);
        }
    }
}

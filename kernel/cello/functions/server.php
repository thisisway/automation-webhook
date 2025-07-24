<?php

if($command == 'server' || $command == 'serve')
    exec('php -S localhost:8000 -t .\public | php -S '.getHostByName(getHostName()).':8000 -t .\public');

if($command == "watch")
    exec('browser-sync start --proxy "localhost:8000" --files "/home/jurandirjunior/reobote/sistema-ieademe/resources/views/**/*.php"');
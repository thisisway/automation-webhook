<?php
if ($command == 'console') {

    $execFunction = $argv[2] ?? null;

    if ($execFunction) {
        try {
            $commandClass = new \App\Console\Commands;
            if (method_exists($commandClass, $execFunction))
                call_user_func_array([$commandClass, $execFunction], $argv);
            else
                echo "Function not found: " . $execFunction . PHP_EOL;

            die();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    } else {
        echo PHP_EOL;
        echo 'Incomplete command, execute command like this: php cello console FunctionName' . PHP_EOL .
            '*\'FunctionName\' is defined function in App/Console/Commands file.' . PHP_EOL;
        echo PHP_EOL;
        die();
    }
}

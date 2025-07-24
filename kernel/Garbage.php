<?php
namespace Kernel;

use App\Models\Garbage as ModelGarbage;
use Kernel\Session as KernelSession;

class Garbage{
    public static function log($table_name, $data, $action)
    {
        $username = KernelSession::get('username');
        $data = json_encode($data);
        (new ModelGarbage())->create(compact('username','table_name','data','action'));
    }
}
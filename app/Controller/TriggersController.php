<?php

namespace App\Controller;

use App\Controller\Controller;
use Kernel\Redis;
use Kernel\Request;
use Kernel\Session;

class TriggersController extends Controller
{
    public function updateTheme(Request $request)
    {
        $theme = $request->get('theme');
        $user_id = Session::get('user_id');

        if (!$theme) {
            return $this->json([
                'success' => false,
                'message' => 'Tema não informado'
            ]);
        }

        $redis = new Redis();
        $redis->set('theme_user_' . $user_id, $theme);

        return $this->json([
            'success' => true,
            'message' => 'Tema atualizado com sucesso'
        ]);
    }
}
<?php
namespace App\Models;
use Kernel\Model;

class Usuarios extends Model
{
    protected $table = 'usuarios';
    protected $fillable = [
        'nome',
        'email',
        'username',
        'password',
        'perfil_id',
        'telefone',
        'status',
        'ultimo_login',
        'foto_perfil'
    ];

    protected $columns = [
        'nome' => ['type' => 'string', 'length' => 100, 'nullable' => false],
        'email' => ['type' => 'string', 'length' => 150, 'nullable' => false, 'unique' => true],
        'username' => ['type' => 'string', 'length' => 50, 'nullable' => false, 'unique' => true],
        'password' => ['type' => 'string', 'length' => 255, 'nullable' => false],
        'perfil_id' => ['type' => 'integer', 'nullable' => false],
        'telefone' => ['type' => 'string', 'length' => 15, 'nullable' => true],
        'status' => ['type' => 'string', 'length' => 20, 'nullable' => false, 'default' => 'ativo'],
        'ultimo_login' => ['type' => 'datetime', 'nullable' => true],
        'foto_perfil' => ['type' => 'string', 'length' => 255, 'nullable' => true],
    ];


    public function attempt($email, $password)
    {
        $result = $this->where('email',$email)->first();
        if($result)
        {
            $result->perfil = (new Perfil)->where('id', $result->perfil_id)->first()->nome;
            $this->ultimo_login = date('Y-m-d H:i:s');
            $this->save();
            
            if($result->password == password_verify($password, $result->password)){
                return [true, '', $result];
            }
        }else{
            password_verify('20ujasd908435kda', '$2y$10$zMxhN4Ds3hC7gCh9GLMaveaykaLmzlaXPU.kapl1kcT5GV0Iq23ke');
        }

        return [false, "Credenciais inválidas", false];

    }
}
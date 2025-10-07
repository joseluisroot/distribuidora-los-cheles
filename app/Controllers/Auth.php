<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function doLogin()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->select('users.*, roles.name as role')
            ->join('roles','roles.id = users.role_id')
            ->where('email',$email)->first();

        if (!$user || !password_verify($password, $user['password']) || !$user['is_active']) {
            return redirect()->back()->withInput()->with('error','Credenciales inválidas.');
        }

        session()->set('user', [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'], // admin | cliente
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('message','Sesión cerrada.');
    }

    public function forgot()
    {
        return view('auth/forgot');
    }

    public function sendReset()
    {
        $email = $this->request->getPost('email');
        $user  = (new UserModel())->findByEmail($email);

        // Respondemos igual para no filtrar existencia
        if (!$user) {
            return redirect()->back()->with('message','Si el correo existe, enviaremos instrucciones.');
        }

        $tokenModel = new PasswordResetTokenModel();
        $token = $tokenModel->createToken($user['id']);

        $resetUrl = base_url('reset/'.$token);

        $emailService = service('email');
        $emailService->setTo($email);
        $emailService->setSubject('Recupera tu contraseña');
        $emailService->setMessage("Hola {$user['name']},\n\nUsa este enlace para restablecer tu contraseña:\n{$resetUrl}\n\nEl enlace expira en 1 hora.");
        $emailService->send();

        return redirect()->back()->with('message','Si el correo existe, enviaremos instrucciones.');
    }

    public function reset($token)
    {
        return view('auth/reset', ['token'=>$token]);
    }

    public function doReset()
    {
        $token = $this->request->getPost('token');
        $pass1 = $this->request->getPost('password');
        $pass2 = $this->request->getPost('password_confirm');

        if ($pass1 !== $pass2 || strlen($pass1) < 8) {
            return redirect()->back()->with('error','Contraseña inválida o no coincide.')->withInput();
        }

        $tokenModel = new PasswordResetTokenModel();
        $row = $tokenModel->validateToken($token);
        if (!$row) {
            return redirect()->to('/login')->with('error','Token inválido o vencido.');
        }

        $userModel = new UserModel();
        $userModel->update($row['user_id'], ['password'=>password_hash($pass1, PASSWORD_DEFAULT)]);
        $tokenModel->update($row['id'], ['used_at'=>date('Y-m-d H:i:s')]);

        return redirect()->to('/login')->with('message','Contraseña actualizada. Inicia sesión.');
    }
}

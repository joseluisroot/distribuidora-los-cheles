<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    // GET /login
    public function login()
    {
        return view('auth/login', [
            'title' => 'Ingresar',
            'showNavbar' => false,
            'showFooter' => false,
        ]);
    }

    // POST /login
    public function doLogin()
    {
        $validation = service('validation');
        $throttler  = service('throttler');

        $email = (string) $this->request->getPost('email');
        $ip    = (string) $this->request->getIPAddress();

        // 🔒 Clave segura para cache/throttle (sin caracteres reservados)
        $rawKey = 'login|' . strtolower($email) . '|' . $ip; // puede tener :, etc.
        $key    = 't_' . md5($rawKey); // o sha1/sha256 en hex

        // 5 intentos por minuto
        if ($throttler->check($key, 5, MINUTE) === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Demasiados intentos. Intenta en 1 minuto.');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los datos e intenta de nuevo.');
        }

        $password  = (string) $this->request->getPost('password');
        $userModel = new UserModel();

        // Trae rol por join como ya lo hacías
        $user = $userModel->select('users.*, roles.name as role')
            ->join('roles','roles.id = users.role_id','left')
            ->where('users.email',$email)
            ->first();

        if (! $user || ! isset($user['password']) || ! password_verify($password, $user['password']) || empty($user['is_active'])) {
            // Respuesta uniforme
            return redirect()->back()->withInput()->with('error', 'Credenciales inválidas.');
        }

        // ✅ Seguridad: regenerar ID de sesión
        session()->regenerate();

        // Normaliza el rol
        $role = strtolower($user['role'] ?? 'cliente');
        $roleMap = [
            'administrador' => 'admin',
            'gerente'       => 'manager',
            'cliente'       => 'cliente',
        ];
        $role = $roleMap[$role] ?? $role;

        // Guardamos datos de sesión
        session()->set([
            'isLoggedIn' => true,
            'role'       => $role,
            'user_id'    => (int) $user['id'],
            'user'       => [
                'id'    => (int) $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $role,
            ],
        ]);

        // 🔁 Redirección por rol
        switch ($role) {
            case 'admin':
            case 'manager':
                $redirectUrl = site_url('admin/dashboard');
                break;
            case 'cliente':
            default:
                $redirectUrl = site_url('dashboard');
                break;
        }

        return redirect()->to($redirectUrl);
    }

    // GET /logout
    public function logout()
    {
        session()->remove('user');
        session()->destroy();
        return redirect()->to('/login')->with('message','Sesión cerrada.');
    }

    // GET /forgot
    public function forgot()
    {
        return view('auth/forgot', [
            'title' => 'Recuperar contraseña',
            'showNavbar' => false,
            'showFooter' => false,
        ]);
    }

    // POST /forgot
    public function sendReset()
    {
        $validation = service('validation');
        $rules = ['email' => 'required|valid_email'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error','Ingresa un correo válido.');
        }

        $email = (string) $this->request->getPost('email');
        $user  = (new UserModel())->findByEmail($email);

        // Siempre responder genérico
        if (!$user) {
            return redirect()->back()->with('message','Si el correo existe, enviaremos instrucciones.');
        }

        $tokenModel = new PasswordResetTokenModel();
        $token      = $tokenModel->createToken((int) $user['id']); // retorna token en texto plano

        $resetUrl = base_url('reset/'.$token);

        try {
            $emailService = service('email');
            $emailService->setTo($email);
            $emailService->setSubject('Recupera tu contraseña - Distribuidora Los Cheles');
            $emailService->setMessage(
                view('emails/reset_link', [
                    'name'    => $user['name'],
                    'resetUrl'=> $resetUrl,
                ])
            );
            $emailService->send();
        } catch (\Throwable $e) {
            // No reveles fallo; loguea para ti
            log_message('error', 'Email reset error: {err}', ['err' => $e->getMessage()]);
        }

        return redirect()->back()->with('message','Si el correo existe, enviaremos instrucciones.');
    }

    // GET /reset/{token}
    public function reset(string $token)
    {
        return view('auth/reset', [
            'title'      => 'Nueva contraseña',
            'showNavbar' => false,
            'showFooter' => false,
            'token'      => $token,
        ]);
    }

    // POST /reset
    public function doReset()
    {
        $validation = service('validation');
        $rules = [
            'token'             => 'required',
            'password'          => 'required|min_length[8]',
            'password_confirm'  => 'required|matches[password]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error','Contraseña inválida o no coincide.');
        }

        $token = (string) $this->request->getPost('token');
        $pass1 = (string) $this->request->getPost('password');

        $tokenModel = new PasswordResetTokenModel();
        $row        = $tokenModel->validateToken($token);
        if (! $row) {
            return redirect()->to('/login')->with('error','Token inválido o vencido.');
        }

        $userModel = new UserModel();
        $userModel->update((int) $row['user_id'], [
            'password'   => password_hash($pass1, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // marcar token usado
        $tokenModel->markUsed((int) $row['id']);

        return redirect()->to('/login')->with('message','Contraseña actualizada. Inicia sesión.');
    }
}

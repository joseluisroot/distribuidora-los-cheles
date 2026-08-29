<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function register()
    {
        return view('auth/register', ['title' => 'Crear cuenta', 'showNavbar' => false, 'showFooter' => false]);
    }

    public function doRegister()
    {
        if (!service('throttler')->check('register_' . hash('sha256', $this->request->getIPAddress()), 5, MINUTE)) {
            return redirect()->back()->with('error', 'Intenta de nuevo más tarde.');
        }
        if (!$this->validateData($this->request->getPost(), [
            'name' => 'required|max_length[100]', 'email' => 'required|valid_email|max_length[150]',
            'password' => 'required|min_length[12]|max_length[200]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return redirect()->back()->with('error', 'Revisa los datos; la contraseña debe tener al menos 12 caracteres.');
        }
        $role = db_connect()->table('roles')->where('name', 'cliente')->get()->getRowArray();
        if (!$role) {
            return $this->response->setStatusCode(503)->setBody('Registro todavía no configurado.');
        }
        try {
            (new UserModel())->insert([
                'role_id' => (int) $role['id'], 'name' => trim((string) $this->request->getPost('name')),
                'email' => strtolower(trim((string) $this->request->getPost('email'))),
                'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'is_active' => 1,
            ]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('login'))->with('message', 'Si ya tienes cuenta, ingresa o recupera tu contraseña.');
        }
        return redirect()->to(site_url('login'))->with('message', 'Cuenta creada. Puedes iniciar sesión.');
    }

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
            return redirect()->back()
                ->with('error', 'Demasiados intentos. Intenta en 1 minuto.');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->with('error', 'Revisa los datos e intenta de nuevo.');
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
            return redirect()->back()->with('error', 'Credenciales inválidas.');
        }

        // ✅ Seguridad: regenerar ID de sesión
        session()->regenerate(true);

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
                'auth_version' => (int) $user['auth_version'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $role,
            ],
        ]);

        // 🔁 Redirección por rol
        switch ($role) {
            case 'admin':
            case 'manager':
                $redirectUrl = (new \App\Services\AccessService())->can((int) $user['id'], 'dashboard.view') && (new \App\Services\AccessService())->can((int) $user['id'], 'orders.view') ? site_url('admin/dashboard') : site_url('dashboard');
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
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->with('error','Ingresa un correo válido.');
        }

        if (!service('throttler')->check('reset_' . hash('sha256', $this->request->getIPAddress()), 5, MINUTE)) {
            return redirect()->back()->with('message', 'Si el correo existe, enviaremos instrucciones.');
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
            if (!$emailService->send()) {
                log_message('error', 'No se pudo enviar el correo de recuperación.');
            }
        } catch (\Throwable $e) {
            // No reveles fallo; loguea para ti
            log_message('error', 'No se pudo enviar el correo de recuperación.');
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
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->with('error','Contraseña inválida o no coincide.');
        }

        $token = (string) $this->request->getPost('token');
        $pass1 = (string) $this->request->getPost('password');

        $tokenModel = new PasswordResetTokenModel();
        if (!$tokenModel->resetPassword($token, $pass1)) {
            return redirect()->to('/login')->with('error', 'Token inválido o vencido.');
        }
        session()->destroy();

        return redirect()->to('/login')->with('message','Contraseña actualizada. Inicia sesión.');
    }
}

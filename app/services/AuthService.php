<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function activate(string $username, string $pin): array
    {
        $user = $this->users->findByUsername($username);
        if (!$user) {
            return ['ok' => false, 'message' => 'Usuario no encontrado.'];
        }
        if ((int) $user['activo'] !== 1) {
            return ['ok' => false, 'message' => 'Usuario deshabilitado.'];
        }
        if ((int) $user['requiere_activacion'] !== 1) {
            return ['ok' => false, 'message' => 'Este usuario ya fue activado.'];
        }

        $pinHash = password_hash($pin, PASSWORD_DEFAULT);
        $this->users->activatePin((int) $user['id'], $pinHash);

        return ['ok' => true, 'message' => 'PIN generado correctamente. Ya puedes iniciar sesion.'];
    }

    public function login(string $username, string $pin): array
    {
        $user = $this->users->findByUsername($username);
        if (!$user) {
            return ['ok' => false, 'message' => 'Credenciales invalidas.'];
        }
        if ((int) $user['activo'] !== 1) {
            return ['ok' => false, 'message' => 'Usuario deshabilitado.'];
        }
        if ((int) $user['requiere_activacion'] === 1) {
            return ['ok' => false, 'message' => 'Primero debes activar tu PIN.'];
        }
        if (!password_verify($pin, (string) $user['pin_hash'])) {
            return ['ok' => false, 'message' => 'Credenciales invalidas.'];
        }

        session_regenerate_id(true);
        session_set('user_id', (int) $user['id']);
        $this->users->touchLastLogin((int) $user['id']);

        return ['ok' => true, 'user' => $user];
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

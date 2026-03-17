<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Helpers\Validator;
use App\Repositories\UserRepository;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;
    private UserRepository $users;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->users = new UserRepository();
    }

    public function loginForm(Request $request): void
    {
        $this->view->render('auth/login', [
            'title' => 'Ingresar',
            'users' => $this->users->activeForLogin(),
        ]);
    }

    public function login(Request $request): void
    {
        $validator = new Validator();
        $validator
            ->required('username', $request->input('username'), 'Usuario requerido.')
            ->required('pin', $request->input('pin'), 'PIN requerido.');

        if (!$validator->passes()) {
            flash('error', implode(' ', array_map(fn ($err) => implode(' ', $err), $validator->errors())));
            $this->response->redirect('/login');
        }

        $result = $this->authService->login((string) $request->input('username'), (string) $request->input('pin'));
        if (!$result['ok']) {
            flash('error', $result['message']);
            $this->response->redirect('/login');
        }

        flash('success', 'Bienvenido, ' . $result['user']['nombre']);
        $this->response->redirect('/dashboard');
    }

    public function activateForm(Request $request): void
    {
        $this->view->render('auth/activate', [
            'title' => 'Activacion inicial',
            'users' => $this->users->pendingActivation(),
        ]);
    }

    public function activate(Request $request): void
    {
        $pin = (string) $request->input('pin');
        $confirm = (string) $request->input('pin_confirm');

        $validator = new Validator();
        $validator
            ->required('username', $request->input('username'), 'Usuario requerido.')
            ->required('pin', $pin, 'PIN requerido.')
            ->minLength('pin', $pin, 4, 'El PIN debe tener al menos 4 caracteres.');

        if ($pin !== $confirm) {
            flash('error', 'Los PIN no coinciden.');
            $this->response->redirect('/activar');
        }

        if (!$validator->passes()) {
            flash('error', implode(' ', array_map(fn ($err) => implode(' ', $err), $validator->errors())));
            $this->response->redirect('/activar');
        }

        $result = $this->authService->activate((string) $request->input('username'), $pin);
        flash($result['ok'] ? 'success' : 'error', $result['message']);
        $this->response->redirect($result['ok'] ? '/login' : '/activar');
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        $this->response->redirect('/login');
    }
}

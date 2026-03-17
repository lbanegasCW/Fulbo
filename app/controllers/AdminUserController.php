<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Helpers\Validator;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

class AdminUserController extends BaseController
{
    private UserRepository $users;
    private RoleRepository $roles;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserRepository();
        $this->roles = new RoleRepository();
    }

    public function index(Request $request): void
    {
        $editId = $request->input('edit_id') ? (int) $request->input('edit_id') : null;
        $showForm = $request->input('form') === '1';

        $editUser = null;
        if ($editId) {
            $editUser = $this->users->findById($editId);
            $showForm = $editUser !== null;
        }

        $this->view->render('admin/users', [
            'title' => 'Gestion de usuarios',
            'users' => $this->users->all(null),
            'roles' => $this->roles->all(),
            'editUser' => $editUser,
            'showForm' => $showForm,
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['nombre', 'username', 'rol_id']);
        $validator = new Validator();
        $validator
            ->required('nombre', $data['nombre'], 'Nombre requerido.')
            ->required('username', $data['username'], 'Username requerido.')
            ->required('rol_id', $data['rol_id'], 'Rol requerido.');

        if (!$validator->passes()) {
            flash('error', 'Completa todos los campos obligatorios.');
            $this->response->redirect('/admin/usuarios');
        }

        $this->users->create([
            'nombre' => trim((string) $data['nombre']),
            'username' => strtolower(trim((string) $data['username'])),
            'rol_id' => (int) $data['rol_id'],
            'activo' => 1,
            'requiere_activacion' => 1,
        ]);
        flash('success', 'Usuario creado en estado pendiente de activacion.');
        $this->response->redirect('/admin/usuarios');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->input('id');
        $data = [
            'nombre' => trim((string) $request->input('nombre')),
            'username' => strtolower(trim((string) $request->input('username'))),
            'rol_id' => (int) $request->input('rol_id'),
            'activo' => $request->input('activo') ? 1 : 0,
            'requiere_activacion' => $request->input('requiere_activacion') ? 1 : 0,
        ];
        $this->users->update($id, $data);
        flash('success', 'Usuario actualizado.');
        $this->response->redirect('/admin/usuarios');
    }
}

<?php
$isEditing = !empty($editUser);
$formVisible = !empty($showForm) || $isEditing;
?>

<?php if ($formVisible): ?>
<section class="card admin-card">
    <div class="card-head">
        <h2><?= $isEditing ? 'Editar usuario' : 'Nuevo usuario' ?></h2>
        <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-outline btn-sm">Cerrar</a>
    </div>

    <form method="post" action="<?= e(url($isEditing ? '/admin/usuarios/editar' : '/admin/usuarios/crear')) ?>" class="grid-form admin-form-grid">
        <?= csrf_field() ?>
        <?php if ($isEditing): ?>
            <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>">
        <?php endif; ?>

        <label class="admin-label">
            Nombre
            <input name="nombre" required placeholder="Nombre visible" value="<?= e($editUser['nombre'] ?? '') ?>">
        </label>

        <label class="admin-label">
            Username
            <input name="username" required placeholder="username unico" value="<?= e($editUser['username'] ?? '') ?>">
        </label>

        <label class="admin-label">
            Rol
            <select name="rol_id" required>
                <option value="">Rol</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int) $role['id'] ?>" <?= $isEditing && (int) $role['id'] === (int) $editUser['rol_id'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <?php if ($isEditing): ?>
            <label class="admin-label inline-check">
                <input type="checkbox" name="activo" <?= (int) $editUser['activo'] === 1 ? 'checked' : '' ?>>
                <span>Activo</span>
            </label>

            <label class="admin-label inline-check">
                <input type="checkbox" name="requiere_activacion" <?= (int) $editUser['requiere_activacion'] === 1 ? 'checked' : '' ?>>
                <span>Requiere activacion</span>
            </label>
        <?php endif; ?>

        <div class="admin-actions-row">
            <button class="btn btn-primary btn-sm btn-block" type="submit"><?= $isEditing ? 'Guardar cambios' : 'Crear usuario' ?></button>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card admin-card">
    <div class="card-head">
        <h1>Usuarios</h1>
        <a href="<?= e(url('/admin/usuarios?form=1')) ?>" class="btn btn-primary btn-sm">Nuevo</a>
    </div>

    <div class="table-wrap">
        <table class="users-table">
            <thead><tr><th>Nombre</th><th>Activo</th><th>Activacion</th><th>Accion</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['nombre']) ?></td>
                    <td><?= (int) $u['activo'] === 1 ? 'Si' : 'No' ?></td>
                    <td><?= (int) $u['requiere_activacion'] === 1 ? 'Pendiente' : 'Lista' ?></td>
                    <td>
                        <a class="icon-link" title="Editar" href="<?= e(url('/admin/usuarios?edit_id=' . (int) $u['id'])) ?>">✎</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="auth-card auth-card-simple">
    <h1>Ingresar</h1>
    <p class="auth-subtitle">Elegi tu usuario y entra con tu PIN para seguir el torneo. Si es tu primer ingreso, ve a <a href="<?= e(url('/activar')) ?>">Activar</a>.</p>

    <form method="post" action="<?= e(url('/login')) ?>" class="stack auth-form-lg" autocomplete="off">
        <?= csrf_field() ?>
        <select name="username" required>
            <option value="">Usuario</option>
            <?php foreach (($users ?? []) as $item): ?>
                <option value="<?= e($item['username']) ?>"><?= e($item['nombre']) ?> (@<?= e($item['username']) ?>)</option>
            <?php endforeach; ?>
        </select>

        <input type="password" name="pin" required minlength="4" maxlength="20" inputmode="numeric" placeholder="PIN">

        <button type="submit" class="btn btn-primary btn-sm btn-block" <?= empty($users) ? 'disabled' : '' ?>>Entrar</button>
    </form>

    <?php if (empty($users)): ?>
        <p class="small-text">No hay usuarios activos para login. Contacta al admin.</p>
    <?php endif; ?>

</section>

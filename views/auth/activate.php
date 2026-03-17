<section class="auth-card auth-card-simple">
    <h1>Activar</h1>
    <p class="auth-subtitle">Configura tu PIN para habilitar la cuenta y entrar a Fulbo.</p>

    <form method="post" action="<?= e(url('/activar')) ?>" class="stack auth-form-lg">
        <?= csrf_field() ?>

        <select name="username" required>
            <option value="">Usuario</option>
            <?php foreach (($users ?? []) as $item): ?>
                <option value="<?= e($item['username']) ?>"><?= e($item['nombre']) ?> (@<?= e($item['username']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="password" name="pin" required minlength="4" maxlength="20" inputmode="numeric" placeholder="PIN nuevo">
        <input type="password" name="pin_confirm" required minlength="4" maxlength="20" inputmode="numeric" placeholder="Confirmar PIN">

        <button type="submit" class="btn btn-primary btn-sm btn-block">Activar cuenta</button>
    </form>
</section>

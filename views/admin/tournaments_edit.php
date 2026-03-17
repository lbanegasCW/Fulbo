<?php
$scheduledValue = '';
if (!empty($tournament['inicio_programado_at'])) {
    $scheduledValue = date('Y-m-d\TH:i', strtotime((string) $tournament['inicio_programado_at']));
}
?>

<section class="card admin-card">
    <div class="card-head">
        <h1>Editar torneo</h1>
        <a href="<?= e(url('/torneos/' . (int) $tournament['id'])) ?>" class="btn btn-outline btn-sm">Volver</a>
    </div>

    <form method="post" action="<?= e(url('/admin/torneos/editar')) ?>" class="stack admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="torneo_id" value="<?= (int) $tournament['id'] ?>">

        <label class="admin-label">
            Nombre del torneo
            <input name="nombre" required maxlength="120" value="<?= e((string) $tournament['nombre']) ?>">
        </label>

        <div class="grid-2 form-row-halves">
            <label class="admin-label">
                Año
                <input type="number" min="2000" max="2100" name="anio" value="<?= e((string) $tournament['anio']) ?>" required>
            </label>
            <label class="admin-label">
                Rondas iniciales
                <input type="number" min="1" max="5" name="rondas_iniciales" value="<?= e((string) $tournament['rondas_iniciales']) ?>" required>
            </label>
        </div>

        <label class="admin-label">
            Fecha y hora del torneo (opcional)
            <input type="datetime-local" name="inicio_programado_at" step="60" value="<?= e($scheduledValue) ?>">
        </label>

        <div class="admin-actions-row">
            <button class="btn btn-primary btn-sm btn-block" type="submit">Guardar cambios</button>
        </div>
    </form>
</section>

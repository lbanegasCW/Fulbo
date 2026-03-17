<section class="card admin-card">
    <div class="card-head">
        <h1>Nuevo torneo</h1>
        <a href="<?= e(url('/torneos')) ?>" class="btn btn-outline btn-sm">Volver</a>
    </div>

    <form method="post" action="<?= e(url('/admin/torneos/crear')) ?>" class="stack admin-form">
        <?= csrf_field() ?>

        <label class="admin-label">
            Nombre del torneo
            <input name="nombre" required maxlength="120" placeholder="Ej: Apertura Fulbo">
        </label>

        <div class="grid-2 form-row-halves">
            <label class="admin-label">
                Año
                <input type="number" name="anio" value="<?= date('Y') ?>" required>
            </label>
            <label class="admin-label">
                Rondas iniciales
                <input type="number" min="1" max="5" name="rondas_iniciales" value="1" required>
            </label>
        </div>

        <label class="admin-label">
            Fecha y hora del torneo (opcional)
            <input type="datetime-local" name="inicio_programado_at" step="60">
        </label>

        <label class="admin-label">
            Jugadores participantes
            <select name="jugadores[]" class="multi-select tall-select" multiple size="10" required>
                <?php foreach ($players as $player): ?>
                    <option value="<?= (int) $player['id'] ?>"><?= e($player['nombre']) ?> (@<?= e($player['username']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="admin-label">
            Bombo del torneo
            <select name="bombo_id" class="tall-select" required>
                <option value="">Seleccionar bombo</option>
                <?php foreach ($pots as $pot): ?>
                    <option value="<?= (int) $pot['id'] ?>"><?= e($pot['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="grid-2 form-row-halves">
            <label class="admin-label">
                Equipos por jugador
                <input type="number" min="1" max="5" name="equipos_por_jugador" value="1" required>
            </label>
            <label class="admin-label">
                Oferta por turno
                <input type="number" min="2" max="6" name="oferta_por_turno" value="2" required>
            </label>
        </div>

        <button class="btn btn-primary btn-sm btn-block" type="submit">Crear torneo</button>
    </form>
</section>

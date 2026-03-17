<?php
$user = auth_user();
$currentTurn = $turnState['current'];
$showCompetitiveSections = in_array((string) $tournament['estado'], ['eleccion_equipos', 'en_juego', 'desempate', 'finalizado'], true);
$scheduledAt = null;
if (!empty($tournament['inicio_programado_at'])) {
    $scheduledAt = strtotime((string) $tournament['inicio_programado_at']);
}
$scheduledLabel = $scheduledAt !== null
    ? (date('d/m/Y', $scheduledAt) . ' · ' . date('H:i', $scheduledAt) . ' hs')
    : 'Sin fecha definida';
?>
<section class="card">
    <div class="card-head">
        <h1><?= e($tournament['nombre']) ?></h1>
        <div class="inline-actions">
            <span class="badge badge-state"><?= e(state_label((string) $tournament['estado'])) ?></span>
            <?php if (is_admin() && (string) $tournament['estado'] !== 'finalizado'): ?>
                <a href="<?= e(url('/admin/torneos/' . (int) $tournament['id'] . '/editar')) ?>" class="btn btn-outline btn-sm">Editar</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="next-tournament next-tournament-compact tournament-summary">
        <div class="next-tournament-body">
            <strong class="summary-date">Fecha: <?= e($scheduledLabel) ?></strong>
            <span>Temporada <?= e((string) $tournament['anio']) ?></span>
            <span>Rondas iniciales: <?= e((string) $tournament['rondas_iniciales']) ?> · Bombo: <?= e($pots[0]['nombre'] ?? 'Sin bombo') ?></span>
        </div>
    </div>

    <?php if (is_admin() && $tournament['estado'] === 'sorteo_pendiente'): ?>
        <form method="post" action="<?= e(url('/admin/torneos/sortear')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="torneo_id" value="<?= (int) $tournament['id'] ?>">
            <button type="submit" class="btn btn-primary btn-sm btn-block">Iniciar sorteo</button>
        </form>
    <?php endif; ?>
</section>

<?php if ($tournament['estado'] === 'eleccion_equipos' && $currentTurn): ?>
    <section class="card">
        <h2>Turno de eleccion</h2>
        <p><strong>Ahora elige:</strong> <?= e($currentTurn['jugador_nombre']) ?> (<?= e($currentTurn['bombo_nombre']) ?>)</p>
        <?php if ($turnState['next']): ?>
            <p class="small-text">Sigue: <?= e($turnState['next']['jugador_nombre']) ?></p>
        <?php endif; ?>

        <?php if ((int) $currentTurn['usuario_id'] === (int) $user['id']): ?>
            <form method="post" action="<?= e(url('/torneos/elegir-equipo')) ?>" class="stack">
                <?= csrf_field() ?>
                <input type="hidden" name="torneo_id" value="<?= (int) $tournament['id'] ?>">
                <label>
                    Elegi tu equipo
                    <select name="equipo_id" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($currentTurn['equipos_ofrecidos'] as $team): ?>
                            <option value="<?= (int) $team['id'] ?>"><?= e($team['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="btn btn-primary">Confirmar eleccion</button>
            </form>
        <?php else: ?>
            <p class="small-text">Esperando que el jugador en turno confirme su seleccion.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h2 class="section-title">Elecciones por torneo</h2>
    <?php if (empty($selectionBoard)): ?>
        <p>Sin sorteo activo todavia.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="tournament-picks-table">
                <thead><tr><th>#</th><th>Jugador</th><th>Equipo</th></tr></thead>
                <tbody>
                <?php foreach ($selectionBoard as $row): ?>
                    <tr>
                        <td><?= e((string) $row['posicion']) ?></td>
                        <td><?= e($row['jugador']) ?></td>
                        <td><?= e($row['equipos']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($showCompetitiveSections): ?>
    <section class="card">
        <h2 class="section-title">Fixture por fechas</h2>
        <?php if (empty($groupedRounds)): ?>
            <p>No hay partidos generados todavia.</p>
        <?php endif; ?>

        <div class="accordion-list">
            <?php foreach ($groupedRounds as $round): ?>
                <?php $isOpen = $activeRoundKey !== null && $activeRoundKey === $round['key']; ?>
                <details class="round-accordion" <?= $isOpen ? 'open' : '' ?>>
                    <summary>
                        <span>Fecha <?= e((string) $round['numero']) ?> · <?= e($round['tipo']) ?></span>
                        <span class="small-text"><?= count($round['matches']) ?> partidos</span>
                    </summary>

                    <div class="fixture-list">
                        <?php foreach ($round['matches'] as $match): ?>
                            <article class="match-card <?= $isOpen ? 'match-card-active' : '' ?>">
                                <div class="match-line">
                                    <h3><?= e($match['local_nombre']) ?> vs <?= e($match['visitante_nombre']) ?></h3>
                                    <span class="badge badge-state"><?= e(state_label((string) $match['estado'])) ?></span>
                                </div>
                                <p class="small-text teams-line">
                                    <?= e($match['equipo_local']) ?> · <?= e($match['equipo_visitante']) ?>
                                    <?php if ($match['estado'] === 'jugado'): ?>
                                        <strong>(<?= e((string) $match['goles_local'] . ' - ' . (string) $match['goles_visitante']) ?>)</strong>
                                    <?php endif; ?>
                                </p>
                                <?php if ($isOpen): ?>
                                    <p class="small-text"><span class="next-badge">Fecha habilitada</span></p>
                                <?php endif; ?>

                                <?php $canSubmit = is_admin() || (
                                    $match['estado'] === 'pendiente'
                                    && $isOpen
                                    && ((int) $match['jugador_local_id'] === (int) $user['id'] || (int) $match['jugador_visitante_id'] === (int) $user['id'])
                                ); ?>

                                <?php if ($canSubmit): ?>
                                    <form method="post" action="<?= e(url('/torneos/cargar-resultado')) ?>" class="result-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="torneo_id" value="<?= (int) $tournament['id'] ?>">
                                        <input type="hidden" name="partido_id" value="<?= (int) $match['id'] ?>">

                                        <label class="goal-picker">
                                            <span><?= e($match['local_nombre']) ?></span>
                                            <select name="goles_local" required>
                                                <?php for ($i = 0; $i <= 15; $i++): ?>
                                                    <option value="<?= $i ?>" <?= ((string) $match['goles_local'] === (string) $i) ? 'selected' : '' ?>><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </label>

                                        <label class="goal-picker">
                                            <span><?= e($match['visitante_nombre']) ?></span>
                                            <select name="goles_visitante" required>
                                                <?php for ($i = 0; $i <= 15; $i++): ?>
                                                    <option value="<?= $i ?>" <?= ((string) $match['goles_visitante'] === (string) $i) ? 'selected' : '' ?>><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </label>

                                        <button class="icon-btn" type="submit" aria-label="Guardar resultado" title="Guardar resultado">
                                            <svg viewBox="0 0 24 24" role="img" aria-hidden="true">
                                                <path d="M5 4h11l3 3v13H5V4zm2 0v5h8V4H7zm0 10v4h10v-4H7z"/>
                                            </svg>
                                        </button>
                                    </form>
                                <?php elseif ($match['estado'] === 'pendiente'): ?>
                                    <p class="small-text">Bloqueado hasta que se habilite esta ronda.</p>
                                <?php elseif (is_admin()): ?>
                                    <p class="small-text">Como admin puedes editar resultados desde cualquier fecha.</p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card">
        <h2 class="section-title">Tabla en vivo</h2>
        <div class="table-wrap">
            <table class="standings-table">
                <thead>
                <tr><th>#</th><th>Jugador</th><th>PTS</th><th>PJ</th><th>PG</th><th>PE</th><th>PP</th><th>GF</th><th>GC</th><th>DG</th></tr>
                </thead>
                <tbody>
                <?php foreach ($standings as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= e($row['nombre']) ?></td>
                        <td><?= e((string) $row['puntos']) ?></td>
                        <td><?= e((string) $row['pj']) ?></td>
                        <td><?= e((string) $row['pg']) ?></td>
                        <td><?= e((string) $row['pe']) ?></td>
                        <td><?= e((string) $row['pp']) ?></td>
                        <td><?= e((string) $row['gf']) ?></td>
                        <td><?= e((string) $row['gc']) ?></td>
                        <td><?= e((string) $row['dg']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (is_admin()): ?>
            <div class="actions-row">
                <form method="post" action="<?= e(url('/torneos/cerrar')) ?>" class="action-form-wide">
                    <?= csrf_field() ?>
                    <input type="hidden" name="torneo_id" value="<?= (int) $tournament['id'] ?>">
                    <button class="btn btn-primary btn-sm btn-block" type="submit">Evaluar cierre / desempate</button>
                </form>
                <?php if ($tournament['estado'] === 'desempate'): ?>
                    <form method="post" action="<?= e(url('/torneos/desempate/resolver')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="torneo_id" value="<?= (int) $tournament['id'] ?>">
                        <button class="btn btn-outline" type="submit">Resolver desempate</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

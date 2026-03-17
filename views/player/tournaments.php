<section class="card">
    <div class="card-head">
        <h1>Torneos</h1>
        <?php if (is_admin()): ?>
            <a href="<?= e(url('/admin/torneos/nuevo')) ?>" class="btn btn-primary btn-sm">Nuevo</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($filterLabel)): ?>
        <p class="small-text"><?= e($filterLabel) ?></p>
    <?php endif; ?>

    <?php if (!empty($filteredTournaments)): ?>
        <div class="list">
            <?php foreach ($filteredTournaments as $item): ?>
                <a href="<?= e(url('/torneos/' . (int) $item['id'])) ?>" class="list-item">
                    <strong><?= e($item['nombre']) ?></strong>
                    <span><?= e((string) $item['anio']) ?> · <?= e(state_label((string) $item['estado'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php elseif (!$activeTournament): ?>
        <p>No hay torneos activos.</p>
    <?php else: ?>
        <a href="<?= e(url('/torneos/' . (int) $activeTournament['id'])) ?>" class="list-item">
            <strong><?= e($activeTournament['nombre']) ?></strong>
            <span><?= e((string) $activeTournament['anio']) ?> · <?= e(state_label((string) $activeTournament['estado'])) ?></span>
        </a>
    <?php endif; ?>
</section>

<?php if (!empty($showRecentHistory)): ?>
    <section class="card">
        <div class="card-head">
            <h2>Historial reciente</h2>
            <a href="<?= e(url('/torneos?historial=1&anio=' . (int) $historyYear)) ?>">Ver historial</a>
        </div>
        <?php if (empty($recentHistory)): ?>
            <p>Todavia no hay torneos finalizados para mostrar.</p>
        <?php else: ?>
            <div class="list">
                <?php foreach ($recentHistory as $item): ?>
                    <a href="<?= e(url('/torneos/' . (int) $item['id'])) ?>" class="list-item">
                        <strong><?= e($item['nombre']) ?></strong>
                        <span><?= e((string) $item['anio']) ?> · <?= e(state_label((string) $item['estado'])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

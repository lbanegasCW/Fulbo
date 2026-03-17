<section class="card">
    <div class="card-head">
        <h1>Ranking anual</h1>
    </div>

    <form method="get" action="<?= e(url('/ranking')) ?>" class="year-filter-row year-filter-inline">
        <span>Temporada</span>
        <select name="anio" onchange="this.form.submit()">
            <?php foreach (($years ?? []) as $year): ?>
                <option value="<?= (int) $year ?>" <?= (int) $selectedYear === (int) $year ? 'selected' : '' ?>><?= (int) $year ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="table-wrap">
        <table class="ranking-table">
            <thead><tr><th>#</th><th>Jugador</th><th>Campeonatos</th><th>Detalle</th></tr></thead>
            <tbody>
            <?php foreach ($ranking as $index => $row): ?>
                <tr>
                    <td>
                        <?php if ($index === 0): ?>🥇<?php elseif ($index === 1): ?>🥈<?php elseif ($index === 2): ?>🥉<?php else: ?><?= $index + 1 ?><?php endif; ?>
                    </td>
                    <td><?= e($row['nombre']) ?></td>
                    <td class="col-center"><?= e((string) $row['campeonatos']) ?></td>
                    <td>
                        <?php if ((int) ($row['id'] ?? 0) > 0): ?>
                            <a class="btn btn-outline btn-sm table-action" href="<?= e(url('/torneos?ganador_id=' . (int) $row['id'] . '&anio=' . (int) $selectedYear)) ?>">
                                <span>Ver</span>
                            </a>
                        <?php else: ?>
                            <span class="small-text">Sin detalle</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

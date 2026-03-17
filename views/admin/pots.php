<section class="card">
    <h1>Bombos y equipos</h1>

    <h2>Nuevo bombo</h2>
    <form method="post" action="<?= e(url('/admin/bombos/crear')) ?>" class="grid-form">
        <?= csrf_field() ?>
        <input name="nombre" required placeholder="Nombre del bombo">
        <input name="descripcion" placeholder="Descripcion">
        <label><input type="checkbox" name="activo" checked> Activo</label>
        <button class="btn btn-primary" type="submit">Crear bombo</button>
    </form>

    <h2>Listado de bombos</h2>
    <div class="list">
        <?php foreach ($pots as $pot): ?>
            <article class="list-item">
                <div>
                    <strong><?= e($pot['nombre']) ?></strong>
                    <span><?= e($pot['descripcion']) ?> · <?= (int) $pot['total_equipos'] ?> equipos</span>
                </div>
                <div class="inline-actions">
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/bombos?bombo_id=' . (int) $pot['id'])) ?>">Ver equipos</a>
                    <details>
                        <summary class="icon-link" title="Editar">✎</summary>
                        <form method="post" action="<?= e(url('/admin/bombos/editar')) ?>" class="stack compact">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $pot['id'] ?>">
                            <input name="nombre" value="<?= e($pot['nombre']) ?>" required>
                            <input name="descripcion" value="<?= e($pot['descripcion']) ?>">
                            <label><input type="checkbox" name="activo" <?= (int) $pot['activo'] === 1 ? 'checked' : '' ?>> Activo</label>
                            <button class="btn btn-outline btn-sm">Guardar</button>
                        </form>
                    </details>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($selectedPot): ?>
    <section class="card">
        <h2>Equipos de <?= e($selectedPot['nombre']) ?></h2>
        <form method="post" action="<?= e(url('/admin/equipos/crear')) ?>" class="grid-form">
            <?= csrf_field() ?>
            <input type="hidden" name="bombo_id" value="<?= (int) $selectedPot['id'] ?>">
            <input name="nombre" required placeholder="Nombre equipo">
            <input name="abreviatura" required maxlength="6" placeholder="Abrev.">
            <label><input type="checkbox" name="activo" checked> Activo</label>
            <button class="btn btn-primary">Agregar equipo</button>
        </form>

        <div class="table-wrap">
            <table class="teams-table">
                <thead><tr><th>Equipo</th><th>Abrev</th><th>Activo</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($teams as $team): ?>
                    <tr>
                        <td><?= e($team['nombre']) ?></td>
                        <td><?= e($team['abreviatura']) ?></td>
                        <td><?= (int) $team['activo'] === 1 ? 'Si' : 'No' ?></td>
                        <td>
                            <details>
                                <summary class="icon-link" title="Editar">✎</summary>
                                <form method="post" action="<?= e(url('/admin/equipos/editar')) ?>" class="stack compact">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $team['id'] ?>">
                                    <input name="nombre" value="<?= e($team['nombre']) ?>">
                                    <input name="abreviatura" value="<?= e($team['abreviatura']) ?>">
                                    <label><input type="checkbox" name="activo" <?= (int) $team['activo'] === 1 ? 'checked' : '' ?>> Activo</label>
                                    <button class="btn btn-outline btn-sm">Guardar</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

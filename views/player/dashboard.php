<section class="hero">
    <h1>Bienvenido a Fulbo !</h1>
    <p>Prepara los cruces de FC, compite con tu grupo y vivi cada fecha como un modo carrera entre amigos.</p>

    <div class="hero-meta">
        <div class="hero-box">
            <strong>Inscripcion</strong>
            <span>$5000 por torneo</span>
        </div>
        <div class="hero-box">
            <strong>Premios</strong>
            <span>1° $13000 · 2° $5000 · 3° $2000 · Ultimo $0</span>
        </div>
    </div>

    <div class="stream-links">
        <a class="stream-link" href="https://www.twitch.tv/luisbanegas05" target="_blank" rel="noopener noreferrer">
            <span class="stream-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 2h18v13l-4 4h-4l-2 3h-3v-3H3V2zm16 11V4H5v11h4v3l2-3h5l3-3zM9 7h2v5H9V7zm5 0h2v5h-2V7z"/></svg></span>
            <span>Luis</span>
        </a>
        <a class="stream-link" href="https://www.twitch.tv/maximof420" target="_blank" rel="noopener noreferrer">
            <span class="stream-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 2h18v13l-4 4h-4l-2 3h-3v-3H3V2zm16 11V4H5v11h4v3l2-3h5l3-3zM9 7h2v5H9V7zm5 0h2v5h-2V7z"/></svg></span>
            <span>Maxi</span>
        </a>
        <a class="stream-link" href="https://twitch.tv/nicomurciano" target="_blank" rel="noopener noreferrer">
            <span class="stream-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 2h18v13l-4 4h-4l-2 3h-3v-3H3V2zm16 11V4H5v11h4v3l2-3h5l3-3zM9 7h2v5H9V7zm5 0h2v5h-2V7z"/></svg></span>
            <span>Nico</span>
        </a>
    </div>

</section>

<section class="card">
    <div class="card-head">
        <h2>Proximo torneo</h2>
        <a href="<?= e(url('/torneos')) ?>">Ir a torneos</a>
    </div>
    <?php if (empty($nextTournament) || empty($nextTournament['inicio_programado_at'])): ?>
        <p>Todavia no hay fecha definida para el proximo torneo.</p>
    <?php else: ?>
        <?php
        $nextDate = strtotime((string) $nextTournament['inicio_programado_at']);
        $monthMap = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR', 5 => 'MAY', 6 => 'JUN',
            7 => 'JUL', 8 => 'AGO', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC',
        ];
        $monthLabel = $monthMap[(int) date('n', $nextDate)] ?? strtoupper(date('M', $nextDate));
        ?>
        <div class="next-tournament">
            <div class="next-date-badge" aria-hidden="true">
                <span class="next-date-day"><?= e(date('d', $nextDate)) ?></span>
                <span class="next-date-month"><?= e($monthLabel) ?></span>
            </div>
            <div class="next-tournament-body">
                <strong><?= e($nextTournament['nombre']) ?></strong>
                <span><?= e((string) $nextTournament['anio']) ?> · <?= e(state_label((string) $nextTournament['estado'])) ?></span>
                <span><?= e(date('d/m/Y', $nextDate)) ?> · <?= e(date('H:i', $nextDate)) ?> hs</span>
            </div>
            <a class="btn btn-outline btn-sm" href="<?= e(url('/torneos/' . (int) $nextTournament['id'])) ?>">Ver</a>
        </div>
    <?php endif; ?>
</section>

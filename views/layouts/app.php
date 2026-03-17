<?php
$user = auth_user();
$flashes = get_flashes();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = base_path_url();
if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath));
    $currentPath = $currentPath === '' ? '/' : $currentPath;
}
$currentPath = rtrim($currentPath, '/') ?: '/';

$isActive = static function (array $prefixes) use ($currentPath): bool {
    foreach ($prefixes as $prefix) {
        if ($prefix === $currentPath) {
            return true;
        }
        if ($prefix !== '/' && str_starts_with($currentPath, $prefix . '/')) {
            return true;
        }
    }
    return false;
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#eef1f5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= e(($title ?? 'Fulbo') . ' | Fulbo') ?></title>
    <link rel="manifest" href="<?= e(versioned_public_url('pwa/manifest.json')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(versioned_public_url('assets/img/logo_fulbo-192.png')) ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= e(versioned_public_url('assets/img/logo_fulbo-192.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(versioned_public_url('assets/img/logo_fulbo-512.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<?php $brandLogo = asset('img/logo_fulbo.png'); ?>
<div class="app-shell">
    <header class="topbar">
        <div class="topbar-inner">
            <?php if ($user): ?>
                <div class="floating-menu">
                    <nav class="menu-side">
                        <a href="<?= e(url('/dashboard')) ?>" class="nav-icon-link <?= $isActive(['/dashboard']) ? 'is-active' : '' ?>">
                            <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l9 8h-3v10h-5v-6H11v6H6V11H3l9-8z"/></svg></span>
                            <small>Inicio</small>
                        </a>
                        <a href="<?= e(url('/torneos')) ?>" class="nav-icon-link <?= $isActive(['/torneos', '/admin/torneos']) ? 'is-active' : '' ?>">
                            <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 4h6v6H4V4zm10 0h6v4h-6V4zM4 14h6v6H4v-6zm10-3h6v9h-6v-9zm2 2v5h2v-5h-2z"/></svg></span>
                            <small>Torneos</small>
                        </a>
                    </nav>

                    <a href="<?= e(url('/dashboard')) ?>" class="menu-logo" aria-label="Fulbo Inicio">
                        <img src="<?= e($brandLogo) ?>" onerror="this.onerror=null;this.src='<?= e(asset('img/fulbo-logo.svg')) ?>';" alt="Fulbo">
                    </a>

                    <nav class="menu-side menu-side-right">
                        <?php if (is_admin()): ?>
                            <a href="<?= e(url('/admin/usuarios')) ?>" class="nav-icon-link <?= $isActive(['/admin/usuarios']) ? 'is-active' : '' ?>">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 11a4 4 0 10-8 0 4 4 0 008 0zm-10 9a6 6 0 1112 0H6z"/></svg></span>
                                <small>Usuarios</small>
                            </a>
                        <?php else: ?>
                            <a href="<?= e(url('/ranking')) ?>" class="nav-icon-link <?= $isActive(['/ranking']) ? 'is-active' : '' ?>">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 20h10v2H7v-2zm9-2H8V8h8v10zm-5-8h2v6h-2v-6zM3 18h3V5H3v13zm15 0h3v-8h-3v8z"/></svg></span>
                                <small>Ranking</small>
                            </a>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('/logout')) ?>" class="logout-icon-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="nav-icon-link nav-icon-btn" aria-label="Salir">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 3h8a2 2 0 012 2v14a2 2 0 01-2 2h-8v-2h8V5h-8V3zM4 12l4-4v3h7v2H8v3l-4-4z"/></svg></span>
                                <small>Salir</small>
                            </button>
                        </form>
                    </nav>
                </div>
            <?php else: ?>
                <div class="floating-menu guest-menu">
                    <a href="<?= e(url('/login')) ?>" class="nav-icon-link <?= $isActive(['/login']) ? 'is-active' : '' ?>">
                        <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 3h8a2 2 0 012 2v14a2 2 0 01-2 2h-8v-2h8V5h-8V3zM4 12l4-4v3h7v2H8v3l-4-4z"/></svg></span>
                        <small>Ingresar</small>
                    </a>
                    <a href="<?= e(url('/login')) ?>" class="menu-logo" aria-label="Fulbo Inicio">
                        <img src="<?= e($brandLogo) ?>" onerror="this.onerror=null;this.src='<?= e(asset('img/fulbo-logo.svg')) ?>';" alt="Fulbo">
                    </a>
                    <a href="<?= e(url('/activar')) ?>" class="nav-icon-link <?= $isActive(['/activar']) ? 'is-active' : '' ?>">
                        <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2l4 4-1.4 1.4-1.6-1.6V14h-2V5.8L9.4 7.4 8 6l4-4zm-7 13h14v7H5v-7zm2 2v3h10v-3H7z"/></svg></span>
                        <small>Activar</small>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?php foreach ($flashes as $flash): ?>
            <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>
</div>

<button type="button" id="installAppBtn" class="install-btn" hidden aria-label="Instalar app" title="Instalar app">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 3h2v9l3-3 1.4 1.4L12 17l-5.4-6.6L8 9l3 3V3zM5 19h14v2H5v-2z"/></svg>
</button>

<script>
window.__BASE_PATH__ = "<?= e(base_path_url()) ?>";
window.__SW_VERSION__ = "<?= e(public_file_version('pwa/service-worker.js')) ?>";
</script>
<script type="module" src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>

export const initPwa = () => {
    const basePath = (window.__BASE_PATH__ || '').replace(/\/$/, '');
    const swVersion = window.__SW_VERSION__ || '1';
    const swUrl = `${basePath}/pwa/service-worker.js?v=${encodeURIComponent(swVersion)}`;
    const swScope = `${basePath || ''}/`;
    const installBtn = document.getElementById('installAppBtn');
    const getIsStandalone = () => window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true
        || document.referrer.startsWith('android-app://');
    const isIOS = /iphone|ipad|ipod/i.test(window.navigator.userAgent)
        || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
    const isAndroid = /android/i.test(window.navigator.userAgent);

    let deferredPrompt = null;

    const showInstallButton = () => {
        if (!installBtn || getIsStandalone()) {
            return;
        }
        installBtn.hidden = false;
    };

    const hideInstallButton = () => {
        if (!installBtn) {
            return;
        }
        installBtn.hidden = true;
    };

    const showIOSInstructions = () => {
        window.alert('Para instalar Fulbo en iPhone: toca Compartir y luego "Agregar a pantalla de inicio".');
    };

    const showAndroidManualInstructions = () => {
        window.alert('Si no aparece el instalador automatico, abre el menu del navegador y elige "Instalar app" o "Agregar a pantalla de inicio".');
    };

    const syncInstallVisibility = () => {
        if (!installBtn) {
            return;
        }
        if (getIsStandalone()) {
            hideInstallButton();
            return;
        }
        if (deferredPrompt || isIOS || isAndroid) {
            showInstallButton();
            return;
        }
        hideInstallButton();
    };

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                syncInstallVisibility();
                return;
            }

            if (isIOS && !getIsStandalone()) {
                showIOSInstructions();
                return;
            }

            showAndroidManualInstructions();
        });
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        syncInstallVisibility();
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        hideInstallButton();
    });

    const standaloneMedia = window.matchMedia('(display-mode: standalone)');
    if (typeof standaloneMedia.addEventListener === 'function') {
        standaloneMedia.addEventListener('change', syncInstallVisibility);
    } else if (typeof standaloneMedia.addListener === 'function') {
        standaloneMedia.addListener(syncInstallVisibility);
    }
    window.addEventListener('visibilitychange', syncInstallVisibility);

    syncInstallVisibility();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(swUrl, { scope: swScope }).catch(() => {
                // no-op
            });
        });
    }
};

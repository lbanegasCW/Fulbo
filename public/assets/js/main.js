const initInputGuards = () => {
    document.querySelectorAll('input[name="pin"], input[name="pin_confirm"]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '');
        });
    });
};

const initAuthFocusGuard = () => {
    if (!document.querySelector('.auth-card')) {
        return;
    }

    if (window.visualViewport && window.visualViewport.width <= 768) {
        document.querySelectorAll('select, input').forEach((el) => {
            el.setAttribute('autocapitalize', 'off');
            el.setAttribute('autocomplete', 'off');
        });
    }

    window.setTimeout(() => {
        const el = document.activeElement;
        if (!(el instanceof HTMLElement)) {
            return;
        }

        if (el.tagName === 'SELECT' || el.tagName === 'INPUT') {
            el.blur();
        }
    }, 0);
};

const initPwaWithVersion = async () => {
    try {
        const version = encodeURIComponent(window.__SW_VERSION__ || '1');
        const module = await import(`./modules/pwa.js?v=${version}`);
        if (typeof module.initPwa === 'function') {
            module.initPwa();
        }
    } catch (error) {
    }
};

const initNoOverflowScrollGuard = () => {
    const root = document.documentElement;

    const update = () => {
        const noOverflow = root.scrollHeight <= window.innerHeight + 1;
        root.classList.toggle('no-scroll', noOverflow);
    };

    update();
    window.addEventListener('resize', update, { passive: true });
    window.addEventListener('orientationchange', update, { passive: true });
};

const init = () => {
    initInputGuards();
    initAuthFocusGuard();
    initNoOverflowScrollGuard();
    void initPwaWithVersion();
};

document.addEventListener('DOMContentLoaded', init);

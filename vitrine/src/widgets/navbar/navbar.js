// src/widgets/navbar/navbar.js
function initNavbar() {
    const burger = document.querySelector('#menu-toggle');
    const menu   = document.querySelector('#navbar-dropdown');

    const servicesBtn = document.getElementById('dropdownNavbarLink');
    const servicesBox = document.getElementById('dropdownNavbar');

    const langBtn = document.getElementById('states-button');
    const langBox = document.getElementById('dropdown-states');

    const firstMenuLink = () => menu?.querySelector('a.nav-item, a.nav-link');

    const openMenu = () => {
        menu.classList.remove('hidden');
        burger.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('overflow-hidden');
        firstMenuLink()?.focus();
    };

    const closeMenu = () => {
        menu.classList.add('hidden');
        burger.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('overflow-hidden');
    };

    const toggleMenu = () => {
        const isHidden = menu.classList.contains('hidden');
        isHidden ? openMenu() : closeMenu();
    };

    const closeServices = () => {
        if (!servicesBox) return;
        servicesBox.classList.add('hidden');
        servicesBox.setAttribute('aria-hidden', 'true');
        servicesBtn?.setAttribute('aria-expanded', 'false');
    };

    const closeLang = () => {
        if (!langBox) return;
        langBox.classList.add('hidden');
        langBox.setAttribute('aria-hidden', 'true');
        langBtn?.setAttribute('aria-expanded', 'false');
    };

    const closeAllDropdowns = () => { closeServices(); closeLang(); };

    if (burger && menu) {
        burger.addEventListener('click', toggleMenu);
        const mq = window.matchMedia('(min-width: 1024px)');
        const closeOnDesktop = e => { if (e.matches) closeMenu(); };
        if (mq.addEventListener) mq.addEventListener('change', closeOnDesktop);
        else if (mq.addListener) mq.addListener(closeOnDesktop);
    }

    if (servicesBtn && servicesBox) {
        servicesBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const nowHidden = servicesBox.classList.toggle('hidden');
            servicesBox.setAttribute('aria-hidden', String(nowHidden));
            servicesBtn.setAttribute('aria-expanded', String(!nowHidden));
            closeLang();
        });
    }

    if (langBtn && langBox) {
        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const nowHidden = langBox.classList.toggle('hidden');
            langBox.setAttribute('aria-hidden', String(nowHidden));
            langBtn.setAttribute('aria-expanded', String(!nowHidden));
            closeServices();
        });
    }

    document.addEventListener('click', (e) => {
        if (servicesBox && !servicesBox.classList.contains('hidden')) {
            if (!servicesBox.contains(e.target) && !servicesBtn.contains(e.target)) closeServices();
        }
        if (langBox && !langBox.classList.contains('hidden')) {
            if (!langBox.contains(e.target) && !langBtn.contains(e.target)) closeLang();
        }
        const clickedInsideMenu = menu?.contains(e.target) || burger?.contains(e.target);
        if (menu && !menu.classList.contains('hidden') && !clickedInsideMenu) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
            if (menu && !menu.classList.contains('hidden')) closeMenu();
        }
    });

    // Fix: normalize prend désormais aussi en compte les liens contenant un sous-dossier
    // (ex: "Blog/blog.html") en ne gardant que le nom de fichier final, comme pour `current`.
    const normalize = (path) => path
        .replace(/^\//, '')
        .replace(/\.html$/i, '')
        .split('/')
        .pop();

    const current = normalize(location.pathname.split('/').pop() || 'index');

    document.querySelectorAll('.nav-link[href]').forEach(a => {
        const href = normalize(a.getAttribute('href') || '');
        if (href === current || (current === '' && href === 'index')) {
            a.classList.add('active');
        }
    });
}
window.initNavbar = initNavbar;
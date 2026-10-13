(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var nav = document.querySelector('.janus-nav');
        if (!nav || document.querySelector('.janus-sidebar')) return;

        var sidebar = document.createElement('aside');
        sidebar.className = 'janus-sidebar';
        sidebar.setAttribute('aria-label', 'Menu principal');

        var logo = document.createElement('a');
        logo.className = 'janus-sidebar-logo';
        logo.href = nav.querySelector('a[href]') ? nav.querySelector('a[href]').getAttribute('href') : 'painel.php';
        logo.innerHTML = '<span class="janus-logo-mark">J</span><span class="janus-logo-text">JANUS</span>';

        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'janus-sidebar-toggle';
        toggle.setAttribute('aria-label', 'Minimizar menu');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.innerHTML = '<span class="toggle-arrow" aria-hidden="true">‹</span>';

        var links = document.createElement('nav');
        links.className = 'janus-sidebar-links';
        links.setAttribute('aria-label', 'Navegação');

        var footer = document.createElement('div');
        footer.className = 'janus-sidebar-footer';
        footer.setAttribute('aria-label', 'Conta');

        var footerItems = [];
        Array.prototype.forEach.call(nav.querySelectorAll('a'), function (a) {
            var href = a.getAttribute('href') || '';
            var text = a.textContent.trim().toLowerCase();
            var isLogout = href.indexOf('controllers/logout.php') !== -1 || text === 'sair';
            var isProfile = /(^|\/)perfil\.php(?:$|[?#])/.test(href) || text.indexOf('perfil') !== -1;
            var clone = a.cloneNode(true);
            clone.classList.add('janus-sidebar-link');
            var textNode = clone.querySelector('.nav-icone + span') || clone.querySelector('span:last-child');
            clone.setAttribute('aria-label', textNode ? textNode.textContent.trim() : clone.textContent.trim());
            if (isLogout || isProfile) {
                footerItems.push(clone);
            } else {
                links.appendChild(clone);
            }
        });

        // Algumas telas administrativas mantêm o logout fora do nav original.
        if (!footerItems.some(function (a) { return (a.getAttribute('href') || '').indexOf('controllers/logout.php') !== -1; })) {
            var oldUser = document.querySelector('.topbar-usuario, .topbar-dir');
            var oldLogout = oldUser ? oldUser.querySelector('a[href*="controllers/logout.php"]') : null;
            var logoutClone = oldLogout ? oldLogout.cloneNode(true) : document.createElement('a');
            if (!oldLogout) {
                logoutClone.href = '../../controllers/logout.php';
                logoutClone.innerHTML = '<span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/></svg></span><span>Sair</span>';
            }
            logoutClone.classList.add('janus-sidebar-link', 'sair-link');
            logoutClone.setAttribute('aria-label', 'Sair');
            footerItems.push(logoutClone);
        }

        // Perfil primeiro e Sair por último na área inferior do menu.
        footerItems.sort(function (a, b) {
            var at = a.textContent.trim().toLowerCase();
            var bt = b.textContent.trim().toLowerCase();
            return (at === 'sair') - (bt === 'sair');
        });
        footerItems.forEach(function (item) { footer.appendChild(item); });

        sidebar.appendChild(logo);
        sidebar.appendChild(toggle);
        sidebar.appendChild(links);
        if (footerItems.length) sidebar.appendChild(footer);
        document.body.appendChild(sidebar);

        var backdrop = document.createElement('div');
        backdrop.className = 'janus-sidebar-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.appendChild(backdrop);

        var mobileTrigger = document.createElement('button');
        mobileTrigger.type = 'button';
        mobileTrigger.className = 'janus-mobile-trigger';
        mobileTrigger.setAttribute('aria-label', 'Abrir menu');
        mobileTrigger.setAttribute('aria-expanded', 'false');
        mobileTrigger.innerHTML = '<span></span><span></span><span></span>';
        document.body.appendChild(mobileTrigger);

        function setCollapsed(collapsed, persist) {
            document.body.classList.toggle('janus-menu-collapsed', collapsed);
            sidebar.classList.toggle('collapsed', collapsed);
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('aria-label', collapsed ? 'Expandir menu' : 'Minimizar menu');
            if (persist) {
                try { localStorage.setItem('janus-menu-collapsed', collapsed ? '1' : '0'); } catch (e) {}
            }
        }

        function setMobile(open) {
            sidebar.classList.toggle('mobile-open', open);
            backdrop.classList.toggle('show', open);
            mobileTrigger.classList.toggle('is-open', open);
            mobileTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            mobileTrigger.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
            document.body.classList.toggle('janus-mobile-menu-open', open);
        }

        toggle.addEventListener('click', function () {
            if (window.innerWidth <= 760) {
                setMobile(!sidebar.classList.contains('mobile-open'));
            } else {
                setCollapsed(!sidebar.classList.contains('collapsed'), true);
            }
        });

        mobileTrigger.addEventListener('click', function () {
            setMobile(!sidebar.classList.contains('mobile-open'));
        });

        backdrop.addEventListener('click', function () { setMobile(false); });

        links.addEventListener('click', function (e) {
            if (window.innerWidth <= 760 && e.target.closest('a')) setMobile(false);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setMobile(false);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 760) setMobile(false);
        });

        // O menu antigo continua no HTML por compatibilidade, mas fica fora da interface.
        nav.style.display = 'none';
        document.querySelectorAll('.menu-toggle').forEach(function (button) {
            button.style.display = 'none';
        });

        var header = document.querySelector('header.topbar, header.app-header, .topbar-cadastro');
        if (header) header.classList.add('janus-header-with-sidebar');

        var saved = false;
        try { saved = localStorage.getItem('janus-menu-collapsed') === '1'; } catch (e) {}
        setCollapsed(saved, false);
        setMobile(false);
    });
})();

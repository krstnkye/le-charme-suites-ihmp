@once
    <style>
        [data-universal-sidebar] {
            display: flex !important;
            transform: translateX(-100%);
            transition: transform 220ms ease;
            will-change: transform;
        }

        body.sidebar-mobile-open [data-universal-sidebar] {
            transform: translateX(0);
        }

        [data-sidebar-backdrop] {
            display: none;
        }

        body.sidebar-mobile-open [data-sidebar-backdrop] {
            display: block;
        }

        [data-sidebar-toggle] {
            align-items: center;
            background: #01162d;
            border: 1px solid rgba(255, 222, 170, .65);
            border-radius: 0 8px 8px 0;
            bottom: 20px;
            box-shadow: 0 5px 16px rgba(1, 22, 45, .24);
            color: #ffdeaa;
            display: flex;
            height: 48px;
            justify-content: center;
            left: 0;
            position: fixed;
            top: auto;
            transition: left 220ms ease, background-color 160ms ease;
            width: 34px;
            z-index: 60;
        }

        [data-sidebar-toggle]:hover {
            background: #172b42;
        }

        [data-sidebar-toggle] .sidebar-close-icon,
        body.sidebar-mobile-open [data-sidebar-toggle] .sidebar-menu-icon {
            display: none;
        }

        body.sidebar-mobile-open [data-sidebar-toggle] {
            left: calc(var(--universal-sidebar-width, 18rem) - 17px);
        }

        body.sidebar-mobile-open [data-sidebar-toggle] .sidebar-close-icon {
            display: inline-block;
        }

        @media (max-width: 1023px) {
            body [class~="pl-72"],
            body [class~="lg:pl-72"],
            body [class~="lg:pl-64"],
            body [class~="lg:ml-72"],
            body [class~="lg:ml-64"] {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }

        @media (min-width: 1024px) {
            body [class~="pl-72"],
            body [class~="lg:pl-72"],
            body [class~="lg:pl-64"],
            body [class~="lg:ml-72"],
            body [class~="lg:ml-64"] {
                transition: margin-left 220ms ease, padding-left 220ms ease;
            }

            [data-universal-sidebar] {
                transform: translateX(0);
            }

            body:not(.sidebar-collapsed) [data-sidebar-toggle] {
                left: calc(var(--universal-sidebar-width, 18rem) - 17px);
            }

            body.sidebar-collapsed [data-universal-sidebar] {
                transform: translateX(-100%);
            }

            body.sidebar-collapsed [data-sidebar-toggle] {
                left: 0;
            }

            body.sidebar-collapsed [data-sidebar-toggle] .sidebar-menu-icon {
                display: inline-block;
            }

            body.sidebar-collapsed [data-sidebar-toggle] .sidebar-close-icon {
                display: none;
            }

            body:not(.sidebar-collapsed) [data-sidebar-toggle] .sidebar-menu-icon {
                display: none;
            }

            body:not(.sidebar-collapsed) [data-sidebar-toggle] .sidebar-close-icon {
                display: inline-block;
            }

            body.sidebar-collapsed [class~="pl-72"],
            body.sidebar-collapsed [class~="lg:pl-72"],
            body.sidebar-collapsed [class~="lg:pl-64"],
            body.sidebar-collapsed [class~="lg:ml-72"],
            body.sidebar-collapsed [class~="lg:ml-64"] {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }
    </style>

    <button type="button" data-sidebar-toggle aria-label="Hide sidebar" aria-expanded="true" title="Hide or show sidebar">
        <span class="material-symbols-outlined sidebar-menu-icon" aria-hidden="true">menu</span>
        <span class="material-symbols-outlined sidebar-close-icon" aria-hidden="true">left_panel_close</span>
    </button>
    <button type="button" data-sidebar-backdrop aria-label="Close sidebar" class="fixed inset-0 z-[35] bg-black/45 lg:hidden"></button>

    <script>
        (() => {
            const sidebar = document.querySelector('[data-universal-sidebar]');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const backdrop = document.querySelector('[data-sidebar-backdrop]');

            if (!sidebar || !toggle || !backdrop) return;

            const desktopQuery = window.matchMedia('(min-width: 1024px)');
            const storageKey = `le-charme-sidebar-collapsed:${sidebar.getAttribute('aria-label') || 'default'}`;

            const sync = () => {
                document.body.style.setProperty('--universal-sidebar-width', `${sidebar.offsetWidth}px`);
                const isDesktop = desktopQuery.matches;
                const isOpen = isDesktop
                    ? !document.body.classList.contains('sidebar-collapsed')
                    : document.body.classList.contains('sidebar-mobile-open');

                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Hide sidebar' : 'Show sidebar');
            };

            const restore = () => {
                document.body.classList.remove('sidebar-mobile-open');
                document.body.classList.toggle(
                    'sidebar-collapsed',
                    desktopQuery.matches && sessionStorage.getItem(storageKey) === 'true'
                );
                sync();
            };

            toggle.addEventListener('click', () => {
                if (desktopQuery.matches) {
                    const collapsed = document.body.classList.toggle('sidebar-collapsed');
                    sessionStorage.setItem(storageKey, String(collapsed));
                } else {
                    document.body.classList.toggle('sidebar-mobile-open');
                }
                sync();
            });

            backdrop.addEventListener('click', () => {
                document.body.classList.remove('sidebar-mobile-open');
                sync();
            });

            sidebar.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', () => {
                if (!desktopQuery.matches) document.body.classList.remove('sidebar-mobile-open');
            }));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.body.classList.remove('sidebar-mobile-open');
                    sync();
                }
            });

            desktopQuery.addEventListener('change', restore);
            restore();
        })();
    </script>
@endonce

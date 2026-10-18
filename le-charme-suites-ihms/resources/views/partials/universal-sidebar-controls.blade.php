@once
    <style>
        html[data-ihms-font-scale="large"] {
            font-size: 17px;
        }

        html[data-ihms-density="compact"] main table th,
        html[data-ihms-density="compact"] main table td {
            padding-bottom: .5rem !important;
            padding-top: .5rem !important;
        }

        html[data-ihms-density="compact"] main input,
        html[data-ihms-density="compact"] main select,
        html[data-ihms-density="compact"] main textarea {
            padding-bottom: .45rem !important;
            padding-top: .45rem !important;
        }

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
            border-radius: 8px;
            bottom: auto;
            box-shadow: 0 5px 16px rgba(1, 22, 45, .24);
            color: #ffdeaa;
            display: flex;
            height: 48px;
            justify-content: center;
            left: 12px;
            position: fixed;
            top: 20px;
            transition: left 220ms ease, background-color 160ms ease;
            width: 34px;
            z-index: 60;
        }

        [data-sidebar-toggle]:hover {
            background: #172b42;
        }

        [data-ui-settings-toggle] {
            align-items: center;
            background: #fff;
            border: 1px solid #c4c6cd;
            border-radius: 8px;
            box-shadow: 0 5px 16px rgba(1, 22, 45, .16);
            color: #01162d;
            display: flex;
            height: 42px;
            justify-content: center;
            position: fixed;
            right: 16px;
            top: 78px;
            width: 42px;
            z-index: 58;
        }

        [data-sidebar-toggle] .sidebar-close-icon,
        body.sidebar-mobile-open [data-sidebar-toggle] .sidebar-menu-icon {
            display: none;
        }

        body.sidebar-mobile-open [data-sidebar-toggle] {
            left: calc(var(--universal-sidebar-width, 18rem) - 46px);
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
                left: calc(var(--universal-sidebar-width, 18rem) - 46px);
            }

            body.sidebar-collapsed [data-universal-sidebar] {
                transform: translateX(-100%);
            }

            body.sidebar-collapsed [data-sidebar-toggle] {
                left: 12px;
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
    <button type="button" data-ui-settings-toggle aria-label="Open interface settings" title="Interface settings">
        <span class="material-symbols-outlined" aria-hidden="true">tune</span>
    </button>
    <div data-ui-settings-panel class="fixed right-4 top-[126px] z-[70] hidden w-[min(340px,calc(100vw-2rem))] rounded-lg border border-[#c4c6cd] bg-white p-4 text-[#01162d] shadow-2xl" role="dialog" aria-modal="false" aria-labelledby="ui-settings-title">
        <div class="flex items-start justify-between gap-4">
            <div><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#755a28]">User Preference</p><h2 id="ui-settings-title" class="text-base font-bold">Interface Settings</h2></div>
            <button type="button" data-ui-settings-close class="flex h-8 w-8 items-center justify-center rounded hover:bg-slate-100" aria-label="Close interface settings"><span class="material-symbols-outlined text-lg">close</span></button>
        </div>
        <form data-ui-settings-form class="mt-4 space-y-3">
            <label class="block"><span class="mb-1 block text-xs font-bold">Display density</span><select name="density" class="w-full rounded border border-[#c4c6cd] px-3 py-2 text-sm"><option value="comfortable">Comfortable</option><option value="compact">Compact</option></select></label>
            <label class="block"><span class="mb-1 block text-xs font-bold">Text size</span><select name="font_scale" class="w-full rounded border border-[#c4c6cd] px-3 py-2 text-sm"><option value="standard">Standard</option><option value="large">Large</option></select></label>
            <label class="block"><span class="mb-1 block text-xs font-bold">Desktop sidebar default</span><select name="sidebar_default" class="w-full rounded border border-[#c4c6cd] px-3 py-2 text-sm"><option value="expanded">Expanded</option><option value="collapsed">Collapsed</option></select></label>
            <p data-ui-settings-feedback class="hidden rounded bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800"></p>
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded bg-[#01162d] px-4 py-2.5 text-sm font-bold text-white"><span class="material-symbols-outlined text-base text-[#ffdeaa]">save</span>Save Interface Settings</button>
            <a href="{{ route('system-manual') }}" class="flex w-full items-center justify-center gap-2 rounded border border-[#c4c6cd] px-4 py-2.5 text-sm font-bold"><span class="material-symbols-outlined text-base text-[#755a28]">menu_book</span>Open System Flipbook</a>
        </form>
    </div>

    <script>
        (() => {
            const sidebar = document.querySelector('[data-universal-sidebar]');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const backdrop = document.querySelector('[data-sidebar-backdrop]');
            const settingsToggle = document.querySelector('[data-ui-settings-toggle]');
            const settingsPanel = document.querySelector('[data-ui-settings-panel]');
            const settingsClose = document.querySelector('[data-ui-settings-close]');
            const settingsForm = document.querySelector('[data-ui-settings-form]');
            const feedback = document.querySelector('[data-ui-settings-feedback]');
            const serverPreferences = @json(auth()->user()?->ui_preferences ?? []);
            const preferences = {
                density: serverPreferences.density || 'comfortable',
                font_scale: serverPreferences.font_scale || 'standard',
                sidebar_default: serverPreferences.sidebar_default || 'expanded',
            };

            if (!sidebar || !toggle || !backdrop) return;

            const desktopQuery = window.matchMedia('(min-width: 1024px)');
            const storageKey = `le-charme-sidebar-collapsed:${sidebar.getAttribute('aria-label') || 'default'}`;

            const applyPreferences = () => {
                document.documentElement.dataset.ihmsDensity = preferences.density;
                document.documentElement.dataset.ihmsFontScale = preferences.font_scale;
                if (settingsForm) {
                    settingsForm.elements.density.value = preferences.density;
                    settingsForm.elements.font_scale.value = preferences.font_scale;
                    settingsForm.elements.sidebar_default.value = preferences.sidebar_default;
                }
            };

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
                    desktopQuery.matches && (
                        sessionStorage.getItem(storageKey) === 'true'
                        || (sessionStorage.getItem(storageKey) === null && preferences.sidebar_default === 'collapsed')
                    )
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
            settingsToggle?.addEventListener('click', () => settingsPanel?.classList.toggle('hidden'));
            settingsClose?.addEventListener('click', () => settingsPanel?.classList.add('hidden'));
            settingsForm?.addEventListener('change', () => {
                preferences.density = settingsForm.elements.density.value;
                preferences.font_scale = settingsForm.elements.font_scale.value;
                preferences.sidebar_default = settingsForm.elements.sidebar_default.value;
                applyPreferences();
            });
            settingsForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                feedback?.classList.add('hidden');

                try {
                    const response = await fetch(@json(route('ui-preferences.update')), {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: JSON.stringify(preferences),
                    });

                    if (!response.ok) throw new Error('Unable to save interface settings.');
                    feedback.classList.remove('bg-red-50', 'text-red-800');
                    feedback.classList.add('bg-emerald-50', 'text-emerald-800');
                    feedback.textContent = 'Interface settings saved to your account.';
                    feedback.classList.remove('hidden');
                    sessionStorage.removeItem(storageKey);
                } catch (error) {
                    feedback.textContent = error.message;
                    feedback.classList.remove('hidden');
                    feedback.classList.remove('bg-emerald-50', 'text-emerald-800');
                    feedback.classList.add('bg-red-50', 'text-red-800');
                }
            });
            applyPreferences();
            restore();
        })();
    </script>

    @include('partials.page-back-button')
@endonce

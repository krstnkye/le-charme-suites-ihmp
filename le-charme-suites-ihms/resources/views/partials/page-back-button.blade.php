@once
    @php
        $showPageBack = ! request()->routeIs('dashboard');
    @endphp

    <style>
        [data-page-back-container] {
            display: flex;
            margin-bottom: 1rem;
        }

        [data-page-back] {
            align-items: center;
            background: #fff;
            border: 1px solid #c4c6cd;
            border-radius: 6px;
            color: #01162d;
            display: inline-flex;
            font-size: .875rem;
            font-weight: 700;
            gap: .5rem;
            min-height: 44px;
            padding: .625rem 1rem;
            transition: background-color 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
        }

        [data-page-back]:hover {
            background: #f7f4ee;
            border-color: #755a28;
        }

        [data-page-back]:focus-visible {
            box-shadow: 0 0 0 3px rgba(230, 193, 133, .55);
            outline: 2px solid #01162d;
            outline-offset: 2px;
        }
    </style>

    <div data-page-back-container hidden>
        <button type="button" data-page-back aria-label="Go back to the previous page">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            <span>Back</span>
        </button>
    </div>

    <script>
        (() => {
            const installPageBack = () => {
                const container = document.querySelector('[data-page-back-container]');
                const button = document.querySelector('[data-page-back]');
                const main = document.querySelector('main');

                if (!container || !button || !main || !@json($showPageBack)) return;

                main.prepend(container);
                container.hidden = false;

                let hasUnsavedChanges = false;
                const editableForms = [...main.querySelectorAll('form')].filter((form) =>
                    (form.getAttribute('method') || 'GET').toUpperCase() !== 'GET'
                    && !form.hasAttribute('data-ignore-unsaved-warning')
                );

                editableForms.forEach((form) => {
                    form.addEventListener('input', () => { hasUnsavedChanges = true; });
                    form.addEventListener('change', () => { hasUnsavedChanges = true; });
                    form.addEventListener('submit', () => { hasUnsavedChanges = false; });
                });

                button.addEventListener('click', () => {
                    if (hasUnsavedChanges && !window.confirm('Leave this page? Unsaved changes will be lost.')) {
                        return;
                    }

                    let referrer = null;

                    try {
                        referrer = document.referrer ? new URL(document.referrer) : null;
                    } catch (error) {
                        referrer = null;
                    }

                    const authPaths = ['/login', '/register'];
                    const hasSafeHistory = window.history.length > 1
                        && referrer
                        && referrer.origin === window.location.origin
                        && referrer.href !== window.location.href
                        && !authPaths.includes(referrer.pathname);

                    if (hasSafeHistory) {
                        window.history.back();
                        return;
                    }

                    window.location.assign(@json(route('dashboard')));
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', installPageBack, { once: true });
            } else {
                installPageBack();
            }
        })();
    </script>
@endonce

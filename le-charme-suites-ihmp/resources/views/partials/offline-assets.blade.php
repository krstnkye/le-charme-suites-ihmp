<link rel="stylesheet" href="{{ asset('vendor/fonts/google-fonts.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/fonts/material-symbols.css') }}">
<script src="{{ asset('vendor/tailwindcss-3.4.17.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/interactions.css') }}">
<script>
    (() => {
        const updateConnectionNotice = () => {
            let notice = document.getElementById('ihmsConnectionNotice');

            if (navigator.onLine) {
                notice?.remove();
                return;
            }

            if (!notice && document.body) {
                notice = document.createElement('div');
                notice.id = 'ihmsConnectionNotice';
                notice.setAttribute('role', 'status');
                notice.textContent = 'Offline local mode - hotel records remain available';
                notice.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:9999;padding:9px 13px;border:1px solid #d6b36a;border-radius:6px;background:#fff7e6;color:#5b4313;font:700 12px Manrope,sans-serif;box-shadow:0 4px 14px rgba(1,22,45,.16)';
                document.body.appendChild(notice);
            }
        };

        /*
         * Material Symbols render ligature words such as "calendar_month", which screen readers
         * would read aloud. Hide decorative icons, and name icon-only controls that lack a label.
         */
        const labelIconOnlyControls = () => {
            document.querySelectorAll('.material-symbols-outlined:not([aria-hidden])').forEach((icon) => {
                const control = icon.closest('a, button, [role="button"]');
                const iconText = icon.textContent.trim();

                if (control && !control.hasAttribute('aria-label') && !control.hasAttribute('aria-labelledby') && control.textContent.replace(iconText, '').trim() === '' && !control.title) {
                    control.setAttribute('aria-label', iconText.replace(/_/g, ' '));
                }

                icon.setAttribute('aria-hidden', 'true');
            });
        };

        window.addEventListener('DOMContentLoaded', labelIconOnlyControls);
        window.addEventListener('DOMContentLoaded', updateConnectionNotice);
        window.addEventListener('online', updateConnectionNotice);
        window.addEventListener('offline', updateConnectionNotice);
    })();
</script>

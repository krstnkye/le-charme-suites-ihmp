<link rel="stylesheet" href="{{ asset('vendor/fonts/google-fonts.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/fonts/material-symbols.css') }}">
<script src="{{ asset('vendor/tailwindcss-3.4.17.js') }}"></script>
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

        window.addEventListener('DOMContentLoaded', updateConnectionNotice);
        window.addEventListener('online', updateConnectionNotice);
        window.addEventListener('offline', updateConnectionNotice);
    })();
</script>

{{-- IHMP sets only strictly necessary cookies, so this notice informs rather than asks for consent. --}}
<section id="cookie-notice" class="fixed inset-x-0 bottom-0 z-[70] hidden border-t border-[#c5a36a]/50 bg-[#0c1c2e] px-4 py-4 text-white shadow-2xl sm:px-6" role="region" aria-labelledby="cookie-notice-title">
    <div class="ihms-focus-light mx-auto flex max-w-5xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined mt-0.5 text-[#e6cda3]" aria-hidden="true">cookie</span>
            <div>
                <h2 id="cookie-notice-title" class="text-sm font-bold">Essential cookies only</h2>
                <p class="mt-0.5 text-xs leading-5 text-slate-200">This portal uses only the cookies it needs to keep you signed in and protect forms from tampering. There are no advertising, analytics, or third-party tracking cookies. <a href="{{ route('legal.cookies') }}" class="font-bold text-[#e6cda3] underline underline-offset-2 hover:text-white">Read the Cookie Policy</a>.</p>
            </div>
        </div>
        <button type="button" id="cookie-notice-dismiss" class="shrink-0 rounded-lg border border-[#c5a36a]/60 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#112235] hover:bg-[#fcf9f3]">Got it</button>
    </div>
</section>
<script>
    (() => {
        const storageKey = 'ihmp-cookie-notice-dismissed';
        const notice = document.getElementById('cookie-notice');
        let isDismissed = false;

        try {
            isDismissed = localStorage.getItem(storageKey) === '1';
        } catch (error) {
            isDismissed = false;
        }

        if (isDismissed) {
            notice.remove();
            return;
        }

        notice.classList.remove('hidden');
        document.getElementById('cookie-notice-dismiss').addEventListener('click', () => {
            try {
                localStorage.setItem(storageKey, '1');
            } catch (error) {
                // Private browsing can block storage; the notice simply returns on the next visit.
            }
            notice.remove();
        });
    })();
</script>

@extends('layouts.legal')

@section('title', 'Cookie Policy')

@section('content')
    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Essential cookies only</h2>
        <p class="mt-2">Cookies are small files a website stores in your browser. This portal uses only the cookies it needs to work. It does not use advertising, analytics, social media, or any other third-party cookies, so there is nothing to opt in to.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Cookies we set</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[32rem] border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-300 text-slate-700">
                        <th scope="col" class="py-2 pr-3 font-bold">Name</th>
                        <th scope="col" class="py-2 pr-3 font-bold">Purpose</th>
                        <th scope="col" class="py-2 font-bold">Expires</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td class="py-2 pr-3 font-mono">{{ config('session.cookie') }}</td>
                        <td class="py-2 pr-3">Keeps your session, including staff sign-in, between pages.</td>
                        <td class="py-2">After {{ config('session.lifetime') }} minutes of inactivity</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-3 font-mono">XSRF-TOKEN</td>
                        <td class="py-2 pr-3">Protects forms against cross-site request forgery.</td>
                        <td class="py-2">With the session</td>
                    </tr>
                    <tr>
                        <td class="py-2 pr-3 font-mono">remember_web_*</td>
                        <td class="py-2 pr-3">Keeps a staff member signed in, only when they tick "Remember me".</td>
                        <td class="py-2">When the staff member signs out</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Browser storage</h2>
        <p class="mt-2">The portal also stores a few settings in your browser, not on our servers: whether the staff sidebar is collapsed, its scroll position, and whether you dismissed the cookie notice. These contain no personal information.</p>
    </section>

    <section>
        <h2 class="font-display text-xl font-bold text-[#112235]">Managing cookies</h2>
        <p class="mt-2">You can delete or block cookies in your browser settings. If you block the essential cookies above, you will not be able to sign in or submit forms.</p>
    </section>
@endsection

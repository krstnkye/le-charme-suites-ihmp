@php($currentUser = auth()->user())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Messages - Le Charme Suites IHMS</title>
    @include('partials.offline-assets')
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Manrope','sans-serif'], serif: ['Noto Serif','serif'] } } } };</script>
    <style>.material-symbols-outlined{display:inline-block;vertical-align:middle;line-height:1;font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}</style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-[#fcf9f3] font-sans text-[#01162d] antialiased">
    @if ($currentUser->role === 'admin')
        @include('admin.partials.sidebar')
    @elseif ($currentUser->role === 'receptionist')
        @include('partials.front-desk-sidebar', ['active' => 'messages'])
    @elseif ($currentUser->role === 'cashier')
        @include('partials.billing-sidebar', ['active' => 'messages'])
    @else
        @include('partials.housekeeping-sidebar', ['active' => 'messages'])
    @endif

    <div class="min-h-screen {{ $currentUser->role === 'receptionist' ? 'lg:ml-64' : 'lg:ml-72' }}">
        <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-[#e5e2dc] bg-white px-6 py-3 shadow-sm">
            <div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#755a28]">Shared Database Communication</p><h1 class="font-serif text-xl font-bold">Le Charme Suites IHMS</h1></div>
            <div class="text-right"><p class="text-sm font-bold">{{ $currentUser->name }}</p><p class="text-xs capitalize text-slate-500">{{ $currentUser->role === 'cashier' ? 'Billing' : $currentUser->role }}</p></div>
        </header>
        <main class="mx-auto w-full max-w-[1500px] p-5 lg:p-8">@include('messages.panel')</main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Rumah Adat Sumatera Utara')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#FFF9F5] text-[#192235]">

    {{-- NAVBAR --}}
    <header class="bg-[#192235] text-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">

            {{-- LOGO --}}
            <a href="{{ route('home') }}" class="text-xl font-bold tracking-wide">
                RUMAH<span class="text-[#FFA586]">ADAT</span>
            </a>

            {{-- NAVIGATION --}}
            <div class="flex gap-8 text-sm font-medium">

                <a href="{{ route('home') }}" class="transition hover:text-[#FFA586]">
                    Beranda
                </a>

                <a href="{{ route('search') }}" class="transition hover:text-[#FFA586]">
                    Jelajahi
                </a>

                <a href="{{ route('graph') }}" class="transition hover:text-[#FFA586] {{ request()->routeIs('graph') ? 'text-[#FFA586]' : '' }}">
                    Knowledge Graph
                </a>

            </div>

        </nav>
    </header>

    {{-- PAGE CONTENT --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-16 bg-[#192235] px-6 py-8 text-center text-sm text-white/70">
        <p>
            &copy; {{ date('Y') }} Rumah Adat Sumatera Utara.
            Eksplorasi Warisan Budaya Berbasis Semantic Web.
        </p>
    </footer>

</body>
</html>
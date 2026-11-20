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

            <a href="/" class="text-xl font-bold tracking-wide">
                RUMAH<span class="text-[#FFA586]">ADAT</span>
            </a>

            <div class="flex gap-8 text-sm font-medium">
                <a href="/" class="hover:text-[#FFA586]">
                    Beranda
                </a>

                <a href="/search" class="hover:text-[#FFA586]">
                    Jelajahi
                </a>
            </div>

        </nav>
    </header>

    {{-- KONTEN HALAMAN --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-16 bg-[#192235] px-6 py-8 text-center text-sm text-white/70">
        <p>
            © {{ date('Y') }} Rumah Adat Sumatera Utara.
            Eksplorasi Warisan Budaya Berbasis Semantic Web.
        </p>
    </footer>

</body>
</html>
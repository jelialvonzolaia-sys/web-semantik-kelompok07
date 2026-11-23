@extends('layouts.app')

@section('title', $house['name'] . ' | Rumah Adat Sumatera Utara')

@section('content')

{{-- HERO DETAIL --}}
<section class="bg-[#192235] px-6 py-16 text-white">
    <div class="mx-auto max-w-7xl">

        <a href="{{ route('search') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-[#FFA586] transition hover:text-white">
            &larr; Kembali ke Jelajahi
        </a>

        <div class="mt-12 max-w-4xl">

            <span class="inline-flex rounded-full border border-[#FFA586]/40 bg-[#561A2E]/50 px-4 py-2 text-xs font-semibold tracking-widest text-[#FFA586]">
                {{ strtoupper($house['tag']) }}
            </span>

            <h1 class="mt-6 text-4xl font-bold leading-tight md:text-6xl">
                {{ $house['name'] }}
            </h1>

            <p class="mt-6 max-w-3xl text-base leading-relaxed text-white/75 md:text-lg">
                {{ $house['description'] }}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <span class="rounded-full bg-white/10 px-4 py-2 text-sm text-white">
                    {{ $house['tribe'] }}
                </span>

                <span class="rounded-full bg-white/10 px-4 py-2 text-sm text-white">
                    {{ $house['region'] }}
                </span>
            </div>

        </div>
    </div>
</section>


{{-- DETAIL CONTENT --}}
<section class="mx-auto max-w-7xl px-6 py-14">

    <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">

        {{-- IDENTITAS RUMAH ADAT --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">

            <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                PROFIL BUDAYA
            </span>

            <h2 class="mt-2 text-2xl font-bold text-[#192235]">
                Identitas Rumah Adat
            </h2>

            <p class="mt-3 text-sm leading-relaxed text-gray-500">
                Informasi dasar mengenai identitas dan asal-usul rumah adat.
            </p>

            <div class="mt-8 grid gap-7 sm:grid-cols-2">

                <div>
                    <p class="text-sm text-gray-500">Nama Rumah Adat</p>
                    <p class="mt-2 font-semibold text-[#192235]">
                        {{ $house['name'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Suku</p>
                    <p class="mt-2 font-semibold text-[#192235]">
                        {{ $house['tribe'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Wilayah Asal</p>
                    <p class="mt-2 font-semibold text-[#192235]">
                        {{ $house['region'] }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Kategori</p>
                    <p class="mt-2 font-semibold text-[#192235]">
                        {{ $house['tag'] }}
                    </p>
                </div>

            </div>
        </div>


        {{-- INFORMASI SEMANTIK --}}
        <div class="rounded-2xl border border-[#FFA586]/40 bg-[#FFA586]/10 p-8">

            <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                SEMANTIC INFORMATION
            </span>

            <h2 class="mt-2 text-2xl font-bold text-[#192235]">
                Keterkaitan Budaya
            </h2>

            <p class="mt-4 text-sm leading-relaxed text-[#384358]">
                Rumah adat merupakan bagian dari identitas budaya masyarakat.
                Informasi rumah adat dapat dihubungkan dengan suku dan wilayah
                asalnya melalui relasi antardata.
            </p>

            <div class="mt-6 space-y-3">

                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs text-gray-500">Entitas Rumah Adat</p>
                    <p class="mt-1 font-semibold text-[#192235]">
                        {{ $house['name'] }}
                    </p>
                </div>

                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs text-gray-500">Terhubung dengan Suku</p>
                    <p class="mt-1 font-semibold text-[#192235]">
                        {{ $house['tribe'] }}
                    </p>
                </div>

                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs text-gray-500">Terhubung dengan Wilayah</p>
                    <p class="mt-1 font-semibold text-[#192235]">
                        {{ $house['region'] }}
                    </p>
                </div>

            </div>

            {{-- KNOWLEDGE GRAPH PLACEHOLDER --}}
            <div class="mt-6 rounded-xl bg-[#192235] p-4 text-white">

                <p class="text-sm font-semibold">
                    Knowledge Graph
                </p>

                <p class="mt-2 text-xs leading-relaxed text-white/60">
                    Visualisasi hubungan antarentitas budaya akan
                    diintegrasikan pada tahap pengembangan berikutnya.
                </p>

                <span class="mt-3 inline-block rounded-full bg-[#FFA586]/20 px-3 py-1 text-xs text-[#FFA586]">
                    Dalam Pengembangan
                </span>

            </div>

        </div>

    </div>


    {{-- DESKRIPSI --}}
    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">

        <span class="text-xs font-bold tracking-widest text-[#B51A28]">
            TENTANG WARISAN BUDAYA
        </span>

        <h2 class="mt-2 text-2xl font-bold text-[#192235]">
            Mengenal {{ $house['name'] }}
        </h2>

        <p class="mt-5 max-w-4xl text-sm leading-8 text-[#384358]">
            {{ $house['description'] }}
        </p>

        <p class="mt-4 text-xs text-gray-400">
            Informasi ini merupakan deskripsi awal dari data demonstrasi
            dan akan dikembangkan berdasarkan inventarisasi serta sumber
            budaya yang telah diverifikasi.
        </p>

    </div>


    {{-- BACK BUTTON --}}
    <div class="mt-10 text-center">

        <a href="{{ route('search') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#B51A28] px-8 py-4 font-semibold text-white transition hover:bg-[#561A2E]">
            &larr; Kembali ke Hasil Pencarian
        </a>

    </div>

</section>

@endsection
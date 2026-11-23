@extends('layouts.app')

@section('title', $house['name'] . ' | Rumah Adat Sumatera Utara')

@section('content')

@php
    $relations = [
        [
            'label' => 'Rumah Adat',
            'value' => $house['name'],
            'type' => 'primary',
        ],
        [
            'label' => 'Suku',
            'value' => $house['tribe'],
            'type' => 'secondary',
        ],
        [
            'label' => 'Wilayah',
            'value' => $house['region'],
            'type' => 'secondary',
        ],
    ];
@endphp

{{-- =====================================================
     1. HERO SECTION
===================================================== --}}
<section class="relative overflow-hidden bg-[#192235] px-6 py-16 text-white md:py-24">

    <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full border border-white/5"></div>
    <div class="absolute -right-10 -top-10 h-60 w-60 rounded-full border border-white/5"></div>

    <div class="relative mx-auto max-w-7xl">

        {{-- BREADCRUMB --}}
        <div class="mb-12 flex flex-wrap items-center gap-2 text-xs text-white/50">
            <a href="{{ route('home') }}" class="transition hover:text-[#FFA586]">
                Beranda
            </a>

            <span>/</span>

            <a href="{{ route('search') }}" class="transition hover:text-[#FFA586]">
                Jelajahi
            </a>

            <span>/</span>

            <span class="text-[#FFA586]">
                {{ $house['name'] }}
            </span>
        </div>

        <div class="grid items-end gap-12 lg:grid-cols-[1fr_300px]">

            <div class="max-w-4xl">

                <span class="inline-flex items-center gap-2 rounded-full border border-[#FFA586]/40 bg-[#561A2E]/50 px-4 py-2 text-xs font-semibold tracking-widest text-[#FFA586]">
                    <span class="h-2 w-2 rounded-full bg-[#FFA586]"></span>
                    CULTURAL HERITAGE PROFILE
                </span>

                <h1 class="mt-8 text-4xl font-bold leading-tight tracking-tight md:text-6xl lg:text-7xl">
                    {{ $house['name'] }}
                </h1>

                <p class="mt-6 max-w-3xl text-base leading-relaxed text-white/65 md:text-lg">
                    {{ $house['description'] }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">

                    <span class="rounded-full bg-white/10 px-5 py-3 text-sm font-medium text-white">
                        {{ $house['tribe'] }}
                    </span>

                    <span class="rounded-full bg-white/10 px-5 py-3 text-sm font-medium text-white">
                        {{ $house['region'] }}
                    </span>

                </div>

            </div>

            {{-- HERO SIDE INFORMATION --}}
            <div class="hidden rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm lg:block">

                <p class="text-xs font-semibold tracking-widest text-[#FFA586]">
                    HERITAGE CATEGORY
                </p>

                <h2 class="mt-4 text-xl font-bold">
                    {{ $house['tag'] }}
                </h2>

                <div class="mt-6 h-px bg-white/10"></div>

                <p class="mt-5 text-xs leading-relaxed text-white/50">
                    Dokumentasi identitas dan keterkaitan budaya rumah adat Sumatera Utara.
                </p>

            </div>

        </div>

        <div class="mt-16 h-px bg-gradient-to-r from-[#FFA586]/70 via-white/10 to-transparent"></div>

        <div class="mt-5 flex items-center justify-between">

            <p class="text-xs tracking-widest text-white/40">
                SUMATERA UTARA CULTURAL HERITAGE
            </p>

            <a href="{{ route('search') }}"
               class="text-sm font-medium text-[#FFA586] transition hover:text-white">
                &larr; Kembali ke Jelajahi
            </a>

        </div>

    </div>

</section>


{{-- =====================================================
     2. MAIN CONTENT
===================================================== --}}
<section class="mx-auto max-w-7xl px-6 py-16">

    <div class="grid gap-8 lg:grid-cols-[1fr_350px]">

        {{-- LEFT CONTENT --}}
        <div class="space-y-8">

            {{-- IDENTITY CARD --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-7 shadow-sm md:p-10">

                <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                    01 — CULTURAL IDENTITY
                </span>

                <h2 class="mt-3 text-2xl font-bold text-[#192235] md:text-3xl">
                    Identitas Rumah Adat
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
                    Informasi dasar mengenai identitas, asal-usul,
                    dan klasifikasi rumah adat.
                </p>

                <div class="mt-9 grid gap-7 sm:grid-cols-2">

                    <div class="border-l-2 border-[#FFA586] pl-5">
                        <p class="text-xs font-medium tracking-wide text-gray-400">
                            NAMA RUMAH ADAT
                        </p>

                        <p class="mt-2 text-lg font-bold text-[#192235]">
                            {{ $house['name'] }}
                        </p>
                    </div>

                    <div class="border-l-2 border-[#FFA586] pl-5">
                        <p class="text-xs font-medium tracking-wide text-gray-400">
                            SUKU
                        </p>

                        <p class="mt-2 text-lg font-bold text-[#192235]">
                            {{ $house['tribe'] }}
                        </p>
                    </div>

                    <div class="border-l-2 border-[#FFA586] pl-5">
                        <p class="text-xs font-medium tracking-wide text-gray-400">
                            WILAYAH ASAL
                        </p>

                        <p class="mt-2 text-lg font-bold text-[#192235]">
                            {{ $house['region'] }}
                        </p>
                    </div>

                    <div class="border-l-2 border-[#FFA586] pl-5">
                        <p class="text-xs font-medium tracking-wide text-gray-400">
                            KATEGORI
                        </p>

                        <p class="mt-2 text-lg font-bold text-[#192235]">
                            {{ $house['tag'] }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- CULTURAL DESCRIPTION --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-7 shadow-sm md:p-10">

                <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                    02 — CULTURAL OVERVIEW
                </span>

                <h2 class="mt-3 text-2xl font-bold text-[#192235] md:text-3xl">
                    Mengenal {{ $house['name'] }}
                </h2>

                <div class="mt-7 rounded-2xl bg-[#FFF9F5] p-6 md:p-8">

                    <div class="mb-5 flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFA586]/25 text-lg text-[#B51A28]">
                            ✦
                        </div>

                        <span class="text-sm font-bold text-[#192235]">
                            Deskripsi Budaya
                        </span>

                    </div>

                    <p class="text-sm leading-8 text-[#384358]">
                        {{ $house['description'] }}
                    </p>

                </div>

                <p class="mt-5 text-xs leading-relaxed text-gray-400">
                    Informasi merupakan deskripsi awal dari data demonstrasi.
                    Pengembangan informasi historis, filosofis, dan arsitektural
                    akan mengikuti hasil inventarisasi dan sumber yang terverifikasi.
                </p>

            </div>

        </div>


        {{-- RIGHT SIDEBAR --}}
        <aside class="space-y-8">

            {{-- SEMANTIC INFORMATION --}}
            <div class="rounded-3xl border border-[#FFA586]/40 bg-[#FFF4ED] p-7">

                <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                    03 — SEMANTIC DATA
                </span>

                <h2 class="mt-3 text-2xl font-bold text-[#192235]">
                    Keterkaitan Budaya
                </h2>

                <p class="mt-3 text-sm leading-relaxed text-[#384358]">
                    Informasi rumah adat dapat dihubungkan dengan
                    entitas budaya lainnya melalui relasi antardata.
                </p>

                <div class="mt-7 space-y-3">

                    @foreach ($relations as $relation)

                        <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">

                            <p class="text-xs font-medium text-gray-400">
                                {{ $relation['label'] }}
                            </p>

                            <p class="mt-2 font-bold text-[#192235]">
                                {{ $relation['value'] }}
                            </p>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- KNOWLEDGE GRAPH --}}
            <div class="overflow-hidden rounded-3xl bg-[#192235] p-7 text-white">

                <span class="text-xs font-bold tracking-widest text-[#FFA586]">
                    04 — KNOWLEDGE GRAPH
                </span>

                <h2 class="mt-3 text-2xl font-bold">
                    Relasi Antarentitas
                </h2>

                <p class="mt-3 text-sm leading-relaxed text-white/55">
                    Gambaran awal hubungan antara rumah adat,
                    suku, dan wilayah.
                </p>

                {{-- RELATION DIAGRAM --}}
                <div class="mt-8 flex flex-col items-center">

                    {{-- NODE 1 --}}
                    <div class="w-full rounded-xl border border-[#FFA586]/40 bg-[#561A2E] p-4 text-center">

                        <p class="text-[10px] font-semibold tracking-widest text-[#FFA586]">
                            RUMAH ADAT
                        </p>

                        <p class="mt-2 text-sm font-bold">
                            {{ $house['name'] }}
                        </p>

                    </div>

                    {{-- CONNECTOR --}}
                    <div class="h-8 w-px bg-[#FFA586]"></div>

                    <div class="rounded-full bg-[#FFA586]/20 px-3 py-1 text-[10px] text-[#FFA586]">
                        Terhubung dengan
                    </div>

                    <div class="h-8 w-px bg-[#FFA586]"></div>

                    {{-- NODE 2 --}}
                    <div class="w-full rounded-xl border border-white/15 bg-white/10 p-4 text-center">

                        <p class="text-[10px] font-semibold tracking-widest text-[#FFA586]">
                            SUKU
                        </p>

                        <p class="mt-2 text-sm font-bold">
                            {{ $house['tribe'] }}
                        </p>

                    </div>

                    {{-- CONNECTOR --}}
                    <div class="h-8 w-px bg-[#FFA586]"></div>

                    <div class="rounded-full bg-[#FFA586]/20 px-3 py-1 text-[10px] text-[#FFA586]">
                        Berasal dari
                    </div>

                    <div class="h-8 w-px bg-[#FFA586]"></div>

                    {{-- NODE 3 --}}
                    <div class="w-full rounded-xl border border-white/15 bg-white/10 p-4 text-center">

                        <p class="text-[10px] font-semibold tracking-widest text-[#FFA586]">
                            WILAYAH
                        </p>

                        <p class="mt-2 text-sm font-bold">
                            {{ $house['region'] }}
                        </p>

                    </div>

                </div>

                <div class="mt-7 rounded-xl border border-white/10 bg-white/5 p-4">

                    <p class="text-xs leading-relaxed text-white/50">
                        Diagram ini merupakan ilustrasi relasi berdasarkan
                        data demonstrasi, bukan hasil query Knowledge Graph.
                    </p>

                    <span class="mt-3 inline-flex rounded-full bg-[#FFA586]/20 px-3 py-1 text-xs font-semibold text-[#FFA586]">
                        Prototype Visualization
                    </span>

                </div>

            </div>

        </aside>

    </div>


    {{-- =================================================
         5. INFORMATION NOTE
    ================================================= --}}
    <div class="mt-10 rounded-2xl border border-gray-200 bg-white p-6 md:p-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FFA586]/20 text-xl text-[#B51A28]">
                i
            </div>

            <div>

                <h3 class="font-bold text-[#192235]">
                    Tentang Informasi dalam Halaman Ini
                </h3>

                <p class="mt-2 text-sm leading-relaxed text-gray-500">
                    Halaman ini menggunakan data demonstrasi untuk menampilkan
                    struktur informasi rumah adat. Informasi tambahan dan
                    visualisasi relasi akan dikembangkan sesuai integrasi
                    dataset ontologi dan hasil validasi sumber budaya.
                </p>

            </div>

        </div>

    </div>


    {{-- =================================================
         6. BOTTOM NAVIGATION
    ================================================= --}}
    <div class="mt-12 flex flex-col items-center justify-between gap-5 border-t border-gray-200 pt-8 sm:flex-row">

        <a href="{{ route('search') }}"
           class="inline-flex items-center gap-3 text-sm font-semibold text-[#384358] transition hover:text-[#B51A28]">

            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200">
                &larr;
            </span>

            Kembali ke Hasil Pencarian

        </a>

        <a href="{{ route('home') }}"
           class="rounded-xl bg-[#B51A28] px-7 py-3 font-semibold text-white transition hover:bg-[#561A2E]">
            Kembali ke Beranda
        </a>

    </div>

</section>

@endsection
@extends('layouts.app')

@section('title', $house['name'] . ' | Rumah Adat Sumatera Utara')

@section('content')

@php
    $relations = array_values(array_filter([
        ['label' => 'Suku', 'values' => $house['suku'] ?? [$house['tribe']], 'filterKey' => 'suku'],
        ['label' => 'Wilayah', 'values' => $house['wilayah'] ?? [$house['region']], 'filterKey' => 'wilayah'],
        ['label' => 'Bahasa Daerah', 'values' => $house['bahasa'] ?? [], 'filterKey' => 'bahasa'],
        ['label' => 'Marga Terkait', 'values' => $house['marga'] ?? [], 'filterKey' => 'marga'],
        ['label' => 'Tradisi Budaya', 'values' => $house['tradisi'] ?? [], 'filterKey' => 'tradisi'],
        ['label' => 'Ornamen Khas', 'values' => $house['ornamen'] ?? [], 'filterKey' => 'ornamen'],
    ], static fn (array $item): bool => $item['values'] !== []));
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

                @if (! empty($house['hasCustomImage']))
                    <div class="mb-5 overflow-hidden rounded-xl border border-white/10">
                        <img
                            src="{{ $house['image'] }}"
                            alt="{{ $house['name'] }}"
                            class="h-44 w-full object-cover"
                        >
                    </div>
                @endif

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
                            NAMA ALTERNATIF
                        </p>

                        <p class="mt-2 text-lg font-bold text-[#192235]">
                            {{ ! empty($house['namaAlternatif']) ? $house['namaAlternatif'] : $house['tag'] }}
                        </p>
                    </div>

                    @if (! empty($house['bentukAtap']))
                        <div class="border-l-2 border-[#FFA586] pl-5">
                            <p class="text-xs font-medium tracking-wide text-gray-400">
                                BENTUK ATAP
                            </p>

                            <p class="mt-2 text-sm leading-relaxed font-semibold text-[#192235]">
                                {{ $house['bentukAtap'] }}
                            </p>
                        </div>
                    @endif

                    @if (! empty($house['bahanBangunan']))
                        <div class="border-l-2 border-[#FFA586] pl-5">
                            <p class="text-xs font-medium tracking-wide text-gray-400">
                                BAHAN BANGUNAN
                            </p>

                            <p class="mt-2 text-sm leading-relaxed font-semibold text-[#192235]">
                                {{ $house['bahanBangunan'] }}
                            </p>
                        </div>
                    @endif

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

                @php
                    $overviewSections = array_filter([
                        'Sejarah & Latar Belakang' => $house['sejarah'] ?? '',
                        'Fungsi Sosial & Adat' => $house['fungsi'] ?? '',
                        'Ciri Arsitektur' => $house['ciriArsitektur'] ?? '',
                        'Makna Filosofis' => $house['maknaFilosofis'] ?? '',
                    ], static fn (string $val): bool => trim($val) !== '');

                    if ($overviewSections === []) {
                        $overviewSections = ['Deskripsi Budaya' => $house['description'] ?? '-'];
                    }
                @endphp

                <div class="mt-7 space-y-5">
                    @foreach ($overviewSections as $heading => $bodyText)
                        <div class="rounded-2xl bg-[#FFF9F5] p-6 md:p-8">

                            <div class="mb-4 flex items-center gap-3">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFA586]/25 text-lg text-[#B51A28]">
                                    ✦
                                </div>

                                <span class="text-sm font-bold text-[#192235]">
                                    {{ $heading }}
                                </span>

                            </div>

                            <p class="text-sm leading-8 text-[#384358]">
                                {{ $bodyText }}
                            </p>

                        </div>
                    @endforeach
                </div>

                @if (! empty($house['referensi']))
                    <p class="mt-5 text-xs leading-relaxed text-gray-500">
                        <span class="font-semibold text-[#192235]">Referensi:</span> {{ $house['referensi'] }}
                    </p>
                @endif

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

                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($relation['values'] as $val)
                                    <a href="{{ route('search', [$relation['filterKey'] => [$val]]) }}"
                                       class="rounded-lg bg-[#FFF4ED] px-2.5 py-1 text-xs font-semibold text-[#561A2E] transition hover:bg-[#B51A28] hover:text-white">
                                        {{ $val }}
                                    </a>
                                @endforeach
                            </div>

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
                    Ringkasan relasi langsung antara rumah adat, suku, wilayah, bahasa, tradisi, ornamen, dan marga.
                </p>

                {{-- RELATION SUMMARY NODES --}}
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

                <div class="mt-6 grid grid-cols-2 gap-2 text-center text-xs">
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-white/50">Marga</p>
                        <p class="mt-1 font-bold text-[#FFA586]">{{ count($house['marga'] ?? []) }}</p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-white/50">Tradisi</p>
                        <p class="mt-1 font-bold text-[#FFA586]">{{ count($house['tradisi'] ?? []) }}</p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-white/50">Ornamen</p>
                        <p class="mt-1 font-bold text-[#FFA586]">{{ count($house['ornamen'] ?? []) }}</p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-white/50">Bahasa</p>
                        <p class="mt-1 font-bold text-[#FFA586]">{{ count($house['bahasa'] ?? []) }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-white/10 bg-white/5 p-4">

                    <p class="text-xs leading-relaxed text-white/60">
                        Data keterkaitan di atas berasal langsung dari ontologi RDF/OWL melalui endpoint SPARQL Fuseki.
                    </p>

                    <a href="{{ route('graph', ['focus' => $house['slug']]) }}"
                       class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-[#B51A28] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#FFA586] hover:text-[#192235]">
                        Lihat di /graph →
                    </a>

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
                    Tentang Data Ontologi Halaman Ini
                </h3>

                <p class="mt-2 text-sm leading-relaxed text-gray-500">
                    Seluruh atribut historis, arsitektural, serta keterkaitan suku, marga, bahasa, tradisi, dan ornamen di halaman ini ditarik secara langsung dari ontologi RDF/OWL melalui Apache Jena Fuseki (SPARQL).
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
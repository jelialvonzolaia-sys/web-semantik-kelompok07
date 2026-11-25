@extends('layouts.app')

@section('title', 'Knowledge Graph | Rumah Adat Sumatera Utara')

@section('content')
<section
    id="kg-page"
    data-api-url="{{ route('api.graph') }}"
    data-detail-base="{{ url('/detail') }}"
    data-search-base="{{ route('search') }}"
    data-initial-focus="{{ $focus ?? request('focus', '') }}"
    data-initial-q="{{ $keyword ?? request('q', '') }}"
>
    {{-- HERO GELAP DI BAWAH NAVBAR --}}
    <div class="bg-[#192235] text-white border-t border-white/10">
        <div class="mx-auto max-w-7xl px-6 py-10">
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                Visualisasi Knowledge Graph Budaya
            </h1>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-white/75 sm:text-base">
                Jelajahi keterhubungan semantik antara Rumah Adat, Suku, Marga, Bahasa Daerah, Wilayah, Tradisi, dan Ornamen di Sumatera Utara.
            </p>
        </div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Peringatan Fuseki --}}
        <div id="kg-fuseki-alert" class="mb-6 hidden rounded-2xl border border-[#B51A28]/30 bg-[#FFA586]/20 px-5 py-4 text-sm text-[#192235]">
            <p class="font-bold text-[#B51A28]">Koneksi Apache Jena Fuseki Belum Terdeteksi</p>
            <p id="kg-fuseki-alert-msg" class="mt-1 text-xs text-[#192235]/85">
                Pastikan layanan Fuseki berjalan di localhost:3030 dengan dataset /rumah_adat.
            </p>
        </div>

        {{-- KARTU FILTER & LEGENDA 7 KELAS --}}
        <div class="mb-6 rounded-3xl border border-[#192235]/10 bg-white p-6 shadow-sm">
            <form id="kg-filter-form" class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="kg-focus-select" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[#192235]/60">
                        Fokus Entitas (Subgraf)
                    </label>
                    <select id="kg-focus-select" class="w-full rounded-xl border border-[#192235]/20 bg-[#FFF9F5] px-4 py-2.5 text-sm text-[#192235] focus:border-[#B51A28] focus:bg-white focus:outline-none">
                        <option value="">Tampilkan Seluruh Knowledge Graph</option>
                    </select>
                </div>

                <div class="flex-1">
                    <label for="kg-search-input" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[#192235]/60">
                        Filter Kata Kunci Semantik
                    </label>
                    <input
                        id="kg-search-input"
                        type="text"
                        placeholder="Contoh: Laia, Purba, Karo, Hombo Batu..."
                        class="w-full rounded-xl border border-[#192235]/20 bg-[#FFF9F5] px-4 py-2.5 text-sm text-[#192235] placeholder-[#192235]/50 focus:border-[#B51A28] focus:bg-white focus:outline-none"
                    >
                </div>

                <div class="flex flex-shrink-0 gap-3">
                    <button type="submit" class="rounded-xl bg-[#B51A28] px-8 py-2.5 text-sm font-semibold text-white transition hover:bg-[#991420]">
                        Terapkan
                    </button>
                    <button id="kg-reset-btn" type="button" class="rounded-xl border border-[#192235]/15 bg-[#FFF9F5] px-6 py-2.5 text-sm font-semibold text-[#192235] transition hover:bg-gray-100">
                        Reset
                    </button>
                </div>
            </form>

            {{-- BARIS LEGENDA + BADGE NODE & RELASI --}}
            <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-[#192235]/10 pt-4">
                <div class="flex flex-wrap items-center gap-2 text-xs text-[#192235]">
                    <span class="font-semibold text-[#192235]/65">Legenda 7 Kelas (klik untuk filter):</span>
                    <div id="kg-legend" class="flex flex-wrap gap-2"></div>
                    <button id="kg-show-all-classes" type="button" class="hidden text-xs font-semibold text-[#B51A28] hover:underline">
                        Tampilkan Semua
                    </button>
                </div>

                <div class="flex items-center gap-2 text-xs font-semibold">
                    <span class="rounded-lg border border-[#192235]/10 bg-[#FFF9F5] px-3 py-1.5 text-[#192235]">
                        Node: <strong id="kg-node-count">0</strong>
                    </span>
                    <span class="rounded-lg border border-[#192235]/10 bg-[#FFF9F5] px-3 py-1.5 text-[#192235]">
                        Relasi: <strong id="kg-edge-count" class="text-[#B51A28]">0</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- AREA UTAMA: KANVAS GRAF (KIRI) + DETAIL NODE TERPILIH (KANAN) --}}
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

            {{-- KIRI: KANVAS GRAF --}}
            <div class="overflow-hidden rounded-3xl border border-[#192235]/10 bg-white shadow-sm lg:col-span-8">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#192235]/10 px-5 py-3.5">
                    <span class="text-xs font-semibold text-[#192235]/75">Kanvas Relasi Ontologi RDF</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <button id="kg-zoom-in" type="button" class="rounded-lg border border-[#192235]/15 bg-[#FFF9F5] px-3 py-1.5 text-xs font-medium text-[#192235] transition hover:border-[#B51A28]">
                            + Zoom
                        </button>
                        <button id="kg-zoom-out" type="button" class="rounded-lg border border-[#192235]/15 bg-[#FFF9F5] px-3 py-1.5 text-xs font-medium text-[#192235] transition hover:border-[#B51A28]">
                            - Zoom
                        </button>
                        <button id="kg-fit-btn" type="button" class="rounded-lg border border-[#192235]/15 bg-[#FFF9F5] px-3 py-1.5 text-xs font-medium text-[#192235] transition hover:border-[#B51A28]">
                            Pusatkan
                        </button>
                        <button id="kg-toggle-physics" type="button" class="rounded-lg border border-[#192235]/15 bg-[#FFF9F5] px-3 py-1.5 text-xs font-medium text-[#192235] transition hover:border-[#B51A28]">
                            Jeda Fisika
                        </button>
                    </div>
                </div>

                <div class="relative h-[580px] w-full bg-white">
                    <div id="kg-canvas" class="h-full w-full"></div>
                </div>
                <div class="border-t border-[#192235]/10 bg-[#FFF9F5] px-5 py-2 text-[11px] text-[#192235]/65">
                    <span id="kg-status-text">Memuat graf...</span>
                </div>
            </div>

            {{-- KANAN: DETAIL NODE TERPILIH --}}
            <aside class="lg:col-span-4">
                <div class="space-y-5 rounded-3xl border border-[#192235]/10 bg-white p-6 shadow-sm">
                    <div>
                        <h2 class="text-xl font-bold text-[#192235]">Detail Node Terpilih</h2>
                        <p class="mt-0.5 text-xs text-[#384358]">Informasi properti dan daftar relasi semantik</p>
                    </div>

                    <div id="kg-inspector-empty" class="rounded-2xl border border-dashed border-[#192235]/15 bg-[#FFF9F5] px-4 py-16 text-center text-sm text-[#192235]/60">
                        Klik salah satu node pada graf untuk melihat detail.
                    </div>

                    <div id="kg-inspector-content" class="hidden space-y-5 rounded-2xl border border-[#192235]/10 bg-[#FFF9F5] p-5">
                        <div>
                            <span id="kg-inspector-badge" class="inline-block rounded-full px-3.5 py-1 text-xs font-semibold"></span>
                        </div>

                        <div>
                            <p id="kg-node-label" class="text-xl font-bold leading-snug text-[#192235]"></p>
                            <p id="kg-node-uri" class="mt-1.5 break-all font-mono text-[11px] text-[#192235]/65"></p>
                        </div>

                        <div id="kg-node-actions" class="flex flex-col gap-2.5 pt-1"></div>
                    </div>

                    <div id="kg-relations-section" class="hidden border-t border-[#192235]/10 pt-5">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wider text-[#192235]/70">
                            RELASI TERHUBUNG (<span id="kg-neighbor-count">0</span>)
                        </p>
                        <ul id="kg-neighbor-list" class="max-h-80 space-y-2.5 overflow-y-auto pr-1 text-xs text-[#192235]"></ul>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</section>
@endsection
@extends('layouts.app')

@section('title', 'Jelajahi Rumah Adat | Rumah Adat Sumatera Utara')

@section('content')

{{-- SEARCH HERO --}}
<section class="bg-[#192235] px-6 pb-16 pt-14 text-white">
    <div class="mx-auto max-w-7xl">

        <div class="mb-8 max-w-3xl">
            <span class="mb-4 inline-flex rounded-full border border-[#FFA586]/40 bg-[#561A2E]/50 px-4 py-2 text-xs font-semibold tracking-widest text-[#FFA586]">
                HERITAGE DISCOVERY
            </span>

            <h1 class="text-3xl font-bold leading-tight md:text-5xl">
                Temukan Warisan
                <span class="text-[#FFA586]">Budaya</span>
            </h1>

            <p class="mt-4 max-w-2xl leading-relaxed text-white/70">
                Jelajahi kekayaan rumah adat Sumatera Utara melalui
                pencarian budaya dan hubungan antarentitas.
            </p>
        </div>

        {{-- SEARCH FORM --}}
        <form action="{{ route('search') }}" method="GET"
              class="flex max-w-4xl flex-col gap-3 rounded-2xl bg-white p-3 shadow-xl sm:flex-row">

            <div class="flex flex-1 items-center gap-3 px-4">
                <svg class="h-6 w-6 shrink-0 text-[#384358]"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke-width="2"/>
                    <path d="m21 21-4.35-4.35"
                          stroke-width="2" stroke-linecap="round"/>
                </svg>

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari rumah adat, suku, atau wilayah..."
                    class="w-full py-3 text-sm text-[#192235] outline-none placeholder:text-gray-400"
                >
            </div>

            <button type="submit"
                    class="rounded-xl bg-[#B51A28] px-8 py-4 font-semibold text-white transition hover:bg-[#561A2E]">
                Cari Sekarang
            </button>
        </form>

        {{-- QUICK SEARCH --}}
        <div class="mt-7 flex flex-wrap items-center gap-3">
            <span class="text-sm text-white/60">
                Pencarian populer:
            </span>

            @foreach (['Rumah Bolon', 'Omo Hada', 'Bagas Godang', 'Istana Maimun', 'Laia', 'Maena', 'Gorga'] as $keyword)
                <a href="{{ route('search', ['q' => $keyword]) }}"
                   class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-sm text-white/80 transition hover:border-[#FFA586] hover:text-[#FFA586]">
                    {{ $keyword }}
                </a>
            @endforeach
        </div>

    </div>
</section>


{{-- SEARCH CONTENT --}}
<section class="mx-auto max-w-7xl px-6 py-12">

    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">

        {{-- FILTER SIDEBAR --}}
        <aside class="h-fit rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#192235]">
                    Filter Pencarian
                </h2>

                <a href="{{ route('search') }}"
                   class="text-xs font-semibold text-[#B51A28] hover:underline">
                    Reset
                </a>
            </div>

            <form action="{{ route('search') }}" method="GET">

                {{-- KEEP SEARCH KEYWORD --}}
                <input type="hidden" name="q" value="{{ request('q') }}">

                @php
                    $sukuOptions = collect($filterOptions['suku'] ?? [])->pluck('label')->all();
                    if ($sukuOptions === []) {
                        $sukuOptions = ['Batak Toba', 'Karo', 'Simalungun', 'Mandailing', 'Nias'];
                    }

                    $wilayahOptions = collect($filterOptions['wilayah'] ?? [])->pluck('label')->all();
                    if ($wilayahOptions === []) {
                        $wilayahOptions = ['Samosir', 'Karo', 'Simalungun', 'Toba', 'Dairi', 'Nias'];
                    }

                    $tradisiOptions = collect($filterOptions['tradisi'] ?? [])->pluck('label')->all();
                    $ornamenOptions = collect($filterOptions['ornamen'] ?? [])->pluck('label')->all();
                    $bahasaOptions = collect($filterOptions['bahasa'] ?? [])->pluck('label')->all();
                    $selectedMargaInput = collect((array) request('marga', []))->first() ?? '';
                @endphp

                {{-- FILTER MARGA --}}
                <div class="border-b border-gray-100 pb-6">

                    <label for="filter-marga" class="mb-3 block font-semibold text-[#192235]">
                        Marga
                    </label>

                    <input
                        id="filter-marga"
                        type="text"
                        name="marga[]"
                        value="{{ $selectedMargaInput }}"
                        placeholder="Contoh: Laia, Harahap, Ginting..."
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-[#192235] outline-none focus:border-[#B51A28]"
                    >

                    @foreach (array_slice((array) request('marga', []), 1) as $extraMarga)
                        <input type="hidden" name="marga[]" value="{{ $extraMarga }}">
                    @endforeach

                </div>

                {{-- FILTER SUKU --}}
                <div class="border-b border-gray-100 py-6">

                    <h3 class="mb-4 font-semibold text-[#192235]">
                        Suku
                    </h3>

                    <div class="max-h-52 space-y-2 overflow-y-auto pr-1">
                        @foreach ($sukuOptions as $item)

                            <label class="flex cursor-pointer items-center gap-3 text-sm text-[#384358]">

                                <input
                                    type="checkbox"
                                    name="suku[]"
                                    value="{{ $item }}"
                                    @checked(in_array($item, (array) request('suku', [])))
                                    class="h-4 w-4 shrink-0 accent-[#B51A28]"
                                >

                                <span>{{ $item }}</span>

                            </label>

                        @endforeach
                    </div>

                </div>


                {{-- FILTER WILAYAH --}}
                <div class="border-b border-gray-100 py-6">

                    <h3 class="mb-4 font-semibold text-[#192235]">
                        Wilayah
                    </h3>

                    <div class="max-h-52 space-y-2 overflow-y-auto pr-1">
                        @foreach ($wilayahOptions as $item)

                            <label class="flex cursor-pointer items-center gap-3 text-sm text-[#384358]">

                                <input
                                    type="checkbox"
                                    name="wilayah[]"
                                    value="{{ $item }}"
                                    @checked(in_array($item, (array) request('wilayah', [])))
                                    class="h-4 w-4 shrink-0 accent-[#B51A28]"
                                >

                                <span>{{ $item }}</span>

                            </label>

                        @endforeach
                    </div>

                </div>

                @if ($tradisiOptions !== [])
                    {{-- FILTER TRADISI --}}
                    <div class="border-b border-gray-100 py-6">

                        <h3 class="mb-4 font-semibold text-[#192235]">
                            Tradisi
                        </h3>

                        <div class="max-h-44 space-y-2 overflow-y-auto pr-1">
                            @foreach ($tradisiOptions as $item)
                                <label class="flex cursor-pointer items-center gap-3 text-sm text-[#384358]">
                                    <input
                                        type="checkbox"
                                        name="tradisi[]"
                                        value="{{ $item }}"
                                        @checked(in_array($item, (array) request('tradisi', [])))
                                        class="h-4 w-4 shrink-0 accent-[#B51A28]"
                                    >
                                    <span>{{ $item }}</span>
                                </label>
                            @endforeach
                        </div>

                    </div>
                @endif

                @if ($ornamenOptions !== [])
                    {{-- FILTER ORNAMEN --}}
                    <div class="border-b border-gray-100 py-6">

                        <h3 class="mb-4 font-semibold text-[#192235]">
                            Ornamen
                        </h3>

                        <div class="max-h-44 space-y-2 overflow-y-auto pr-1">
                            @foreach ($ornamenOptions as $item)
                                <label class="flex cursor-pointer items-center gap-3 text-sm text-[#384358]">
                                    <input
                                        type="checkbox"
                                        name="ornamen[]"
                                        value="{{ $item }}"
                                        @checked(in_array($item, (array) request('ornamen', [])))
                                        class="h-4 w-4 shrink-0 accent-[#B51A28]"
                                    >
                                    <span>{{ $item }}</span>
                                </label>
                            @endforeach
                        </div>

                    </div>
                @endif

                @if ($bahasaOptions !== [])
                    {{-- FILTER BAHASA DAERAH --}}
                    <div class="py-6">

                        <h3 class="mb-4 font-semibold text-[#192235]">
                            Bahasa Daerah
                        </h3>

                        <div class="max-h-44 space-y-2 overflow-y-auto pr-1">
                            @foreach ($bahasaOptions as $item)
                                <label class="flex cursor-pointer items-center gap-3 text-sm text-[#384358]">
                                    <input
                                        type="checkbox"
                                        name="bahasa[]"
                                        value="{{ $item }}"
                                        @checked(in_array($item, (array) request('bahasa', [])))
                                        class="h-4 w-4 shrink-0 accent-[#B51A28]"
                                    >
                                    <span>{{ $item }}</span>
                                </label>
                            @endforeach
                        </div>

                    </div>
                @else
                    <div class="pt-6"></div>
                @endif


                {{-- APPLY FILTER --}}
                <button type="submit"
                        class="w-full rounded-xl bg-[#192235] px-5 py-3 font-semibold text-white transition hover:bg-[#B51A28]">

                    Terapkan Filter

                </button>

            </form>

        </aside>


        {{-- SEARCH RESULTS --}}
        <div>

            {{-- RESULT HEADER --}}
            <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                <div>

                    <p class="text-sm text-[#384358]">
                        Hasil eksplorasi untuk
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-[#192235]">
                        "{{ request('q') ?: 'Seluruh Rumah Adat' }}"
                    </h2>

                    <p class="mt-2 text-sm text-gray-500">
                        Menampilkan {{ count($houses) }} rumah adat
                    </p>

                </div>

                @if ($fusekiAvailable ?? true)
                    <span class="w-fit rounded-full bg-green-50 px-4 py-2 text-xs font-semibold text-green-700">
                        SPARQL LIVE
                    </span>
                @else
                    <span class="w-fit rounded-full bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-800">
                        FUSEKI OFFLINE
                    </span>
                @endif

            </div>

            @if (! empty($is_corrected))
                <div class="mb-6 rounded-2xl border border-[#FFA586]/60 bg-[#FFF4ED] px-5 py-3.5 text-sm text-[#561A2E]">
                    Menampilkan hasil untuk <strong class="font-bold">"{{ $corrected }}"</strong> (dikoreksi dari <span class="italic">"{{ $original }}"</span>).
                </div>
            @endif

            @if (! ($fusekiAvailable ?? true))
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    {{ $fusekiError ?? 'Server Apache Jena Fuseki belum dapat dihubungi.' }}
                    Pastikan server Fuseki aktif di <code>{{ config('services.fuseki.endpoint') }}</code>.
                </div>
            @endif


            {{-- DYNAMIC RESULT CARDS --}}
            <div class="space-y-5">

                @forelse ($houses as $house)

                    <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                        <div class="flex flex-col justify-between gap-5 md:flex-row">

                            @if (! empty($house['hasCustomImage']))
                                <div class="w-full shrink-0 overflow-hidden rounded-xl md:w-44">
                                    <img
                                        src="{{ $house['image'] }}"
                                        alt="{{ $house['name'] }}"
                                        class="h-40 w-full object-cover md:h-full"
                                    >
                                </div>
                            @endif

                            {{-- HOUSE INFORMATION --}}
                            <div class="flex-1">

                                <div class="mb-3 flex flex-wrap items-center gap-2">

                                    <span class="rounded-full bg-[#FFA586]/20 px-3 py-1 text-xs font-semibold text-[#561A2E]">
                                        {{ $house['tag'] }}
                                    </span>

                                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                        Terdaftar
                                    </span>

                                    @if (request()->filled('q') && ! empty($house['relevanceScore']))
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-[#192235]">
                                            Skor {{ $house['relevanceScore'] }}
                                        </span>
                                    @endif

                                </div>


                                <h3 class="text-xl font-bold text-[#192235] transition group-hover:text-[#B51A28]">

                                    {{ $house['name'] }}

                                </h3>


                                {{-- TRIBE AND REGION --}}
                                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-[#384358]">

                                    <span>
                                        ◈ {{ $house['tribe'] }}
                                    </span>

                                    <span>
                                        ⌖ {{ $house['region'] }}
                                    </span>

                                </div>


                                {{-- DESCRIPTION --}}
                                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-600">

                                    {{ $house['description'] }}

                                </p>


                                {{-- SEMANTIC RELATIONS / MATCH PATHS --}}
                                <div class="mt-5 flex flex-wrap items-center gap-2">

                                    <span class="text-xs font-medium text-[#384358]">
                                        {{ ! empty($house['matchPaths']) ? 'Alasan kecocokan:' : 'Relasi budaya:' }}
                                    </span>

                                    @if (! empty($house['matchPaths']))
                                        @foreach (array_slice($house['matchPaths'], 0, 4) as $path)
                                            <span class="rounded-lg bg-[#FFA586]/20 px-3 py-1 text-xs font-medium text-[#561A2E]">
                                                {{ $path }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="rounded-lg bg-gray-100 px-3 py-1 text-xs text-[#384358]">
                                            {{ $house['tribe'] }}
                                        </span>

                                        <span class="rounded-lg bg-gray-100 px-3 py-1 text-xs text-[#384358]">
                                            {{ $house['region'] }}
                                        </span>
                                    @endif

                                </div>

                            </div>


                            {{-- DETAIL BUTTON --}}
                            <div class="flex shrink-0 items-end">

                                <a href="{{ route('detail', ['slug' => $house['slug']]) }}"
                                   class="inline-block rounded-xl bg-[#192235] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#B51A28]">

                                    Lihat Detail →

                                </a>

                            </div>

                        </div>

                    </article>

                @empty

                    {{-- EMPTY SEARCH RESULT --}}
                    <div class="rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center shadow-sm">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#FFA586]/20 text-3xl text-[#B51A28]">
                            ⌕
                        </div>

                        <h3 class="mt-5 text-xl font-bold text-[#192235]">
                            Rumah Adat Tidak Ditemukan
                        </h3>

                        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                            Maaf, kami belum menemukan rumah adat yang sesuai
                            dengan kata kunci atau filter yang kamu pilih.
                            Coba gunakan kata kunci lain.
                        </p>

                        <a href="{{ route('search') }}"
                           class="mt-6 inline-block rounded-xl bg-[#B51A28] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#561A2E]">

                            Lihat Semua Rumah Adat

                        </a>

                    </div>

                @endforelse

            </div>


            {{-- SEMANTIC SEARCH INFORMATION --}}
            <div class="mt-8 rounded-2xl border border-[#FFA586]/40 bg-[#FFA586]/10 p-6">

                <div class="flex items-start gap-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#561A2E] text-xl text-white">
                        ◈
                    </div>

                    <div>

                        <h3 class="font-bold text-[#192235]">
                            Eksplorasi Berbasis Relasi Budaya
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed text-[#384358]">
                            Website ini dirancang untuk menghubungkan informasi
                            rumah adat dengan suku, marga, wilayah, bahasa,
                            tradisi, dan ornamen melalui teknologi Semantic Web.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection
@extends('layouts.app')

@section('title', 'Beranda | Rumah Adat Sumatera Utara')

@section('content')

{{-- =========================================================
     1. HERO SECTION
========================================================= --}}
<section
    class="relative z-30 flex min-h-[680px] items-center justify-center bg-[#192235] px-6 py-24 text-center text-white md:min-h-[760px]"
    style="background-image: url('{{ asset('images/rumah-adat/hero-rumah-adat.jpeg') }}');
           background-size: cover;
           background-position: center;"
>

    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        {{-- DARK OVERLAY --}}
        <div class="absolute inset-0 bg-[#192235]/55"></div>

        {{-- GRADIENT OVERLAY --}}
        <div class="absolute inset-0 bg-gradient-to-t from-[#192235]/90 via-transparent to-[#192235]/40"></div>
    </div>

    <div class="relative z-30 mx-auto w-full max-w-5xl">

        <span class="mb-7 inline-flex items-center rounded-full border border-[#FFA586]/40 bg-[#561A2E]/60 px-5 py-2 text-sm font-medium tracking-wide text-[#FFA586]">
            Eksplorasi Warisan Budaya Sumatera Utara
        </span>

        <h1 class="mx-auto mb-7 max-w-4xl text-4xl font-bold leading-tight tracking-tight md:text-6xl lg:text-7xl">
            Jelajahi Warisan
            <span class="text-[#FFA586]">Rumah Adat</span>
            Sumatera Utara
        </h1>

        <p class="mx-auto mb-11 max-w-2xl text-base leading-relaxed text-white/85 md:text-lg">
            Temukan kekayaan arsitektur, sejarah, filosofi,
            dan keterkaitan budaya rumah adat Sumatera Utara
            melalui teknologi pencarian berbasis semantik.
        </p>

        {{-- SEARCH BAR + LIVE DROPDOWN --}}
        <div id="home-search-wrapper" class="relative z-40 mx-auto w-full max-w-3xl">
            <form
                action="{{ route('search') }}"
                method="GET"
                role="search"
                class="flex w-full flex-col gap-3 rounded-2xl bg-white p-3 shadow-2xl sm:flex-row"
            >

                <div class="flex min-w-0 flex-1 items-center gap-3 px-4">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#384358"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>

                    <input
                        id="home-live-search-input"
                        type="search"
                        name="q"
                        autocomplete="off"
                        aria-label="Cari rumah adat"
                        placeholder="Cari rumah adat, suku, marga, wilayah, atau singkatan (mis. madina, tapteng)..."
                        class="w-full min-w-0 py-3 text-base text-[#192235] outline-none placeholder:text-gray-400"
                    >

                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-[#B51A28] px-8 py-4 font-semibold text-white transition duration-300 hover:bg-[#561A2E]"
                >
                    Jelajahi Sekarang
                </button>

            </form>

            {{-- LIVE INSTANT DROPDOWN --}}
            <div
                id="home-live-dropdown"
                class="hidden absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white text-left text-[#192235] shadow-2xl"
            ></div>
        </div>

        {{-- POPULAR SEARCH --}}
        <div class="mt-7 flex flex-wrap items-center justify-center gap-3">

            <span class="text-sm text-white/70">
                Pencarian populer:
            </span>

            @foreach (['Batak Toba', 'Karo', 'Simalungun', 'Nias', 'Mandailing', 'Melayu Deli'] as $keyword)
                <a
                    href="{{ route('search', ['q' => $keyword]) }}"
                    class="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-sm text-white backdrop-blur-sm transition hover:border-[#FFA586] hover:bg-[#FFA586]/20"
                >
                    {{ $keyword }}
                </a>
            @endforeach

        </div>

    </div>

    {{-- BOTTOM DECORATIVE LINE --}}
    <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-[#561A2E] via-[#FFA586] to-[#B51A28]"></div>

</section>


{{-- =========================================================
     2. DATASET STATISTICS
========================================================= --}}
<section class="relative z-20 mx-auto -mt-10 max-w-6xl px-6">

    @php
        $statistics = [
            [
                'number' => $stats['RumahAdat'] ?? 0,
                'label' => 'Rumah Adat',
                'description' => 'Warisan arsitektur',
            ],
            [
                'number' => $stats['Suku'] ?? 0,
                'label' => 'Suku',
                'description' => 'Identitas masyarakat',
            ],
            [
                'number' => $stats['Wilayah'] ?? 0,
                'label' => 'Wilayah',
                'description' => 'Persebaran budaya',
            ],
            [
                'number' => $stats['Marga'] ?? 0,
                'label' => 'Marga',
                'description' => 'Hubungan kekerabatan',
            ],
        ];
    @endphp

    <div class="grid grid-cols-2 gap-4 rounded-3xl border border-gray-100 bg-white p-6 shadow-xl md:grid-cols-4 md:p-8">

        @foreach ($statistics as $stat)

            <div class="flex flex-col items-center justify-center border-gray-100 p-4 text-center md:border-r last:border-r-0">

                <h3 class="text-3xl font-bold text-[#192235] md:text-4xl">
                    {{ $stat['number'] }}
                    <span class="text-[#B51A28]">+</span>
                </h3>

                <p class="mt-2 font-semibold text-[#192235]">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    {{ $stat['description'] }}
                </p>

            </div>

        @endforeach

    </div>

    <p class="mt-3 text-center text-xs text-gray-400">
        Statistik real-time dari endpoint Apache Jena Fuseki (ontologi rumah adat Sumatera Utara).
    </p>

    @if (! ($fusekiAvailable ?? true))
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-center text-xs text-amber-800">
            {{ $fusekiError ?? 'Server Apache Jena Fuseki belum dapat dihubungi.' }}
            Pastikan Fuseki berjalan di <code>{{ config('services.fuseki.endpoint') }}</code>.
        </div>
    @endif

</section>


{{-- =========================================================
     3. QUICK EXPLORE WILAYAH
========================================================= --}}
<section class="mx-auto max-w-7xl px-6 py-20">

    <div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">

        <div>
            <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                EXPLORE BY REGION
            </span>

            <h2 class="mt-3 text-3xl font-bold text-[#192235] md:text-4xl">
                Jelajahi Berdasarkan Wilayah
            </h2>

            <p class="mt-3 max-w-2xl text-[#384358]">
                Temukan kekayaan rumah adat dan identitas budaya
                dari berbagai wilayah di Sumatera Utara.
            </p>
        </div>

        <a
            href="{{ route('search') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-[#B51A28] transition hover:text-[#561A2E]"
        >
            Jelajahi Semua
            <span>→</span>
        </a>

    </div>

    @php
        $regions = ! empty($featuredRegions)
            ? collect($featuredRegions)->values()->map(function (array $region, int $index): array {
                return [
                    'name' => $region['name'],
                    'description' => $region['count'] . ' rumah adat terdaftar di ontologi',
                    'keyword' => $region['keyword'],
                    'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                ];
            })->all()
            : [
                [
                    'name' => 'Kabupaten Langkat',
                    'description' => 'Balai adat dan rumah Melayu pesisir',
                    'keyword' => 'Kabupaten Langkat',
                    'number' => '01',
                ],
                [
                    'name' => 'Kabupaten Karo',
                    'description' => 'Arsitektur rumah komunal Siwaluh Jabu',
                    'keyword' => 'Kabupaten Karo',
                    'number' => '02',
                ],
                [
                    'name' => 'Kabupaten Mandailing Natal',
                    'description' => 'Bagas Godang dan Sopo Godang klasik',
                    'keyword' => 'Kabupaten Mandailing Natal',
                    'number' => '03',
                ],
                [
                    'name' => 'Kabupaten Nias Selatan',
                    'description' => 'Omo Hada & Omo Sebua tahan gempa',
                    'keyword' => 'Kabupaten Nias Selatan',
                    'number' => '04',
                ],
                [
                    'name' => 'Kabupaten Toba',
                    'description' => 'Warisan Rumah Bolon pesisir Danau Toba',
                    'keyword' => 'Kabupaten Toba',
                    'number' => '05',
                ],
                [
                    'name' => 'Kabupaten Samosir',
                    'description' => 'Pusat perkampungan Huta Batak Toba',
                    'keyword' => 'Kabupaten Samosir',
                    'number' => '06',
                ],
            ];
    @endphp

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

        @foreach ($regions as $region)

            <a
                href="{{ route('search', ['wilayah' => [$region['keyword']]]) }}"
                class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#FFA586] hover:shadow-xl"
            >

                <div class="flex items-start justify-between">

                    <span class="text-sm font-bold tracking-widest text-[#B51A28]">
                        {{ $region['number'] }}
                    </span>

                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FFA586]/20 text-xl text-[#B51A28] transition group-hover:bg-[#B51A28] group-hover:text-white">
                        →
                    </span>

                </div>

                <h3 class="mt-8 text-2xl font-bold text-[#192235] transition group-hover:text-[#B51A28]">
                    {{ $region['name'] }}
                </h3>

                <p class="mt-2 text-sm text-[#384358]">
                    {{ $region['description'] }}
                </p>

                <div class="mt-7 h-1 w-12 rounded-full bg-[#FFA586] transition-all duration-300 group-hover:w-24"></div>

            </a>

        @endforeach

    </div>

</section>


{{-- =========================================================
     4. DISCOVER CULTURAL HERITAGE
========================================================= --}}
<section class="bg-white py-20">

    <div class="mx-auto max-w-7xl px-6">

        <div class="mb-12 text-center">

            <span class="text-xs font-bold tracking-widest text-[#B51A28]">
                CULTURAL DISCOVERY
            </span>

            <h2 class="mt-3 text-3xl font-bold text-[#192235] md:text-4xl">
                Jelajahi Kekayaan Budaya
            </h2>

            <p class="mx-auto mt-4 max-w-2xl text-[#384358]">
                Lebih dari sekadar bangunan, rumah adat menyimpan
                sejarah, identitas, dan filosofi kehidupan masyarakat.
            </p>

        </div>

        @php
            $discoveries = [
                [
                    'number' => '01',
                    'title' => 'Arsitektur Tradisional',
                    'description' => 'Pelajari keunikan bentuk bangunan, struktur, material, dan karakteristik atap rumah adat.',
                    'keyword' => 'Arsitektur',
                ],
                [
                    'number' => '02',
                    'title' => 'Tradisi & Upacara Adat',
                    'description' => 'Temukan tradisi seperti Maena, Hombo Batu, dan Mangulosi yang melekat pada tiap rumah adat.',
                    'keyword' => 'Maena',
                ],
                [
                    'number' => '03',
                    'title' => 'Ornamen & Kekerabatan Marga',
                    'description' => 'Telusuri hubungan antara ukiran ornamen seperti Gorga, marga kerabat, bahasa, dan wilayah.',
                    'keyword' => 'Gorga',
                ],
            ];
        @endphp

        <div class="grid gap-6 md:grid-cols-3">

            @foreach ($discoveries as $item)

                <a
                    href="{{ route('search', ['q' => $item['keyword']]) }}"
                    class="group rounded-2xl border border-gray-100 bg-[#FFF9F5] p-8 transition duration-300 hover:-translate-y-1 hover:border-[#FFA586] hover:shadow-lg"
                >

                    <span class="text-sm font-bold tracking-widest text-[#B51A28]">
                        {{ $item['number'] }}
                    </span>

                    <h3 class="mt-6 mb-3 text-xl font-bold text-[#192235] transition group-hover:text-[#B51A28]">
                        {{ $item['title'] }}
                    </h3>

                    <p class="text-sm leading-relaxed text-[#384358]">
                        {{ $item['description'] }}
                    </p>

                    <span class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-[#B51A28]">
                        Mulai eksplorasi
                        <span class="transition group-hover:translate-x-1">→</span>
                    </span>

                </a>

            @endforeach

        </div>

    </div>

</section>


{{-- =========================================================
     5. CALL TO ACTION
========================================================= --}}
<section class="px-6 py-20">

    <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-[#192235] px-8 py-14 text-center text-white shadow-xl md:px-16">

        <span class="text-xs font-bold tracking-widest text-[#FFA586]">
            DISCOVER NORTH SUMATRA HERITAGE
        </span>

        <h2 class="mx-auto mt-5 max-w-3xl text-3xl font-bold leading-tight md:text-4xl">
            Mulai Perjalanan Menjelajahi
            <span class="text-[#FFA586]">Warisan Budaya</span>
        </h2>

        <p class="mx-auto mt-5 max-w-2xl leading-relaxed text-white/70">
            Temukan berbagai rumah adat dan hubungan budaya
            yang membentuk identitas masyarakat Sumatera Utara.
        </p>

        <a
            href="{{ route('search') }}"
            class="mt-8 inline-flex items-center gap-3 rounded-xl bg-[#B51A28] px-8 py-4 font-semibold text-white transition duration-300 hover:bg-[#FFA586] hover:text-[#192235]"
        >
            Mulai Jelajahi
            <span>→</span>
        </a>

    </div>

</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('home-search-wrapper');
        const input = document.getElementById('home-live-search-input');
        const dropdown = document.getElementById('home-live-dropdown');
        const liveEndpoint = @json(route('api.search.live'));
        const searchPageUrl = @json(route('search'));

        if (! wrapper || ! input || ! dropdown) {
            return;
        }

        let debounceTimer = null;
        let activeController = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');

        const hideDropdown = () => {
            dropdown.classList.add('hidden');
        };

        const renderLoading = () => {
            dropdown.innerHTML = `
                <div class="px-5 py-4 text-sm text-[#384358]">
                    Mencari kecocokan semantik di Fuseki...
                </div>
            `;
            dropdown.classList.remove('hidden');
        };

        const renderResults = (payload, rawQuery) => {
            const results = Array.isArray(payload.results) ? payload.results : [];
            const preview = results.slice(0, 6);
            const correction = payload.correction || {};

            let html = '';

            if (payload.fusekiAvailable === false) {
                html += `
                    <div class="border-b border-amber-200 bg-amber-50 px-5 py-3 text-xs text-amber-800">
                        ${escapeHtml(payload.fusekiError || 'Server Apache Jena Fuseki belum dapat dihubungi.')}
                    </div>
                `;
            }

            if (correction.is_corrected) {
                html += `
                    <div class="border-b border-[#FFA586]/40 bg-[#FFF4ED] px-5 py-2.5 text-xs text-[#561A2E]">
                        Menampilkan hasil untuk <strong>"${escapeHtml(correction.corrected_query)}"</strong>
                        (dari <em>"${escapeHtml(correction.original_query)}"</em>)
                    </div>
                `;
            }

            if (preview.length === 0) {
                html += `
                    <div class="px-5 py-6 text-center text-sm text-gray-500">
                        Tidak ditemukan rumah adat untuk kata kunci <strong>"${escapeHtml(rawQuery)}"</strong>.
                    </div>
                `;
            } else {
                html += '<div class="divide-y divide-gray-100">';
                for (const item of preview) {
                    const detailUrl = item.detail_url || `/detail/${encodeURIComponent(item.slug || '')}`;
                    const badgeText = (item.matchPaths && item.matchPaths[0]) ? item.matchPaths[0] : (item.tag || 'Rumah Adat');

                    html += `
                        <a href="${escapeHtml(detailUrl)}"
                           class="flex items-start justify-between gap-4 px-5 py-3.5 transition hover:bg-[#FFF9F5]">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-[#192235]">${escapeHtml(item.name)}</p>
                                <p class="mt-0.5 truncate text-xs text-[#384358]">
                                    ◈ ${escapeHtml(item.tribe)} • ⌖ ${escapeHtml(item.region)}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full bg-[#FFA586]/25 px-2.5 py-1 text-[11px] font-semibold text-[#561A2E]">
                                ${escapeHtml(badgeText)}
                            </span>
                        </a>
                    `;
                }
                html += '</div>';

                const allHref = `${searchPageUrl}?q=${encodeURIComponent(rawQuery)}`;
                html += `
                    <a href="${escapeHtml(allHref)}"
                       class="block border-t border-gray-100 bg-gray-50 px-5 py-3 text-center text-xs font-bold text-[#B51A28] transition hover:bg-[#B51A28] hover:text-white">
                        Lihat semua ${escapeHtml(payload.total ?? results.length)} hasil →
                    </a>
                `;
            }

            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        };

        input.addEventListener('input', () => {
            const q = input.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < 2) {
                if (activeController) {
                    activeController.abort();
                }
                hideDropdown();
                return;
            }

            debounceTimer = setTimeout(async () => {
                if (activeController) {
                    activeController.abort();
                }

                activeController = new AbortController();
                renderLoading();

                try {
                    const url = new URL(liveEndpoint, window.location.origin);
                    url.searchParams.set('q', q);

                    const response = await fetch(url.toString(), {
                        headers: { Accept: 'application/json' },
                        signal: activeController.signal,
                    });

                    if (! response.ok) {
                        hideDropdown();
                        return;
                    }

                    const payload = await response.json();
                    renderResults(payload, q);
                } catch (err) {
                    if (err && err.name !== 'AbortError') {
                        hideDropdown();
                    }
                }
            }, 250);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
                dropdown.classList.remove('hidden');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hideDropdown();
            }
        });

        document.addEventListener('click', (event) => {
            if (! wrapper.contains(event.target)) {
                hideDropdown();
            }
        });
    });
</script>

@endsection
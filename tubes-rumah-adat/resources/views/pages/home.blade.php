@extends('layouts.app')

@section('title', 'Beranda | Rumah Adat Sumatera Utara')

@section('content')

{{-- HERO SECTION --}}
<section
    class="relative flex min-h-[680px] items-center justify-center overflow-hidden bg-[#192235] px-6 py-24 text-center text-white md:min-h-[760px]"
    style="background-image: url('{{ asset('images/rumah-adat/hero-rumah-adat.jpeg') }}');
           background-size: cover;
           background-position: center;"
>

    {{-- DARK OVERLAY --}}
    <div class="absolute inset-0 bg-[#192235]/55"></div>

    {{-- GRADIENT OVERLAY --}}
    <div class="absolute inset-0 bg-gradient-to-t from-[#192235]/90 via-transparent to-[#192235]/40"></div>

    {{-- HERO CONTENT --}}
    <div class="relative z-10 mx-auto w-full max-w-5xl">

        {{-- BADGE --}}
        <span
            class="mb-7 inline-flex items-center rounded-full border border-[#FFA586]/40 bg-[#561A2E]/60 px-5 py-2 text-sm font-medium tracking-wide text-[#FFA586]"
        >
            Eksplorasi Warisan Budaya Sumatera Utara
        </span>

        {{-- TITLE --}}
        <h1 class="mx-auto mb-7 max-w-4xl text-4xl font-bold leading-tight tracking-tight md:text-6xl lg:text-7xl">
            Jelajahi Warisan
            <span class="text-[#FFA586]">
                Rumah Adat
            </span>
            Sumatera Utara
        </h1>

        {{-- DESCRIPTION --}}
        <p class="mx-auto mb-11 max-w-2xl text-base leading-relaxed text-white/85 md:text-lg">
            Temukan kekayaan arsitektur, sejarah, filosofi,
            dan keterkaitan budaya rumah adat Sumatera Utara
            melalui teknologi pencarian berbasis semantik.
        </p>

        {{-- SEARCH BAR --}}
        <form
            action="/search"
            method="GET"
            role="search"
            class="mx-auto flex w-full max-w-3xl flex-col gap-3 rounded-2xl bg-white p-3 shadow-2xl sm:flex-row"
        >

            <div class="flex min-w-0 flex-1 items-center gap-3 px-4">

                {{-- SEARCH ICON --}}
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
                    type="search"
                    name="q"
                    aria-label="Cari rumah adat"
                    placeholder="Cari rumah adat, suku, atau wilayah..."
                    class="w-full min-w-0 py-3 text-base text-[#192235] outline-none placeholder:text-gray-400"
                >

            </div>

            {{-- SEARCH BUTTON --}}
            <button
                type="submit"
                class="rounded-xl bg-[#B51A28] px-8 py-4 font-semibold text-white transition duration-300 hover:bg-[#561A2E]"
            >
                Jelajahi Sekarang
            </button>

        </form>

        {{-- POPULAR SEARCH --}}
        <div class="mt-7 flex flex-wrap items-center justify-center gap-3">

            <span class="text-sm text-white/70">
                Pencarian populer:
            </span>

            @foreach (['Batak Toba', 'Karo', 'Simalungun'] as $keyword)
                <a
                    href="{{ url('/search') . '?' . http_build_query(['q' => $keyword]) }}"
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

<section class="mx-auto max-w-7xl px-6 py-20">

    <div class="mb-10 text-center">
        <h2 class="text-3xl font-bold text-[#192235]">
            Jelajahi Kekayaan Budaya
        </h2>

        <p class="mt-3 text-[#384358]">
            Kenali rumah adat dari berbagai wilayah Sumatera Utara.
        </p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">

        <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
            <h3 class="mb-3 text-xl font-bold">Arsitektur Tradisional</h3>
            <p class="text-[#384358]">
                Pelajari keunikan bentuk dan struktur rumah adat.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
            <h3 class="mb-3 text-xl font-bold">Warisan Budaya</h3>
            <p class="text-[#384358]">
                Temukan nilai budaya dan filosofi di balik rumah adat.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
            <h3 class="mb-3 text-xl font-bold">Pencarian Semantik</h3>
            <p class="text-[#384358]">
                Jelajahi hubungan antara rumah adat, suku, dan wilayah.
            </p>
        </div>

    </div>

</section>

@endsection
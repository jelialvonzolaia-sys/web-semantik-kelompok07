@extends('layouts.app')

@section('title', 'Knowledge Graph | Rumah Adat Sumatera Utara')

@section('content')
<section class="mx-auto max-w-4xl px-6 py-20">
    <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
        <span class="inline-flex rounded-full bg-[#FFA586]/20 px-4 py-1.5 text-xs font-semibold tracking-widest text-[#561A2E]">
            ENDPOINT API SIAP
        </span>

        <h1 class="mt-4 text-3xl font-bold text-[#192235]">
            Halaman Knowledge Graph
        </h1>

        <p class="mt-3 text-sm leading-relaxed text-[#384358]">
            Route <code>/graph</code> dan endpoint JSON
            <code>{{ route('api.graph', array_filter(['focus' => $focus ?? null, 'q' => $keyword ?? null])) }}</code>
            sudah tersedia dari backend Laravel dan siap disambungkan ke visualisasi interaktif (Vis.js / Cytoscape.js).
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('api.graph', array_filter(['focus' => $focus ?? null, 'q' => $keyword ?? null])) }}"
               target="_blank"
               class="rounded-xl bg-[#192235] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#B51A28]">
                Lihat JSON /api/graph →
            </a>

            <a href="{{ route('search') }}"
               class="rounded-xl border border-gray-200 px-5 py-2.5 text-xs font-semibold text-[#192235] transition hover:bg-gray-50">
                Kembali ke Pencarian
            </a>
        </div>
    </div>
</section>
@endsection

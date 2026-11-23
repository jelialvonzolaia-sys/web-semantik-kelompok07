<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RumahAdatController extends Controller
{
    private function getHouses()
    {
        return [
            [
                'slug' => 'rumah-bolon',
                'name' => 'Rumah Bolon',
                'tribe' => 'Batak Toba',
                'region' => 'Samosir, Sumatera Utara',
                'description' => 'Rumah tradisional Batak Toba yang dikenal dengan bentuk atap melengkung menyerupai punggung kerbau.',
                'tag' => 'Arsitektur Tradisional',
            ],
            [
                'slug' => 'siwaluh-jabu',
                'name' => 'Siwaluh Jabu',
                'tribe' => 'Karo',
                'region' => 'Kabupaten Karo, Sumatera Utara',
                'description' => 'Rumah adat Karo yang mencerminkan kehidupan komunal dan hubungan sosial masyarakatnya.',
                'tag' => 'Rumah Komunal',
            ],
            [
                'slug' => 'rumah-bolon-simalungun',
                'name' => 'Rumah Bolon Simalungun',
                'tribe' => 'Simalungun',
                'region' => 'Simalungun, Sumatera Utara',
                'description' => 'Rumah tradisional Simalungun dengan karakter arsitektur dan ornamen khas daerahnya.',
                'tag' => 'Warisan Arsitektur',
            ],
        ];
    }

    // HALAMAN PENCARIAN
    public function index(Request $request)
    {
        $houses = collect($this->getHouses());

        // 1. PENCARIAN KATA KUNCI
        if ($request->filled('q')) {

            $keyword = mb_strtolower(trim($request->q));

            $houses = $houses->filter(function ($house) use ($keyword) {

                return str_contains(
                    mb_strtolower($house['name']),
                    $keyword
                )
                || str_contains(
                    mb_strtolower($house['tribe']),
                    $keyword
                )
                || str_contains(
                    mb_strtolower($house['region']),
                    $keyword
                )
                || str_contains(
                    mb_strtolower($house['description']),
                    $keyword
                );
            });
        }

        // 2. FILTER SUKU
        if ($request->filled('suku')) {

            $selectedTribes = (array) $request->suku;

            $houses = $houses->filter(function ($house) use ($selectedTribes) {

                return in_array($house['tribe'], $selectedTribes);
            });
        }

        // 3. FILTER WILAYAH
        if ($request->filled('wilayah')) {

            $selectedRegions = (array) $request->wilayah;

            $houses = $houses->filter(function ($house) use ($selectedRegions) {

                foreach ($selectedRegions as $region) {

                    if (str_contains(
                        mb_strtolower($house['region']),
                        mb_strtolower($region)
                    )) {
                        return true;
                    }
                }

                return false;
            });
        }

        // KIRIM HASIL KE HALAMAN SEARCH
        return view('pages.search', [
            'houses' => $houses->values()->all(),
        ]);
    }

    // HALAMAN DETAIL RUMAH ADAT
    public function show($slug)
    {
        $houses = $this->getHouses();

        $house = collect($houses)->firstWhere('slug', $slug);

        abort_if(!$house, 404);

        return view('pages.detail', compact('house'));
    }
}
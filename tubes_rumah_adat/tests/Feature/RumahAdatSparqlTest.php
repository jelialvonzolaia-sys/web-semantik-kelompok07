<?php

namespace Tests\Feature;

use App\Services\SparqlService;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RumahAdatSparqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('ontology_dictionary');
    }

    public function test_fuseki_configuration_matches_expected_defaults(): void
    {
        $this->assertSame('http://localhost:3030', config('services.fuseki.base_url'));
        $this->assertSame('rumah_adat', config('services.fuseki.dataset'));
        $this->assertSame('http://localhost:3030/rumah_adat/query', config('services.fuseki.endpoint'));
    }

    public function test_home_displays_dynamic_stats_and_regions_from_fuseki(): void
    {
        Http::fake(function (ClientRequest $request) {
            $sparql = (string) ($request['query'] ?? '');

            if (str_contains($sparql, 'ra:bagianDariWilayah ra:wil_sumatera_utara')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            ['namaWilayah' => ['value' => 'Kabupaten Langkat'], 'jumlah' => ['value' => '4']],
                            ['namaWilayah' => ['value' => 'Kabupaten Toba'], 'jumlah' => ['value' => '3']],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'results' => [
                    'bindings' => [
                        ['tipe' => ['value' => 'RumahAdat'], 'jumlah' => ['value' => '42']],
                        ['tipe' => ['value' => 'Suku'], 'jumlah' => ['value' => '15']],
                        ['tipe' => ['value' => 'Wilayah'], 'jumlah' => ['value' => '61']],
                        ['tipe' => ['value' => 'Marga'], 'jumlah' => ['value' => '107']],
                    ],
                ],
            ], 200);
        });

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('42');
        $response->assertSee('15');
        $response->assertSee('61');
        $response->assertSee('107');
        $response->assertSee('Kabupaten Langkat');
        $response->assertSee('4 rumah adat terdaftar di ontologi');
        $response->assertDontSee('Knowledge Graph');
    }

    public function test_home_gracefully_handles_fuseki_connection_failure(): void
    {
        Http::fake([
            'http://localhost:3030/rumah_adat/query' => fn () => throw new \RuntimeException('Connection refused'),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Apache Jena Fuseki');
    }

    public function test_search_sends_sanitized_query_filters_and_aggregates_match_paths(): void
    {
        $capturedQueries = [];

        Http::fake(function (ClientRequest $request) use (&$capturedQueries) {
            $sparql = (string) ($request['query'] ?? '');
            $capturedQueries[] = $sparql;

            if (str_contains($sparql, '?label ?altLabel')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            ['label' => ['value' => 'Laia']],
                            ['label' => ['value' => 'Suku Nias']],
                            ['label' => ['value' => 'Ginting']],
                        ],
                    ],
                ], 200);
            }

            if (str_contains($sparql, '?kategori ?uri ?label')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            [
                                'kategori' => ['value' => 'suku'],
                                'uri' => ['value' => 'http://example.org/ontology/rumah-adat#suku_nias'],
                                'label' => ['value' => 'Suku Nias'],
                            ],
                            [
                                'kategori' => ['value' => 'wilayah'],
                                'uri' => ['value' => 'http://example.org/ontology/rumah-adat#wil_nias_selatan'],
                                'label' => ['value' => 'Kabupaten Nias Selatan'],
                            ],
                            [
                                'kategori' => ['value' => 'bahasa'],
                                'uri' => ['value' => 'http://example.org/ontology/rumah-adat#bhs_nias'],
                                'label' => ['value' => 'Bahasa Nias'],
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_omo_hada_nias_selatan'],
                            'namaRumah' => ['value' => 'Omo Hada Nias Selatan'],
                            'namaSuku' => ['value' => 'Suku Nias'],
                            'namaWilayah' => ['value' => 'Kabupaten Nias Selatan'],
                            'matchPath' => ['value' => 'Marga langsung: Laia'],
                            'relevanceScore' => ['value' => '95'],
                        ],
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_omo_hada_nias_selatan'],
                            'namaRumah' => ['value' => 'Omo Hada Nias Selatan'],
                            'namaSuku' => ['value' => 'Suku Nias'],
                            'namaWilayah' => ['value' => 'Pulau Nias'],
                            'matchPath' => ['value' => 'Marga langsung: Laia'],
                            'relevanceScore' => ['value' => '95'],
                        ],
                    ],
                ],
            ], 200);
        });

        $response = $this->get('/search?q=Laia');

        $response->assertStatus(200);
        $response->assertSee('Omo Hada Nias Selatan');
        $response->assertSee('Marga langsung: Laia');
        $response->assertSee('Skor 95');
        $response->assertSee('Bahasa Nias');
        $response->assertSee('Menampilkan 1 rumah adat');
        $response->assertViewHas('is_corrected', false);
        $response->assertSee('id="typo-correction-banner"', false);

        $searchSparql = collect($capturedQueries)->first(
            static fn (string $query): bool => str_contains($query, 'BIND(LCASE(')
        );
        $this->assertNotNull($searchSparql);
        $this->assertStringContainsString('BIND(LCASE("Laia") AS ?ql)', $searchSparql);
    }

    public function test_resolve_typo_keyword_caches_tokenized_dictionary_and_computes_hybrid_similarity(): void
    {
        $dictionaryRequests = 0;

        Http::fake(function () use (&$dictionaryRequests) {
            $dictionaryRequests++;

            return Http::response([
                'results' => [
                    'bindings' => [
                        ['label' => ['value' => 'Bahasa Nias (Li Niha)']],
                        ['label' => ['value' => 'Suku Nias']],
                        ['label' => ['value' => 'Suku Batak Toba']],
                        ['label' => ['value' => 'Rumah Bolon Jangga Dolok']],
                        ['label' => ['value' => 'Rumah Siwaluh Jabu Desa Lingga']],
                        ['label' => ['value' => 'Ginting']],
                        ['label' => ['value' => 'Gulö']],
                        ['label' => ['value' => 'Mendröfa']],
                        ['label' => ['value' => 'Mandailing']],
                        ['label' => ['value' => 'Laia']],
                        ['label' => ['value' => 'Maena']],
                        ['label' => ['value' => 'Tradisi']],
                        ['label' => ['value' => 'Kerja']],
                        ['label' => ['value' => 'Omo Hada & Omo Sebua (Umum Nias)']],
                    ],
                ],
            ], 200);
        });

        $service = app(SparqlService::class);
        $dictionary = $service->buildOntologyDictionary();

        $this->assertArrayHasKey('bolon', $dictionary['tokens']);
        $this->assertArrayNotHasKey('rumah', $dictionary['tokens']);
        $this->assertArrayNotHasKey('suku', $dictionary['tokens']);
        $this->assertArrayHasKey('rumah bolon', $dictionary['phrases']);

        $this->assertGreaterThanOrEqual(0.85, $service->calculateHybridSimilarity('nisa', 'Nias'));
        $this->assertGreaterThanOrEqual(0.85, $service->calculateHybridSimilarity('tradiis', 'Tradisi'));
        $this->assertGreaterThanOrEqual(0.68, $service->calculateHybridSimilarity('abtk', 'Batak'));
        $this->assertGreaterThanOrEqual(0.90, $service->calculateHybridSimilarity('ginitng', 'Ginting'));
        $this->assertSame(1.0, $service->calculateHybridSimilarity('laiaa', 'Laia'));
        $this->assertSame(1.0, $service->calculateHybridSimilarity('djaboe', 'Jabu'));
        $this->assertLessThan(0.68, $service->calculateHybridSimilarity('kerbau', 'Kerja'));

        $nisa = $service->resolveTypoKeyword('nisa');
        $this->assertTrue($nisa['is_corrected']);
        $this->assertSame('Nias', $nisa['corrected_query']);

        $abtk = $service->resolveTypoKeyword('abtk');
        $this->assertTrue($abtk['is_corrected']);
        $this->assertSame('Batak', $abtk['corrected_query']);

        $tradiis = $service->resolveTypoKeyword('tradiis');
        $this->assertTrue($tradiis['is_corrected']);
        $this->assertSame('Tradisi', $tradiis['corrected_query']);

        $laiaa = $service->resolveTypoKeyword('laiaa');
        $this->assertTrue($laiaa['is_corrected']);
        $this->assertSame('Laia', $laiaa['corrected_query']);

        $ginitng = $service->resolveTypoKeyword('ginitng');
        $this->assertTrue($ginitng['is_corrected']);
        $this->assertSame('Ginting', $ginitng['corrected_query']);

        $gulo = $service->resolveTypoKeyword('gulo');
        $this->assertTrue($gulo['is_corrected']);
        $this->assertSame('Gulö', $gulo['corrected_query']);

        $multiWord = $service->resolveTypoKeyword('rmah blon btk');
        $this->assertTrue($multiWord['is_corrected']);
        $this->assertSame('Rumah Bolon Batak', $multiWord['corrected_query']);
        $this->assertContains('Rumah Bolon', $multiWord['expansion_terms']);
        $this->assertContains('Batak', $multiWord['expansion_terms']);

        $exactPunct = $service->resolveTypoKeyword('  Maena!! ');
        $this->assertFalse($exactPunct['is_corrected']);
        $this->assertSame('Maena', $exactPunct['corrected_query']);

        $unrelatedSixLetter = $service->resolveTypoKeyword('kerbau');
        $this->assertFalse($unrelatedSixLetter['is_corrected']);
        $this->assertSame('kerbau', $unrelatedSixLetter['corrected_query']);

        $this->assertSame(1, $dictionaryRequests);
        $this->assertTrue(Cache::has('ontology_dictionary'));
    }

    public function test_search_uses_multi_keyword_or_expansion_and_displays_banner_suggestions(): void
    {
        $capturedQueries = [];

        Http::fake(function (ClientRequest $request) use (&$capturedQueries) {
            $sparql = (string) ($request['query'] ?? '');
            $capturedQueries[] = $sparql;

            if (str_contains($sparql, '?label ?altLabel')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            ['label' => ['value' => 'Rumah Bolon Jangga Dolok']],
                            ['label' => ['value' => 'Suku Batak Toba']],
                            ['label' => ['value' => 'Suku Nias']],
                        ],
                    ],
                ], 200);
            }

            if (str_contains($sparql, '?kategori ?uri ?label')) {
                return Http::response(['results' => ['bindings' => []]], 200);
            }

            return Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_huta_batak_tb_silalahi_center'],
                            'namaRumah' => ['value' => 'Huta Batak TB Silalahi Center'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Balige, Kabupaten Toba'],
                            'matchPath' => ['value' => 'Suku: Suku Batak Toba'],
                            'relevanceScore' => ['value' => '85'],
                        ],
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_jangga_dolok'],
                            'namaRumah' => ['value' => 'Rumah Bolon Jangga Dolok'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Kabupaten Toba'],
                            'matchPath' => ['value' => 'Nama rumah adat: Rumah Bolon Jangga Dolok'],
                            'relevanceScore' => ['value' => '100'],
                        ],
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_jangga_dolok'],
                            'namaRumah' => ['value' => 'Rumah Bolon Jangga Dolok'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Kabupaten Toba'],
                            'matchPath' => ['value' => 'Suku: Suku Batak Toba'],
                            'relevanceScore' => ['value' => '85'],
                        ],
                    ],
                ],
            ], 200);
        });

        $response = $this->get('/search?q=rmah+blon+btk');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Rumah Bolon Jangga Dolok', 'Huta Batak TB Silalahi Center']);
        $response->assertSee('Menampilkan hasil untuk', false);
        $response->assertSee('"Rumah Bolon Batak"', false);
        $response->assertSee('(dikoreksi dari', false);
        $response->assertSee('"rmah blon btk"', false);
        $response->assertSee('Saran & ekspansi kata:', false);

        $searchSparql = collect($capturedQueries)->first(
            static fn (string $query): bool => str_contains($query, 'FILTER(CONTAINS(')
        );
        $this->assertNotNull($searchSparql);
        $this->assertStringContainsString('||', $searchSparql);
        $this->assertStringContainsString('LCASE("Rumah Bolon")', $searchSparql);
        $this->assertStringContainsString('LCASE("Batak")', $searchSparql);
    }

    public function test_expand_abbreviation_supports_manual_dynamic_and_typo_abbreviations(): void
    {
        Http::fake([
            'http://localhost:3030/rumah_adat/query' => Http::response([
                'results' => [
                    'bindings' => [
                        ['label' => ['value' => 'Rumah Siwaluh Jabu Desa Lingga']],
                        ['label' => ['value' => 'Bagas Godang Panyabungan Tonga']],
                        ['label' => ['value' => 'Sidang Adat & Musyawarah Dalihan Na Tolu']],
                        ['label' => ['value' => 'Kabupaten Tapanuli Tengah']],
                        ['label' => ['value' => 'Kabupaten Tapanuli Selatan']],
                        ['label' => ['value' => 'Suku Nias']],
                        ['label' => ['value' => 'Suku Batak Karo']],
                    ],
                ],
            ], 200),
        ]);

        $service = app(SparqlService::class);

        $this->assertSame('Tapanuli Tengah', $service->expandAbbreviation('tapteng'));
        $this->assertSame('Tapanuli Selatan', $service->expandAbbreviation('tapsel'));
        $this->assertSame('Mandailing Natal', $service->expandAbbreviation('madina'));
        $this->assertSame('Padang Lawas Utara', $service->expandAbbreviation('paluta'));
        $this->assertSame('Humbang Hasundutan', $service->expandAbbreviation('humbahas'));
        $this->assertSame('Nias Selatan', $service->expandAbbreviation('nisel'));
        $this->assertSame('Sumatera Utara', $service->expandAbbreviation('sumut'));
        $this->assertSame('Dalihan Na Tolu', $service->expandAbbreviation('dnt'));
        $this->assertSame('Rakut Sitelu', $service->expandAbbreviation('rs'));
        $this->assertSame('Sulang Silima', $service->expandAbbreviation('ss'));
        $this->assertSame('Batak Toba', $service->expandAbbreviation('batoba'));
        $this->assertSame('Batak Karo', $service->expandAbbreviation('bkaro'));
        $this->assertSame('Hombo Batu', $service->expandAbbreviation('hombo'));
        $this->assertSame('Saur Matua', $service->expandAbbreviation('saurmatua'));

        // Generator akronim dinamis dari kamus ontologi ("Siwaluh Jabu" -> "sija", "Bagas Godang" -> "bago")
        $this->assertSame('Siwaluh Jabu', $service->expandAbbreviation('sija'));
        $this->assertSame('Bagas Godang', $service->expandAbbreviation('bago'));

        // Typo pada singkatan
        $this->assertSame('Tapanuli Tengah', $service->expandAbbreviation('taptng'));
        $this->assertSame('Tapanuli Selatan', $service->expandAbbreviation('tapsl'));

        // Kata asli entitas tidak diubah
        $this->assertNull($service->expandAbbreviation('Karo'));
        $this->assertNull($service->expandAbbreviation('Nias'));
    }

    public function test_live_search_api_and_views_include_live_search_support(): void
    {
        Http::fake(function (ClientRequest $request) {
            $sparql = (string) ($request['query'] ?? '');

            if (str_contains($sparql, '?label ?altLabel')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            ['label' => ['value' => 'Kabupaten Tapanuli Tengah']],
                            ['label' => ['value' => 'Rumah Adat Pasisi (Rumah Rajo Sorkam Kanan)']],
                        ],
                    ],
                ], 200);
            }

            if (str_contains($sparql, 'ra:bagianDariWilayah ra:wil_sumatera_utara') || str_contains($sparql, '?kategori ?uri ?label') || str_contains($sparql, 'GROUP BY ?tipe')) {
                return Http::response(['results' => ['bindings' => []]], 200);
            }

            return Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_adat_pasisi_rajo_sorkam_kanan'],
                            'namaRumah' => ['value' => 'Rumah Adat Pasisi (Rumah Rajo Sorkam Kanan)'],
                            'namaSuku' => ['value' => 'Etnis Pasisi'],
                            'namaWilayah' => ['value' => 'Sorkam Kanan, Kecamatan Sorkam Barat'],
                            'matchPath' => ['value' => 'Wilayah: Kabupaten Tapanuli Tengah'],
                            'relevanceScore' => ['value' => '70'],
                        ],
                    ],
                ],
            ], 200);
        });

        $liveResponse = $this->getJson('/api/search/live?q=taptng');
        $liveResponse->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('correction.original_query', 'taptng')
            ->assertJsonPath('correction.corrected_query', 'Tapanuli Tengah')
            ->assertJsonPath('correction.is_corrected', true)
            ->assertJsonPath('results.0.name', 'Rumah Adat Pasisi (Rumah Rajo Sorkam Kanan)')
            ->assertJsonPath('results.0.slug', 'rumah-adat-pasisi-rajo-sorkam-kanan');

        $this->assertStringEndsWith('/detail/rumah-adat-pasisi-rajo-sorkam-kanan', (string) $liveResponse->json('results.0.detail_url'));

        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200)
            ->assertSee('home-live-dropdown')
            ->assertSee('api\/search\/live', false);

        $searchPageResponse = $this->get('/search');
        $searchPageResponse->assertStatus(200)
            ->assertSee('live-search-input')
            ->assertSee('search-results-container')
            ->assertSee('api\/search\/live', false);
    }

    public function test_detail_renders_ontology_fields_and_preserves_comma_in_wilayah(): void
    {
        Http::fake([
            'http://localhost:3030/rumah_adat/query' => Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'nama' => ['value' => 'Huta Batak TB Silalahi Center'],
                            'namaAlternatif' => ['value' => 'Ruma Bolon Museum TB Silalahi'],
                            'sejarah' => ['value' => 'Kompleks pelestarian arsitektur Batak Toba di Balige.'],
                            'fungsi' => ['value' => 'Museum budaya dan edukasi warisan Batak.'],
                            'ciriArsitektur' => ['value' => 'Rumah panggung kayu dengan ukiran Gorga khas Batak Toba.'],
                            'bentukAtap' => ['value' => 'Pelana melengkung menyerupai tanduk kerbau.'],
                            'bahanBangunan' => ['value' => 'Kayu keras pilihan dan atap ijuk.'],
                            'maknaFilosofis' => ['value' => 'Simbol keseimbangan kosmos tiga dunia Batak Toba.'],
                            'referensi' => ['value' => 'Dokumentasi Budaya Sumatera Utara'],
                            'suku' => ['value' => 'Suku Batak Toba'],
                            'wilayah' => ['value' => 'Balige, Kabupaten Toba || Kabupaten Toba'],
                            'bahasa' => ['value' => 'Bahasa Batak Toba'],
                            'marga' => ['value' => 'Silalahi || Siahaan'],
                            'tradisi' => ['value' => 'Mangokal Holi'],
                            'ornamen' => ['value' => 'Gorga'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get('/detail/rumah-huta-batak-tb-silalahi-center');

        $response->assertStatus(200);
        $response->assertSee('Huta Batak TB Silalahi Center');
        $response->assertSee('Ruma Bolon Museum TB Silalahi');
        $response->assertSee('Balige, Kabupaten Toba');
        $response->assertSee('Silalahi');
        $response->assertSee('Gorga');
    }

    public function test_detail_returns_404_when_house_is_not_found(): void
    {
        Http::fake([
            'http://localhost:3030/rumah_adat/query' => Http::response([
                'results' => [
                    'bindings' => [],
                ],
            ], 200),
        ]);

        $response = $this->get('/detail/rumah-tidak-ditemukan');

        $response->assertStatus(404);
    }

    public function test_graph_placeholder_and_api_graph_endpoint_are_ready(): void
    {
        Http::fake([
            'http://localhost:3030/rumah_adat/query' => Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'subject' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_istana_maimun_medan'],
                            'subjectLabel' => ['value' => 'Istana Maimun'],
                            'subjectType' => ['value' => 'RumahAdat'],
                            'predicate' => ['value' => 'http://example.org/ontology/rumah-adat#dimilikiOlehSuku'],
                            'predicateLabel' => ['value' => 'dimiliki oleh suku'],
                            'object' => ['value' => 'http://example.org/ontology/rumah-adat#suku_melayu_deli'],
                            'objectLabel' => ['value' => 'Suku Melayu Deli'],
                            'objectType' => ['value' => 'Suku'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $pageResponse = $this->get('/graph');
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('ENDPOINT API SIAP');
        $pageResponse->assertDontSee('vis-network');

        $apiResponse = $this->getJson('/api/graph');
        $apiResponse->assertStatus(200)
            ->assertJsonPath('meta.nodeCount', 2)
            ->assertJsonPath('meta.edgeCount', 1)
            ->assertJsonPath('meta.fusekiAvailable', true)
            ->assertJsonPath('nodes.0.slug', 'rumah-istana-maimun-medan')
            ->assertJsonPath('edges.0.label', 'dimiliki oleh suku');
    }

    public function test_search_cards_have_stretched_link_in_blade_and_js_render_cards(): void
    {
        Http::fake(function (ClientRequest $request) {
            $sparql = (string) ($request['query'] ?? '');

            if (str_contains($sparql, '?label ?altLabel')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            ['label' => ['value' => 'Omo Hada & Omo Sebua (Umum Nias)']],
                        ],
                    ],
                ], 200);
            }

            if (str_contains($sparql, '?kategori ?uri ?label')) {
                return Http::response(['results' => ['bindings' => []]], 200);
            }

            return Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_omo_hada_umum_nias'],
                            'namaRumah' => ['value' => 'Omo Hada & Omo Sebua (Umum Nias)'],
                            'namaSuku' => ['value' => 'Suku Nias'],
                            'namaWilayah' => ['value' => 'Pulau Nias'],
                            'matchPath' => ['value' => 'Nama rumah adat: Omo Hada & Omo Sebua (Umum Nias)'],
                            'relevanceScore' => ['value' => '100'],
                        ],
                    ],
                ],
            ], 200);
        });

        $response = $this->get('/search?q=Omo+Hada');

        $response->assertStatus(200);
        $response->assertSee('relative group cursor-pointer', false);
        $response->assertSee('after:absolute after:inset-0 focus:outline-none', false);
        $response->assertSee('/detail/rumah-omo-hada-umum-nias', false);
        $response->assertSee('group-hover:bg-[#B51A28]', false);

        // Pastikan template JS renderCards juga memakai stretched link & span untuk tombol visual
        $content = (string) $response->getContent();
        $this->assertStringContainsString('<a href="${escapeHtml(detailUrl)}" class="after:absolute after:inset-0 focus:outline-none">', $content);
        $this->assertStringContainsString('<span class="inline-block rounded-xl bg-[#192235] px-5 py-3 text-sm font-semibold text-white transition group-hover:bg-[#B51A28]">', $content);
    }

    public function test_get_related_houses_excludes_source_deduplicates_reasons_and_sorts_by_graph_score(): void
    {
        $capturedQueries = [];

        Http::fake(function (ClientRequest $request) use (&$capturedQueries) {
            $sparql = (string) ($request['query'] ?? '');
            $capturedQueries[] = $sparql;

            return Http::response([
                'results' => [
                    'bindings' => [
                        // Jika Fuseki mengembalikan node sumber (edge case), SparqlService wajib mengecualikannya
                        [
                            'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_jangga_dolok'],
                            'namaRumah' => ['value' => 'Rumah Bolon Jangga Dolok'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Kabupaten Toba'],
                            'reason' => ['value' => 'Suku sama: Suku Batak Toba'],
                            'weight' => ['value' => '35'],
                        ],
                        // Kandidat A: Suku sama (35) + Ornamen sama (20) = 55 (meski baris reason terduplikasi oleh wilayah)
                        [
                            'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_huta_batak_tb_silalahi_center'],
                            'namaRumah' => ['value' => 'Huta Batak TB Silalahi Center'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Balige, Kabupaten Toba'],
                            'reason' => ['value' => 'Suku sama: Suku Batak Toba'],
                            'weight' => ['value' => '35'],
                        ],
                        [
                            'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_huta_batak_tb_silalahi_center'],
                            'namaRumah' => ['value' => 'Huta Batak TB Silalahi Center'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Kabupaten Toba'],
                            'reason' => ['value' => 'Suku sama: Suku Batak Toba'],
                            'weight' => ['value' => '35'],
                        ],
                        [
                            'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_huta_batak_tb_silalahi_center'],
                            'namaRumah' => ['value' => 'Huta Batak TB Silalahi Center'],
                            'namaSuku' => ['value' => 'Suku Batak Toba'],
                            'namaWilayah' => ['value' => 'Kabupaten Toba'],
                            'reason' => ['value' => 'Ornamen sama: Gorga'],
                            'weight' => ['value' => '20'],
                        ],
                        // Kandidat B: Suku berkerabat (12) + Wilayah serumpun (25) = 37
                        [
                            'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_pematang_purba'],
                            'namaRumah' => ['value' => 'Rumah Bolon Pematang Purba'],
                            'namaSuku' => ['value' => 'Suku Simalungun'],
                            'namaWilayah' => ['value' => 'Kabupaten Simalungun'],
                            'reason' => ['value' => 'Wilayah serumpun: Kawasan Danau Toba'],
                            'weight' => ['value' => '25'],
                        ],
                        [
                            'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_pematang_purba'],
                            'namaRumah' => ['value' => 'Rumah Bolon Pematang Purba'],
                            'namaSuku' => ['value' => 'Suku Simalungun'],
                            'namaWilayah' => ['value' => 'Kabupaten Simalungun'],
                            'reason' => ['value' => 'Suku berkerabat: Suku Simalungun'],
                            'weight' => ['value' => '12'],
                        ],
                    ],
                ],
            ], 200);
        });

        $service = app(SparqlService::class);
        $related = $service->getRelatedHouses('rumah-bolon-jangga-dolok', 4);

        $this->assertCount(2, $related);
        $this->assertSame('rumah-huta-batak-tb-silalahi-center', $related[0]['slug']);
        $this->assertSame(55, $related[0]['similarityScore']);
        $this->assertSame(2, $related[0]['sharedRelationCount']);
        $this->assertSame(['Suku sama: Suku Batak Toba', 'Ornamen sama: Gorga'], $related[0]['relatedReasons']);

        $this->assertSame('rumah-bolon-pematang-purba', $related[1]['slug']);
        $this->assertSame(37, $related[1]['similarityScore']);

        $this->assertNotEmpty($capturedQueries);
        $this->assertStringContainsString('BIND(ra:rumah_bolon_jangga_dolok AS ?source)', $capturedQueries[0]);
        $this->assertStringContainsString('FILTER(?candidate != ?source)', $capturedQueries[0]);
        $this->assertStringContainsString('FILTER(?wilInduk != ra:wil_sumatera_utara)', $capturedQueries[0]);
    }

    public function test_detail_page_renders_related_houses_section_with_graph_reasons(): void
    {
        Http::fake(function (ClientRequest $request) {
            $sparql = (string) ($request['query'] ?? '');

            if (str_contains($sparql, 'FILTER(?candidate != ?source)')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            [
                                'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_jangga_dolok'],
                                'namaRumah' => ['value' => 'Rumah Bolon Jangga Dolok'],
                                'namaSuku' => ['value' => 'Suku Batak Toba'],
                                'namaWilayah' => ['value' => 'Kabupaten Toba'],
                                'reason' => ['value' => 'Suku sama: Suku Batak Toba'],
                                'weight' => ['value' => '35'],
                            ],
                            [
                                'candidate' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_bolon_jangga_dolok'],
                                'namaRumah' => ['value' => 'Rumah Bolon Jangga Dolok'],
                                'namaSuku' => ['value' => 'Suku Batak Toba'],
                                'namaWilayah' => ['value' => 'Kabupaten Toba'],
                                'reason' => ['value' => 'Ornamen sama: Gorga'],
                                'weight' => ['value' => '20'],
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'results' => [
                    'bindings' => [
                        [
                            'nama' => ['value' => 'Huta Batak TB Silalahi Center'],
                            'namaAlternatif' => ['value' => 'Ruma Bolon Museum TB Silalahi'],
                            'sejarah' => ['value' => 'Kompleks pelestarian arsitektur Batak Toba di Balige.'],
                            'fungsi' => ['value' => 'Museum budaya dan edukasi warisan Batak.'],
                            'ciriArsitektur' => ['value' => 'Rumah panggung kayu dengan ukiran Gorga khas Batak Toba.'],
                            'bentukAtap' => ['value' => 'Pelana melengkung menyerupai tanduk kerbau.'],
                            'bahanBangunan' => ['value' => 'Kayu keras pilihan dan atap ijuk.'],
                            'maknaFilosofis' => ['value' => 'Simbol keseimbangan kosmos tiga dunia Batak Toba.'],
                            'referensi' => ['value' => 'Dokumentasi Budaya Sumatera Utara'],
                            'suku' => ['value' => 'Suku Batak Toba'],
                            'wilayah' => ['value' => 'Balige, Kabupaten Toba || Kabupaten Toba'],
                            'bahasa' => ['value' => 'Bahasa Batak Toba'],
                            'marga' => ['value' => 'Silalahi || Siahaan'],
                            'tradisi' => ['value' => 'Mangokal Holi'],
                            'ornamen' => ['value' => 'Gorga'],
                        ],
                    ],
                ],
            ], 200);
        });

        $response = $this->get('/detail/rumah-huta-batak-tb-silalahi-center');

        $response->assertStatus(200);
        $response->assertSee('Rumah Adat Terkait');
        $response->assertSee('Rumah Bolon Jangga Dolok');
        $response->assertSee('Skor Graf 55');
        $response->assertSee('Suku sama: Suku Batak Toba');
        $response->assertSee('Ornamen sama: Gorga');
        $response->assertSee('/detail/rumah-bolon-jangga-dolok', false);
        $response->assertSee('after:absolute after:inset-0 focus:outline-none', false);
    }
}

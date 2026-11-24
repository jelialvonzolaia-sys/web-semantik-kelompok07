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
        $response->assertDontSee('dikoreksi dari');

        $searchSparql = collect($capturedQueries)->first(
            static fn (string $query): bool => str_contains($query, 'BIND(LCASE(')
        );
        $this->assertNotNull($searchSparql);
        $this->assertStringContainsString('BIND(LCASE("Laia") AS ?ql)', $searchSparql);
    }

    public function test_resolve_typo_keyword_caches_dictionary_and_normalizes_typos_and_diacritics(): void
    {
        $dictionaryRequests = 0;

        Http::fake(function () use (&$dictionaryRequests) {
            $dictionaryRequests++;

            return Http::response([
                'results' => [
                    'bindings' => [
                        ['label' => ['value' => 'Bahasa Nias (Li Niha)']],
                        ['label' => ['value' => 'Suku Nias']],
                        ['label' => ['value' => 'Ginting']],
                        ['label' => ['value' => 'Gulö']],
                        ['label' => ['value' => 'Mendröfa']],
                        ['label' => ['value' => 'Mandailing']],
                        ['label' => ['value' => 'Maena']],
                        ['label' => ['value' => 'Kerja']],
                        ['label' => ['value' => 'Omo Hada & Omo Sebua (Umum Nias)']],
                    ],
                ],
            ], 200);
        });

        $service = app(SparqlService::class);

        $nisa = $service->resolveTypoKeyword('nisa');
        $this->assertTrue($nisa['is_corrected']);
        $this->assertSame('Nias', $nisa['corrected']);

        $ginitng = $service->resolveTypoKeyword('ginitng');
        $this->assertTrue($ginitng['is_corrected']);
        $this->assertSame('Ginting', $ginitng['corrected']);

        $mandalng = $service->resolveTypoKeyword('mandalng');
        $this->assertTrue($mandalng['is_corrected']);
        $this->assertSame('Mandailing', $mandalng['corrected']);

        $gulo = $service->resolveTypoKeyword('gulo');
        $this->assertTrue($gulo['is_corrected']);
        $this->assertSame('Gulö', $gulo['corrected']);

        $omoPunct = $service->resolveTypoKeyword('  Ömo!! ');
        $this->assertTrue($omoPunct['is_corrected']);
        $this->assertSame('Omo', $omoPunct['corrected']);

        $exactPunct = $service->resolveTypoKeyword('  Maena!! ');
        $this->assertFalse($exactPunct['is_corrected']);
        $this->assertSame('Maena', $exactPunct['corrected']);

        $tooFarShort = $service->resolveTypoKeyword('nxxx');
        $this->assertFalse($tooFarShort['is_corrected']);
        $this->assertSame('nxxx', $tooFarShort['corrected']);

        $unrelatedSixLetter = $service->resolveTypoKeyword('kerbau');
        $this->assertFalse($unrelatedSixLetter['is_corrected']);
        $this->assertSame('kerbau', $unrelatedSixLetter['corrected']);

        $this->assertSame(1, $dictionaryRequests);
        $this->assertTrue(Cache::has('ontology_dictionary'));
    }

    public function test_search_displays_typo_correction_banner_and_uses_corrected_keyword(): void
    {
        $capturedQueries = [];

        Http::fake(function (ClientRequest $request) use (&$capturedQueries) {
            $sparql = (string) ($request['query'] ?? '');
            $capturedQueries[] = $sparql;

            if (str_contains($sparql, '?label ?altLabel')) {
                return Http::response([
                    'results' => [
                        'bindings' => [
                            ['label' => ['value' => 'Suku Nias']],
                            ['label' => ['value' => 'Ginting']],
                            ['label' => ['value' => 'Omo Hada Nias Selatan']],
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
                            'rumah' => ['value' => 'http://example.org/ontology/rumah-adat#rumah_omo_hada_nias_selatan'],
                            'namaRumah' => ['value' => 'Omo Hada Nias Selatan'],
                            'namaSuku' => ['value' => 'Suku Nias'],
                            'namaWilayah' => ['value' => 'Kabupaten Nias Selatan'],
                            'matchPath' => ['value' => 'Suku: Suku Nias'],
                            'relevanceScore' => ['value' => '85'],
                        ],
                    ],
                ],
            ], 200);
        });

        $response = $this->get('/search?q=nisa');

        $response->assertStatus(200);
        $response->assertSee('Omo Hada Nias Selatan');
        $response->assertSee('Menampilkan hasil untuk', false);
        $response->assertSee('"Nias"', false);
        $response->assertSee('(dikoreksi dari', false);
        $response->assertSee('"nisa"', false);

        $searchSparql = collect($capturedQueries)->first(
            static fn (string $query): bool => str_contains($query, 'BIND(LCASE(')
        );
        $this->assertNotNull($searchSparql);
        $this->assertStringContainsString('BIND(LCASE("Nias") AS ?ql)', $searchSparql);
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
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SparqlService
{
    private const NAMESPACE_URI = 'http://example.org/ontology/rumah-adat#';

    private bool $fusekiAvailable = true;

    private ?string $lastError = null;

    public function isFusekiAvailable(): bool
    {
        return $this->fusekiAvailable;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Eksekusi query SPARQL SELECT ke endpoint Apache Jena Fuseki.
     *
     * @return array<int, array<string, array{type?: string, value?: string}>>
     */
    public function query(string $sparql): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/sparql-results+json',
                ])
                ->asForm()
                ->post($this->getEndpoint(), [
                    'query' => $sparql,
                ]);

            if ($response->failed()) {
                $this->fusekiAvailable = false;
                $this->lastError = 'Server Fuseki merespons dengan status '.$response->status().'.';
                Log::warning('Fuseki SPARQL query failed', [
                    'status' => $response->status(),
                    'endpoint' => $this->getEndpoint(),
                ]);

                return [];
            }

            $this->fusekiAvailable = true;
            $this->lastError = null;

            $bindings = $response->json('results.bindings', []);

            return is_array($bindings) ? $bindings : [];
        } catch (\Throwable $exception) {
            $this->fusekiAvailable = false;
            $this->lastError = 'Tidak dapat terhubung ke server Apache Jena Fuseki.';
            Log::warning('Fuseki SPARQL connection error', [
                'endpoint' => $this->getEndpoint(),
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Ambil jumlah statistik entitas dari stats.rq.
     *
     * @return array{
     *     RumahAdat: int,
     *     Suku: int,
     *     Marga: int,
     *     Wilayah: int,
     *     Tradisi: int,
     *     Ornamen: int,
     *     BahasaDaerah: int
     * }
     */
    public function getStats(): array
    {
        $stats = [
            'RumahAdat' => 0,
            'Suku' => 0,
            'Marga' => 0,
            'Wilayah' => 0,
            'Tradisi' => 0,
            'Ornamen' => 0,
            'BahasaDaerah' => 0,
        ];

        $sparql = $this->loadQuery('stats.rq');
        $bindings = $this->query($sparql);

        foreach ($bindings as $row) {
            $tipe = $this->bindingValue($row, 'tipe');
            $jumlah = $this->bindingValue($row, 'jumlah');

            if ($tipe !== '' && array_key_exists($tipe, $stats)) {
                $stats[$tipe] = (int) $jumlah;
            }
        }

        return $stats;
    }

    /**
     * Ambil opsi filter dinamis per kategori dari filters.rq.
     *
     * @return array{
     *     suku: array<int, array{uri: string, label: string}>,
     *     wilayah: array<int, array{uri: string, label: string}>,
     *     tradisi: array<int, array{uri: string, label: string}>,
     *     ornamen: array<int, array{uri: string, label: string}>,
     *     bahasa: array<int, array{uri: string, label: string}>,
     *     marga: array<int, array{uri: string, label: string}>
     * }
     */
    public function getFilterOptions(): array
    {
        $options = [
            'suku' => [],
            'wilayah' => [],
            'tradisi' => [],
            'ornamen' => [],
            'bahasa' => [],
            'marga' => [],
        ];

        $sparql = $this->loadQuery('filters.rq');
        $bindings = $this->query($sparql);

        foreach ($bindings as $row) {
            $kategori = $this->bindingValue($row, 'kategori');
            $uri = $this->bindingValue($row, 'uri');
            $label = $this->bindingValue($row, 'label');

            if ($kategori !== '' && $label !== '' && array_key_exists($kategori, $options)) {
                $options[$kategori][] = [
                    'uri' => $uri,
                    'label' => $label,
                ];
            }
        }

        return $options;
    }

    /**
     * Ambil daftar wilayah utama Sumatera Utara beserta jumlah rumah adatnya dari Fuseki.
     *
     * @return array<int, array{name: string, keyword: string, count: int}>
     */
    public function getFeaturedRegions(int $limit = 6): array
    {
        $safeLimit = max(1, min($limit, 12));

        $sparql = <<<SPARQL
PREFIX ra: <http://example.org/ontology/rumah-adat#>

SELECT ?namaWilayah (COUNT(DISTINCT ?rumah) AS ?jumlah)
WHERE {
  ?wil ra:bagianDariWilayah ra:wil_sumatera_utara ;
       ra:nama ?namaWilayah .
  ?rumah a ra:RumahAdat ;
         ra:berasalDariWilayah/(ra:bagianDariWilayah)* ?wil .
}
GROUP BY ?namaWilayah
ORDER BY DESC(?jumlah) ASC(?namaWilayah)
LIMIT {$safeLimit}
SPARQL;

        $bindings = $this->query($sparql);
        $regions = [];

        foreach ($bindings as $row) {
            $name = $this->bindingValue($row, 'namaWilayah');
            $count = (int) $this->bindingValue($row, 'jumlah');

            if ($name !== '' && $count > 0) {
                $regions[] = [
                    'name' => $name,
                    'keyword' => $name,
                    'count' => $count,
                ];
            }
        }

        return $regions;
    }

    /**
     * Jalankan pencarian multi-hop + filter kategori, lalu kelompokkan per Rumah Adat.
     *
     * @param  array<string, array<int, string>|string>  $filters
     * @return array<int, array{
     *     uri: string,
     *     id: string,
     *     slug: string,
     *     name: string,
     *     tribe: string,
     *     tribes: array<int, string>,
     *     region: string,
     *     regions: array<int, string>,
     *     tag: string,
     *     description: string,
     *     image: string,
     *     hasCustomImage: bool,
     *     matchPaths: array<int, string>,
     *     relevanceScore: int
     * }>
     */
    public function search(string $keyword = '', array $filters = []): array
    {
        $cleanKeyword = $this->sanitizeInput($keyword);
        $filterClauses = $this->buildFilterClauses($filters);

        if ($cleanKeyword !== '') {
            $sparql = $this->loadQuery('search.rq');
            $escapedKeyword = $this->escapeSparqlLiteral($cleanKeyword);
            $sparql = str_replace(
                'BIND(LCASE("laia") AS ?ql)',
                "BIND(LCASE(\"{$escapedKeyword}\") AS ?ql)",
                $sparql
            );

            if ($filterClauses !== '') {
                $sparql = $this->injectBeforeWhereEnd($sparql, $filterClauses);
            }
        } else {
            $sparql = $this->buildBrowseQuery($filterClauses);
        }

        $bindings = $this->query($sparql);

        return $this->aggregateSearchBindings($bindings, $cleanKeyword !== '');
    }

    /**
     * Ambil detail lengkap satu rumah adat dari detail.rq.
     *
     * @return array{
     *     uri: string,
     *     id: string,
     *     slug: string,
     *     name: string,
     *     namaAlternatif: string,
     *     sejarah: string,
     *     fungsi: string,
     *     ciriArsitektur: string,
     *     bentukAtap: string,
     *     bahanBangunan: string,
     *     maknaFilosofis: string,
     *     referensi: string,
     *     tribe: string,
     *     region: string,
     *     tag: string,
     *     description: string,
     *     image: string,
     *     hasCustomImage: bool,
     *     suku: array<int, string>,
     *     wilayah: array<int, string>,
     *     bahasa: array<int, string>,
     *     marga: array<int, string>,
     *     tradisi: array<int, string>,
     *     ornamen: array<int, string>
     * }|null
     */
    public function getDetail(string $id): ?array
    {
        $resource = $this->resolveHouseResource($id);

        if ($resource === null) {
            return null;
        }

        $sparql = $this->loadQuery('detail.rq');
        $sparql = str_replace(
            'BIND(ra:rumah_bagas_godang_panyabungan_tonga AS ?rumah)',
            "BIND({$resource} AS ?rumah)",
            $sparql
        );
        $sparql = str_replace('SEPARATOR=", "', 'SEPARATOR=" || "', $sparql);

        $bindings = $this->query($sparql);

        if ($bindings === []) {
            return null;
        }

        $row = $bindings[0];
        $name = $this->bindingValue($row, 'nama');

        if ($name === '') {
            return null;
        }

        $localName = substr($resource, 3);
        $suku = $this->splitGroupConcat($this->bindingValue($row, 'suku'));
        $wilayah = $this->splitGroupConcat($this->bindingValue($row, 'wilayah'), false);
        $bahasa = $this->splitGroupConcat($this->bindingValue($row, 'bahasa'));
        $marga = $this->splitGroupConcat($this->bindingValue($row, 'marga'));
        $tradisi = $this->splitGroupConcat($this->bindingValue($row, 'tradisi'));
        $ornamen = $this->splitGroupConcat($this->bindingValue($row, 'ornamen'));

        $sejarah = $this->bindingValue($row, 'sejarah');
        $ciriArsitektur = $this->bindingValue($row, 'ciriArsitektur');
        $fungsi = $this->bindingValue($row, 'fungsi');
        $maknaFilosofis = $this->bindingValue($row, 'maknaFilosofis');

        $description = $sejarah !== ''
            ? $sejarah
            : ($ciriArsitektur !== '' ? $ciriArsitektur : ($fungsi !== '' ? $fungsi : $maknaFilosofis));

        $primaryTribe = $suku[0] ?? 'Sumatera Utara';
        $slug = str_replace('_', '-', $localName);
        $imageInfo = $this->resolveHouseImage($localName, $slug);

        return [
            'uri' => self::NAMESPACE_URI.$localName,
            'id' => $localName,
            'slug' => $slug,
            'name' => $name,
            'namaAlternatif' => $this->bindingValue($row, 'namaAlternatif'),
            'sejarah' => $sejarah,
            'fungsi' => $fungsi,
            'ciriArsitektur' => $ciriArsitektur,
            'bentukAtap' => $this->bindingValue($row, 'bentukAtap'),
            'bahanBangunan' => $this->bindingValue($row, 'bahanBangunan'),
            'maknaFilosofis' => $maknaFilosofis,
            'referensi' => $this->bindingValue($row, 'referensi'),
            'tribe' => $suku !== [] ? implode(', ', $suku) : 'Sumatera Utara',
            'region' => $wilayah !== [] ? implode(', ', $wilayah) : 'Sumatera Utara',
            'tag' => $suku !== [] ? "Rumah Adat {$primaryTribe}" : 'Warisan Arsitektur',
            'description' => $description,
            'image' => $imageInfo['url'],
            'hasCustomImage' => $imageInfo['isCustom'],
            'suku' => $suku,
            'wilayah' => $wilayah,
            'bahasa' => $bahasa,
            'marga' => $marga,
            'tradisi' => $tradisi,
            'ornamen' => $ornamen,
        ];
    }

    /**
     * Ambil triplet graf dari graph.rq dan ubah menjadi nodes & edges untuk Vis-Network.
     *
     * @return array{
     *     nodes: array<int, array{id: string, label: string, group: string, type: string, slug: ?string, title: string}>,
     *     edges: array<int, array{id: string, from: string, to: string, label: string, predicate: string, arrows: string}>,
     *     meta: array{nodeCount: int, edgeCount: int, focus: ?string, keyword: ?string}
     * }
     */
    public function getGraphData(?string $focusId = null, ?string $keyword = null): array
    {
        $cleanFocus = $focusId !== null ? trim($focusId) : '';
        $cleanKeyword = $keyword !== null ? $this->sanitizeInput($keyword) : '';

        if ($cleanFocus !== '') {
            $focusResource = $this->resolveHouseResource($cleanFocus);

            if ($focusResource === null) {
                return $this->emptyGraphResponse($cleanFocus, $cleanKeyword !== '' ? $cleanKeyword : null);
            }

            $sparql = $this->buildFocusedGraphQuery($focusResource, $cleanKeyword);
        } elseif ($cleanKeyword !== '') {
            $sparql = $this->loadQuery('graph.rq');
            $escapedKeyword = $this->escapeSparqlLiteral($cleanKeyword);
            $keywordFilter = <<<SPARQL
  FILTER(
    CONTAINS(LCASE(STR(?subjectLabel)), LCASE("{$escapedKeyword}")) ||
    CONTAINS(LCASE(STR(?objectLabel)), LCASE("{$escapedKeyword}")) ||
    CONTAINS(LCASE(STR(?predicateLabel)), LCASE("{$escapedKeyword}"))
  )
SPARQL;
            $sparql = $this->injectBeforeWhereEnd($sparql, $keywordFilter);
        } else {
            $sparql = $this->loadQuery('graph.rq');
        }

        $bindings = $this->query($sparql);
        $nodes = [];
        $edges = [];

        foreach ($bindings as $row) {
            $subject = $this->bindingValue($row, 'subject');
            $subjectLabel = $this->bindingValue($row, 'subjectLabel');
            $subjectType = $this->bindingValue($row, 'subjectType');
            $predicate = $this->bindingValue($row, 'predicate');
            $predicateLabel = $this->bindingValue($row, 'predicateLabel');
            $object = $this->bindingValue($row, 'object');
            $objectLabel = $this->bindingValue($row, 'objectLabel');
            $objectType = $this->bindingValue($row, 'objectType');

            if ($subject === '' || $object === '' || $predicate === '') {
                continue;
            }

            if (! isset($nodes[$subject])) {
                $nodes[$subject] = $this->formatGraphNode($subject, $subjectLabel, $subjectType);
            }

            if (! isset($nodes[$object])) {
                $nodes[$object] = $this->formatGraphNode($object, $objectLabel, $objectType);
            }

            $edgeKey = "{$subject}|{$predicate}|{$object}";

            if (! isset($edges[$edgeKey])) {
                $edges[$edgeKey] = [
                    'id' => md5($edgeKey),
                    'from' => $subject,
                    'to' => $object,
                    'label' => $predicateLabel,
                    'predicate' => $predicate,
                    'arrows' => 'to',
                ];
            }
        }

        return [
            'nodes' => array_values($nodes),
            'edges' => array_values($edges),
            'meta' => [
                'nodeCount' => count($nodes),
                'edgeCount' => count($edges),
                'focus' => $cleanFocus !== '' ? $cleanFocus : null,
                'keyword' => $cleanKeyword !== '' ? $cleanKeyword : null,
            ],
        ];
    }

    private function getEndpoint(): string
    {
        $endpoint = (string) config('services.fuseki.endpoint', '');

        if ($endpoint !== '') {
            return $endpoint;
        }

        $baseUrl = rtrim((string) config('services.fuseki.base_url', 'http://localhost:3030'), '/');
        $dataset = trim((string) config('services.fuseki.dataset', 'rumah_adat'), '/');

        return "{$baseUrl}/{$dataset}/query";
    }

    private function loadQuery(string $filename): string
    {
        $path = resource_path("sparql/{$filename}");

        if (! is_file($path)) {
            throw new \RuntimeException("File SPARQL tidak ditemukan: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException("Gagal membaca file SPARQL: {$path}");
        }

        return $contents;
    }

    private function bindingValue(array $row, string $key): string
    {
        $value = $row[$key]['value'] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function sanitizeInput(string $input): string
    {
        $cleaned = preg_replace('/[\x00-\x1F\x7F{}<>]/u', ' ', $input) ?? '';

        return trim((string) preg_replace('/\s+/u', ' ', $cleaned));
    }

    private function escapeSparqlLiteral(string $value): string
    {
        return str_replace(
            ['\\', '"', "\r", "\n"],
            ['\\\\', '\\"', ' ', ' '],
            $value
        );
    }

    /**
     * @param  array<string, array<int, string>|string>  $filters
     */
    private function buildFilterClauses(array $filters): string
    {
        $propertyMap = [
            'suku' => 'ra:dimilikiOlehSuku',
            'wilayah' => 'ra:berasalDariWilayah/(ra:bagianDariWilayah)*',
            'tradisi' => 'ra:memilikiTradisi',
            'ornamen' => 'ra:memilikiOrnamen',
            'bahasa' => 'ra:menggunakanBahasa',
            'marga' => 'ra:memilikiMarga',
        ];

        $clauses = [];

        foreach ($propertyMap as $key => $propertyPath) {
            $rawValues = $filters[$key] ?? [];
            $values = is_array($rawValues) ? $rawValues : [$rawValues];
            $cleanedValues = [];

            foreach ($values as $value) {
                $sanitized = $this->sanitizeInput((string) $value);

                if ($sanitized !== '') {
                    $cleanedValues[] = $sanitized;
                }
            }

            if ($cleanedValues === []) {
                continue;
            }

            $varSuffix = ucfirst($key);
            $nodeVar = "?filter{$varSuffix}";
            $labelVar = "?filter{$varSuffix}Nama";
            $conditions = [];

            foreach ($cleanedValues as $val) {
                $escaped = $this->escapeSparqlLiteral($val);
                $conditions[] = "CONTAINS(LCASE(STR({$labelVar})), LCASE(\"{$escaped}\")) || STR({$nodeVar}) = \"{$escaped}\"";
            }

            $combined = implode(' || ', $conditions);
            $clauses[] = <<<SPARQL
  FILTER EXISTS {
    ?rumah {$propertyPath} {$nodeVar} .
    {$nodeVar} ra:nama {$labelVar} .
    FILTER({$combined})
  }
SPARQL;
        }

        return implode("\n", $clauses);
    }

    private function buildBrowseQuery(string $filterClauses): string
    {
        return <<<SPARQL
PREFIX ra: <http://example.org/ontology/rumah-adat#>

SELECT DISTINCT
  ?rumah
  ?namaRumah
  ?namaSuku
  ?namaWilayah
  ?matchPath
  ?relevanceScore
WHERE {
  ?rumah a ra:RumahAdat ;
         ra:nama ?namaRumah .

  OPTIONAL {
    ?rumah ra:dimilikiOlehSuku ?suku .
    ?suku ra:nama ?namaSuku .
  }
  OPTIONAL {
    ?rumah ra:berasalDariWilayah ?wil .
    ?wil ra:nama ?namaWilayah .
  }

  BIND("Rumah Adat Sumatera Utara" AS ?matchPath)
  BIND(100 AS ?relevanceScore)
{$filterClauses}
}
ORDER BY ASC(?namaRumah)
SPARQL;
    }

    private function buildFocusedGraphQuery(string $focusResource, string $keyword): string
    {
        $keywordFilter = '';

        if ($keyword !== '') {
            $escaped = $this->escapeSparqlLiteral($keyword);
            $keywordFilter = <<<SPARQL
  FILTER(
    CONTAINS(LCASE(STR(?subjectLabel)), LCASE("{$escaped}")) ||
    CONTAINS(LCASE(STR(?objectLabel)), LCASE("{$escaped}")) ||
    CONTAINS(LCASE(STR(?predicateLabel)), LCASE("{$escaped}"))
  )
SPARQL;
        }

        return <<<SPARQL
PREFIX ra:   <http://example.org/ontology/rumah-adat#>
PREFIX rdf:  <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT DISTINCT
  ?subject
  ?subjectLabel
  ?subjectType
  ?predicate
  ?predicateLabel
  ?object
  ?objectLabel
  ?objectType
WHERE {
  {
    BIND({$focusResource} AS ?subject)
    ?subject ?predicate ?object .
  }
  UNION
  {
    {$focusResource} (ra:berasalDariWilayah|ra:dimilikiOlehSuku|ra:memilikiMarga) ?subject .
    ?subject ?predicate ?object .
    FILTER(?predicate IN (ra:bagianDariWilayah, ra:berkerabatDenganMarga, ra:berkerabatDenganSuku, ra:menuturkanBahasa, ra:bagianDariSuku))
  }

  FILTER(isIRI(?subject) && isIRI(?object))
  FILTER(?predicate != rdf:type)

  ?subject a ?sClass .
  FILTER(?sClass IN (ra:RumahAdat, ra:Suku, ra:Marga, ra:BahasaDaerah, ra:Wilayah, ra:Tradisi, ra:Ornamen))
  BIND(REPLACE(STR(?sClass), "^.*[#/]([^#/?]+)$", "$1") AS ?subjectType)

  ?object a ?oClass .
  FILTER(?oClass IN (ra:RumahAdat, ra:Suku, ra:Marga, ra:BahasaDaerah, ra:Wilayah, ra:Tradisi, ra:Ornamen))
  BIND(REPLACE(STR(?oClass), "^.*[#/]([^#/?]+)$", "$1") AS ?objectType)

  OPTIONAL { ?subject ra:nama ?sNama . }
  OPTIONAL { ?subject rdfs:label ?sRdfsLabel . }
  BIND(COALESCE(STR(?sNama), STR(?sRdfsLabel), REPLACE(STR(?subject), "^.*[#/]([^#/?]+)$", "$1")) AS ?subjectLabel)

  OPTIONAL { ?object ra:nama ?oNama . }
  OPTIONAL { ?object rdfs:label ?oRdfsLabel . }
  BIND(COALESCE(STR(?oNama), STR(?oRdfsLabel), REPLACE(STR(?object), "^.*[#/]([^#/?]+)$", "$1")) AS ?objectLabel)

  OPTIONAL { ?predicate rdfs:label ?pLabel . }
  BIND(COALESCE(STR(?pLabel), REPLACE(STR(?predicate), "^.*[#/]([^#/?]+)$", "$1")) AS ?predicateLabel)
{$keywordFilter}
}
ORDER BY ?subjectLabel ?predicateLabel ?objectLabel
LIMIT 250
SPARQL;
    }

    private function injectBeforeWhereEnd(string $sparql, string $clause): string
    {
        $replaced = preg_replace('/\}(\s*ORDER BY)/i', "\n{$clause}\n}\$1", $sparql, 1);

        return is_string($replaced) ? $replaced : $sparql;
    }

    /**
     * Kelompokkan hasil SPARQL yang berulang per kombinasi suku/wilayah/matchPath ke satu kartu rumah adat.
     *
     * @param  array<int, array<string, array{type?: string, value?: string}>>  $bindings
     * @return array<int, array{
     *     uri: string,
     *     id: string,
     *     slug: string,
     *     name: string,
     *     tribe: string,
     *     tribes: array<int, string>,
     *     region: string,
     *     regions: array<int, string>,
     *     tag: string,
     *     description: string,
     *     image: string,
     *     hasCustomImage: bool,
     *     matchPaths: array<int, string>,
     *     relevanceScore: int
     * }>
     */
    private function aggregateSearchBindings(array $bindings, bool $hasKeyword): array
    {
        $grouped = [];

        foreach ($bindings as $row) {
            $uri = $this->bindingValue($row, 'rumah');
            $name = $this->bindingValue($row, 'namaRumah');

            if ($uri === '' || $name === '') {
                continue;
            }

            if (! isset($grouped[$uri])) {
                $localName = $this->uriToLocalName($uri);
                $grouped[$uri] = [
                    'uri' => $uri,
                    'id' => $localName,
                    'slug' => str_replace('_', '-', $localName),
                    'name' => $name,
                    'tribes' => [],
                    'regions' => [],
                    'matchPaths' => [],
                    'topMatchCategory' => 'Rumah Adat',
                    'relevanceScore' => 0,
                ];
            }

            $tribe = $this->bindingValue($row, 'namaSuku');

            if ($tribe !== '' && ! in_array($tribe, $grouped[$uri]['tribes'], true)) {
                $grouped[$uri]['tribes'][] = $tribe;
            }

            $region = $this->bindingValue($row, 'namaWilayah');

            if ($region !== '' && ! in_array($region, $grouped[$uri]['regions'], true)) {
                $grouped[$uri]['regions'][] = $region;
            }

            $matchPath = $this->bindingValue($row, 'matchPath');
            $score = (int) $this->bindingValue($row, 'relevanceScore');

            if ($hasKeyword && $matchPath !== '' && ! in_array($matchPath, $grouped[$uri]['matchPaths'], true)) {
                $grouped[$uri]['matchPaths'][] = $matchPath;
            }

            if ($score > $grouped[$uri]['relevanceScore']) {
                $grouped[$uri]['relevanceScore'] = $score;

                if ($hasKeyword && $matchPath !== '') {
                    $grouped[$uri]['topMatchCategory'] = trim(Str::before($matchPath, ':')) ?: 'Pencocokan Semantik';
                }
            }
        }

        $results = [];

        foreach ($grouped as $item) {
            $tribeLabel = $item['tribes'] !== [] ? implode(', ', $item['tribes']) : 'Sumatera Utara';
            $regionLabel = $item['regions'] !== [] ? implode(', ', $item['regions']) : 'Sumatera Utara';
            $tag = $hasKeyword ? $item['topMatchCategory'] : ('Rumah Adat '.($item['tribes'][0] ?? 'Sumatera Utara'));

            $description = "Rumah adat masyarakat {$tribeLabel} dari wilayah {$regionLabel}.";

            if ($hasKeyword && $item['matchPaths'] !== []) {
                $shownPaths = array_slice($item['matchPaths'], 0, 3);
                $suffix = count($item['matchPaths']) > 3 ? ' (+ lainnya)' : '';
                $description .= ' Terhubung melalui: '.implode(' • ', $shownPaths).$suffix.'.';
            }

            $imageInfo = $this->resolveHouseImage($item['id'], $item['slug']);

            $results[] = [
                'uri' => $item['uri'],
                'id' => $item['id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'tribe' => $tribeLabel,
                'tribes' => $item['tribes'],
                'region' => $regionLabel,
                'regions' => $item['regions'],
                'tag' => $tag,
                'description' => $description,
                'image' => $imageInfo['url'],
                'hasCustomImage' => $imageInfo['isCustom'],
                'matchPaths' => $item['matchPaths'],
                'relevanceScore' => $item['relevanceScore'],
            ];
        }

        usort($results, static function (array $a, array $b): int {
            if ($b['relevanceScore'] !== $a['relevanceScore']) {
                return $b['relevanceScore'] <=> $a['relevanceScore'];
            }

            return strcmp($a['name'], $b['name']);
        });

        return $results;
    }

    /**
     * @return array{url: string, isCustom: bool}
     */
    private function resolveHouseImage(string $localName, string $slug): array
    {
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];
        $candidates = array_values(array_unique([$slug, $localName]));

        foreach ($candidates as $candidate) {
            foreach ($extensions as $extension) {
                $relativePath = "images/rumah-adat/{$candidate}.{$extension}";

                if (is_file(public_path($relativePath))) {
                    return [
                        'url' => asset($relativePath),
                        'isCustom' => true,
                    ];
                }
            }
        }

        return [
            'url' => asset('images/rumah-adat/hero-rumah-adat.jpeg'),
            'isCustom' => false,
        ];
    }

    private function resolveHouseResource(string $id): ?string
    {
        $trimmed = trim($id);

        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, self::NAMESPACE_URI)) {
            $localName = substr($trimmed, strlen(self::NAMESPACE_URI));
        } elseif (str_starts_with($trimmed, 'ra:')) {
            $localName = substr($trimmed, 3);
        } else {
            $localName = str_replace('-', '_', $trimmed);
        }

        if (preg_match('/^[A-Za-z0-9_]+$/', $localName) !== 1) {
            return null;
        }

        return 'ra:'.$localName;
    }

    private function uriToLocalName(string $uri): string
    {
        $extracted = preg_replace('/^.*[#\/]/', '', $uri);

        return is_string($extracted) ? $extracted : $uri;
    }

    /**
     * @return array<int, string>
     */
    private function splitGroupConcat(string $raw, bool $splitCommaFallback = true): array
    {
        if ($raw === '') {
            return [];
        }

        if (str_contains($raw, ' || ')) {
            $parts = array_map('trim', explode(' || ', $raw));
        } elseif ($splitCommaFallback) {
            $parts = array_map('trim', explode(', ', $raw));
        } else {
            $parts = [trim($raw)];
        }

        $filtered = array_filter($parts, static fn (string $item): bool => $item !== '');

        return array_values(array_unique($filtered));
    }

    /**
     * @return array{id: string, label: string, group: string, type: string, slug: ?string, title: string}
     */
    private function formatGraphNode(string $uri, string $label, string $type): array
    {
        $localName = $this->uriToLocalName($uri);
        $cleanType = $type !== '' ? $type : 'Entitas';

        return [
            'id' => $uri,
            'label' => $label !== '' ? $label : $localName,
            'group' => $cleanType,
            'type' => $cleanType,
            'slug' => $cleanType === 'RumahAdat' ? str_replace('_', '-', $localName) : null,
            'title' => ($label !== '' ? $label : $localName)." ({$cleanType})",
        ];
    }

    /**
     * @return array{
     *     nodes: array<int, array{id: string, label: string, group: string, type: string, slug: ?string, title: string}>,
     *     edges: array<int, array{id: string, from: string, to: string, label: string, predicate: string, arrows: string}>,
     *     meta: array{nodeCount: int, edgeCount: int, focus: ?string, keyword: ?string}
     * }
     */
    private function emptyGraphResponse(?string $focus, ?string $keyword): array
    {
        return [
            'nodes' => [],
            'edges' => [],
            'meta' => [
                'nodeCount' => 0,
                'edgeCount' => 0,
                'focus' => $focus,
                'keyword' => $keyword,
            ],
        ];
    }
}

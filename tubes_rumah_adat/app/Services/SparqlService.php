<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SparqlService
{
    private const NAMESPACE_URI = 'http://example.org/ontology/rumah-adat#';

    private bool $fusekiAvailable = true;

    private ?string $lastError = null;

    /** @var array{
     *     original: string,
     *     corrected: string,
     *     original_query: string,
     *     corrected_query: string,
     *     is_corrected: bool,
     *     suggestions: array<int, string>,
     *     expansion_terms: array<int, string>
     * }|null
     */
    private ?array $lastTypoResolution = null;

    public function isFusekiAvailable(): bool
    {
        return $this->fusekiAvailable;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array{
     *     original: string,
     *     corrected: string,
     *     original_query: string,
     *     corrected_query: string,
     *     is_corrected: bool,
     *     suggestions: array<int, string>,
     *     expansion_terms: array<int, string>
     * }|null
     */
    public function getLastTypoResolution(): ?array
    {
        return $this->lastTypoResolution;
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
     * Bangun kamus entitas ter-tokenisasi dari seluruh ra:nama 7 kelas ontologi dan simpan di Cache Laravel.
     *
     * @return array{
     *     phrases: array<string, string>,
     *     tokens: array<string, string>,
     *     raw_phrases: array<int, string>,
     *     raw_tokens: array<string, bool>
     * }
     */
    public function buildOntologyDictionary(): array
    {
        $cached = Cache::remember('ontology_dictionary', 3600, function (): array {
            $labels = $this->fetchOntologyDictionary();

            if ($labels === []) {
                return [];
            }

            return $this->buildDictionaryIndex($labels);
        });

        if ($cached === [] && ! $this->fusekiAvailable) {
            Cache::forget('ontology_dictionary');
        }

        if (! isset($cached['phrases'], $cached['tokens'], $cached['raw_phrases'], $cached['raw_tokens'])) {
            return [
                'phrases' => [],
                'tokens' => [],
                'raw_phrases' => [],
                'raw_tokens' => [],
            ];
        }

        return $cached;
    }

    /**
     * Hitung skor kemiripan gabungan (0.0 - 1.0) memakai Damerau-Levenshtein,
     * Sorted-Character Levenshtein, similar_text, dan metaphone.
     */
    public function calculateHybridSimilarity(string $input, string $target): float
    {
        $normA = $this->normalizeSimilarityText($input);
        $normB = $this->normalizeSimilarityText($target);

        if ($normA === '' || $normB === '') {
            return 0.0;
        }

        if ($normA === $normB) {
            return 1.0;
        }

        $maxLength = max(strlen($normA), strlen($normB), 1);
        $damerauDistance = $this->calculateEditDistance($normA, $normB);
        $damerauScore = max(0.0, 1.0 - ($damerauDistance / $maxLength));

        $sortedA = $this->sortCharacters(str_replace(' ', '', $normA));
        $sortedB = $this->sortCharacters(str_replace(' ', '', $normB));
        $sortedMax = max(strlen($sortedA), strlen($sortedB), 1);
        $sortedScore = max(0.0, 1.0 - (levenshtein($sortedA, $sortedB) / $sortedMax));

        similar_text($normA, $normB, $similarPercent);
        $similarTextScore = $similarPercent / 100.0;

        $metaA = metaphone($normA);
        $metaB = metaphone($normB);
        $phoneticScore = 0.0;

        if ($metaA !== '' && $metaB !== '') {
            if ($metaA === $metaB) {
                $phoneticScore = 1.0;
            } else {
                $metaMax = max(strlen($metaA), strlen($metaB), 1);
                $phoneticScore = max(0.0, 1.0 - (levenshtein($metaA, $metaB) / $metaMax));
            }
        }

        $combined = (0.30 * $damerauScore)
            + (0.30 * $sortedScore)
            + (0.20 * $similarTextScore)
            + (0.20 * $phoneticScore);

        return round(min(1.0, max(0.0, $combined)), 4);
    }

    /**
     * Resolusi kata kunci typo (single/multi-keyword) dengan ambang batas hybrid similarity >= 0.68
     * serta ekspansi kandidat top untuk query SPARQL OR (||).
     *
     * @return array{
     *     original: string,
     *     corrected: string,
     *     original_query: string,
     *     corrected_query: string,
     *     is_corrected: bool,
     *     suggestions: array<int, string>,
     *     expansion_terms: array<int, string>
     * }
     */
    public function resolveTypoKeyword(string $keyword): array
    {
        $trimmed = trim($keyword);

        if ($trimmed === '') {
            return $this->formatTypoResult($keyword, '', false, [], []);
        }

        $cleanedInput = $this->stripExtraPunctuation($trimmed);

        if ($cleanedInput === '') {
            return $this->formatTypoResult($keyword, '', false, [], []);
        }

        $dictionary = $this->buildOntologyDictionary();

        if ($dictionary['phrases'] === [] && $dictionary['tokens'] === []) {
            return $this->formatTypoResult($keyword, $cleanedInput, false, [], [$cleanedInput]);
        }

        $rawLower = mb_strtolower($cleanedInput, 'UTF-8');

        if ($this->matchesExactPreserveDiacritics($rawLower, $dictionary)) {
            return $this->formatTypoResult($keyword, $cleanedInput, false, [], [$cleanedInput]);
        }

        if (! str_contains($cleanedInput, ' ')) {
            return $this->resolveSingleTerm($keyword, $cleanedInput, $dictionary);
        }

        return $this->resolveMultiWordQuery($keyword, $cleanedInput, $dictionary);
    }

    /**
     * Jalankan pencarian multi-hop + filter kategori dengan dukungan typo tolerance & OR expansion.
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
        $typoResolution = $this->resolveTypoKeyword($keyword);
        $this->lastTypoResolution = $typoResolution;
        $cleanKeyword = $this->sanitizeInput($typoResolution['corrected_query']);
        $expansionTerms = $this->sanitizeExpansionTerms($typoResolution['expansion_terms'], $cleanKeyword);
        $filterClauses = $this->buildFilterClauses($filters);

        if ($cleanKeyword !== '') {
            $sparql = $this->loadQuery('search.rq');
            $escapedKeyword = $this->escapeSparqlLiteral($cleanKeyword);
            $sparql = str_replace(
                'BIND(LCASE("laia") AS ?ql)',
                "BIND(LCASE(\"{$escapedKeyword}\") AS ?ql)",
                $sparql
            );

            if (count($expansionTerms) > 1) {
                $orConditions = array_map(function (string $term): string {
                    $escaped = $this->escapeSparqlLiteral($term);

                    return "CONTAINS(LCASE(STR(?matchValue)), LCASE(\"{$escaped}\"))";
                }, $expansionTerms);

                $orFilter = 'FILTER('.implode(' || ', $orConditions).')';
                $sparql = str_replace(
                    'FILTER(?ql != "" && CONTAINS(LCASE(STR(?matchValue)), ?ql))',
                    $orFilter,
                    $sparql
                );
            }

            if ($filterClauses !== '') {
                $sparql = $this->injectBeforeWhereEnd($sparql, $filterClauses);
            }
        } else {
            $sparql = $this->buildBrowseQuery($filterClauses);
        }

        $bindings = $this->query($sparql);

        return $this->aggregateSearchBindings($bindings, $cleanKeyword !== '', $expansionTerms);
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
     * @return array<int, string>
     */
    private function fetchOntologyDictionary(): array
    {
        $sparql = <<<'SPARQL'
PREFIX ra: <http://example.org/ontology/rumah-adat#>

SELECT DISTINCT ?label ?altLabel
WHERE {
  ?entitas a ?kelas ;
           ra:nama ?label .
  FILTER(?kelas IN (ra:RumahAdat, ra:Suku, ra:Marga, ra:Wilayah, ra:BahasaDaerah, ra:Tradisi, ra:Ornamen))
  OPTIONAL { ?entitas ra:namaAlternatif ?altLabel . }
}
ORDER BY ASC(?label)
SPARQL;

        $bindings = $this->query($sparql);
        $entries = [];

        foreach ($bindings as $row) {
            $label = $this->bindingValue($row, 'label');

            if ($label !== '') {
                $entries[$label] = true;
            }

            $altRaw = $this->bindingValue($row, 'altLabel');

            if ($altRaw !== '') {
                foreach (explode(',', $altRaw) as $altPart) {
                    $cleanAlt = trim($altPart);

                    if ($cleanAlt !== '') {
                        $entries[$cleanAlt] = true;
                    }
                }
            }
        }

        return array_keys($entries);
    }

    /**
     * @param  array<int, string>  $dictionary
     * @return array{
     *     phrases: array<string, string>,
     *     tokens: array<string, string>,
     *     raw_phrases: array<int, string>,
     *     raw_tokens: array<string, bool>
     * }
     */
    private function buildDictionaryIndex(array $dictionary): array
    {
        $stopWords = [
            'rumah', 'adat', 'suku', 'kabupaten', 'kota',
            'dan', 'atau', 'kecamatan', 'desa', 'pulau',
            'etnis', 'umum', 'bahasa', 'yang', 'dari',
        ];

        $rawPhrases = [];
        $rawTokens = [];
        $phrases = [];
        $tokens = [];

        foreach ($dictionary as $label) {
            $trimmed = trim($label);

            if ($trimmed === '') {
                continue;
            }

            $rawLower = mb_strtolower($this->stripExtraPunctuation($trimmed), 'UTF-8');

            if ($rawLower !== '') {
                $rawPhrases[] = $rawLower;
            }

            $normFull = $this->normalizeSimilarityText($trimmed);

            if ($normFull !== '' && ! isset($phrases[$normFull])) {
                $phrases[$normFull] = $trimmed;
            }

            $segments = preg_split('/[&\/(),]+/u', $trimmed) ?: [];

            foreach ($segments as $segment) {
                $cleanSeg = trim($segment);
                $normSeg = $this->normalizeSimilarityText($cleanSeg);

                if ($normSeg !== '' && ! isset($phrases[$normSeg])) {
                    $phrases[$normSeg] = $cleanSeg;
                }

                $rawSeg = mb_strtolower($this->stripExtraPunctuation($cleanSeg), 'UTF-8');

                if ($rawSeg !== '') {
                    $rawPhrases[] = $rawSeg;
                }

                // Ekstrak frasa bigram signifikan (mis. "Rumah Bolon", "Bagas Godang", "Suku Nias")
                $segWords = preg_split('/\s+/u', $this->stripExtraPunctuation($cleanSeg)) ?: [];
                $wordCount = count($segWords);

                for ($i = 0; $i < $wordCount - 1; $i++) {
                    $bigram = $segWords[$i].' '.$segWords[$i + 1];
                    $normBigram = $this->normalizeSimilarityText($bigram);

                    if ($normBigram !== '' && ! isset($phrases[$normBigram])) {
                        $phrases[$normBigram] = $bigram;
                    }

                    $rawBigram = mb_strtolower($bigram, 'UTF-8');
                    $rawPhrases[] = $rawBigram;
                }
            }

            $rawWords = preg_split('/[^\p{L}\p{N}]+/u', $trimmed) ?: [];

            foreach ($rawWords as $word) {
                $wordLower = mb_strtolower($word, 'UTF-8');

                if (mb_strlen($word, 'UTF-8') < 3 || in_array($wordLower, $stopWords, true)) {
                    continue;
                }

                $rawTokens[$wordLower] = true;

                $normWord = $this->normalizeSimilarityText($word);

                if (strlen($normWord) >= 3 && ! isset($tokens[$normWord])) {
                    $tokens[$normWord] = $word;
                }
            }
        }

        return [
            'phrases' => $phrases,
            'tokens' => $tokens,
            'raw_phrases' => array_values(array_unique($rawPhrases)),
            'raw_tokens' => $rawTokens,
        ];
    }

    /**
     * @param  array{
     *     phrases: array<string, string>,
     *     tokens: array<string, string>,
     *     raw_phrases: array<int, string>,
     *     raw_tokens: array<string, bool>
     * }  $index
     */
    private function matchesExactPreserveDiacritics(string $rawLower, array $index): bool
    {
        if (isset($index['raw_tokens'][$rawLower])) {
            return true;
        }

        if (mb_strlen($rawLower, 'UTF-8') < 3) {
            return false;
        }

        foreach ($index['raw_phrases'] as $phrase) {
            if ($phrase === $rawLower) {
                return true;
            }
        }

        if (! str_contains($rawLower, ' ')) {
            foreach ($index['raw_phrases'] as $phrase) {
                if (str_contains($phrase, $rawLower)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array{
     *     phrases: array<string, string>,
     *     tokens: array<string, string>,
     *     raw_phrases: array<int, string>,
     *     raw_tokens: array<string, bool>
     * }  $index
     * @return array{
     *     original: string,
     *     corrected: string,
     *     original_query: string,
     *     corrected_query: string,
     *     is_corrected: bool,
     *     suggestions: array<int, string>,
     *     expansion_terms: array<int, string>
     * }
     */
    private function resolveSingleTerm(string $originalKeyword, string $cleanedInput, array $index): array
    {
        $normInput = $this->normalizeSimilarityText($cleanedInput);
        $rawLower = mb_strtolower($cleanedInput, 'UTF-8');

        if ($normInput === '' || strlen($normInput) < 3) {
            return $this->formatTypoResult($originalKeyword, $cleanedInput, false, [], [$cleanedInput]);
        }

        $pool = $index['tokens'] + $index['phrases'];

        if (isset($pool[$normInput])) {
            $canonical = $pool[$normInput];
            $isChanged = mb_strtolower($canonical, 'UTF-8') !== $rawLower;

            return $this->formatTypoResult(
                $originalKeyword,
                $canonical,
                $isChanged,
                $isChanged ? [$canonical] : [],
                [$canonical]
            );
        }

        $candidates = $this->rankSimilarityCandidates($cleanedInput, $pool, 0.68, 3);

        if ($candidates === []) {
            return $this->formatTypoResult($originalKeyword, $cleanedInput, false, [], [$cleanedInput]);
        }

        $bestTerm = $candidates[0]['term'];
        $suggestionTerms = array_values(array_unique(array_column($candidates, 'term')));
        $isCorrected = mb_strtolower($bestTerm, 'UTF-8') !== $rawLower;

        return $this->formatTypoResult(
            $originalKeyword,
            $bestTerm,
            $isCorrected,
            $isCorrected ? $suggestionTerms : [],
            $suggestionTerms
        );
    }

    /**
     * @param  array{
     *     phrases: array<string, string>,
     *     tokens: array<string, string>,
     *     raw_phrases: array<int, string>,
     *     raw_tokens: array<string, bool>
     * }  $index
     * @return array{
     *     original: string,
     *     corrected: string,
     *     original_query: string,
     *     corrected_query: string,
     *     is_corrected: bool,
     *     suggestions: array<int, string>,
     *     expansion_terms: array<int, string>
     * }
     */
    private function resolveMultiWordQuery(string $originalKeyword, string $cleanedInput, array $index): array
    {
        $normFull = $this->normalizeSimilarityText($cleanedInput);
        $rawLower = mb_strtolower($cleanedInput, 'UTF-8');

        if (isset($index['phrases'][$normFull])) {
            $canonicalPhrase = $index['phrases'][$normFull];
            $isChanged = mb_strtolower($canonicalPhrase, 'UTF-8') !== $rawLower;

            return $this->formatTypoResult(
                $originalKeyword,
                $canonicalPhrase,
                $isChanged,
                $isChanged ? [$canonicalPhrase] : [],
                [$canonicalPhrase]
            );
        }

        $wholeCandidates = $this->rankSimilarityCandidates($cleanedInput, $index['phrases'], 0.78, 3);

        if ($wholeCandidates !== []) {
            $bestPhrase = $wholeCandidates[0]['term'];
            $terms = array_values(array_unique(array_column($wholeCandidates, 'term')));

            return $this->formatTypoResult($originalKeyword, $bestPhrase, true, $terms, $terms);
        }

        $words = preg_split('/\s+/u', $cleanedInput) ?: [];
        $count = count($words);
        $resolvedParts = [];
        $expansionTerms = [];
        $suggestions = [];
        $anyCorrected = false;

        for ($i = 0; $i < $count; $i++) {
            $word = $words[$i];
            $wordLower = mb_strtolower($word, 'UTF-8');

            // Cek frasa 2-kata terlebih dahulu agar frasa bertipe "Rumah Bolon" / "Suku Nias" tetap tertangkap
            if ($i < $count - 1) {
                $bigram = $word.' '.$words[$i + 1];
                $normBigram = $this->normalizeSimilarityText($bigram);

                if (isset($index['phrases'][$normBigram])) {
                    $canonicalBigram = $index['phrases'][$normBigram];
                    if (mb_strtolower($canonicalBigram, 'UTF-8') !== mb_strtolower($bigram, 'UTF-8')) {
                        $anyCorrected = true;
                    }
                    $resolvedParts[] = $canonicalBigram;
                    $expansionTerms[] = $canonicalBigram;
                    $suggestions[] = $canonicalBigram;
                    $i++;

                    continue;
                }

                $bigramMatches = $this->rankSimilarityCandidates($bigram, $index['phrases'], 0.75, 2);

                if ($bigramMatches !== []) {
                    $bestBigram = $bigramMatches[0]['term'];
                    $anyCorrected = true;
                    $resolvedParts[] = $bestBigram;
                    foreach ($bigramMatches as $match) {
                        $expansionTerms[] = $match['term'];
                        $suggestions[] = $match['term'];
                    }
                    $i++;

                    continue;
                }
            }

            if (isset($index['raw_tokens'][$wordLower])) {
                $resolvedParts[] = $word;
                $expansionTerms[] = $word;

                continue;
            }

            $tokenNorm = $this->normalizeSimilarityText($word);

            if (isset($index['tokens'][$tokenNorm])) {
                $canonicalToken = $index['tokens'][$tokenNorm];
                if (mb_strtolower($canonicalToken, 'UTF-8') !== $wordLower) {
                    $anyCorrected = true;
                }
                $resolvedParts[] = $canonicalToken;
                $expansionTerms[] = $canonicalToken;
                $suggestions[] = $canonicalToken;

                continue;
            }

            $tokenMatches = $this->rankSimilarityCandidates($word, $index['tokens'], 0.68, 2);

            if ($tokenMatches !== []) {
                $bestToken = $tokenMatches[0]['term'];
                $anyCorrected = true;
                $resolvedParts[] = $bestToken;
                foreach ($tokenMatches as $match) {
                    $expansionTerms[] = $match['term'];
                    $suggestions[] = $match['term'];
                }
            } else {
                $resolvedParts[] = $word;
                $expansionTerms[] = $word;
            }
        }

        $correctedQuery = implode(' ', $resolvedParts);
        $uniqueExpansion = array_values(array_unique(array_filter($expansionTerms)));
        $uniqueSuggestions = array_values(array_unique(array_filter(
            $anyCorrected ? array_merge([$correctedQuery], $suggestions) : []
        )));

        return $this->formatTypoResult(
            $originalKeyword,
            $correctedQuery,
            $anyCorrected,
            $uniqueSuggestions,
            $uniqueExpansion !== [] ? $uniqueExpansion : [$correctedQuery]
        );
    }

    /**
     * @param  array<string, string>  $candidatesPool
     * @return array<int, array{term: string, score: float, lenDiff: int}>
     */
    private function rankSimilarityCandidates(
        string $input,
        array $candidatesPool,
        float $threshold,
        int $limit
    ): array {
        $normInput = $this->normalizeSimilarityText($input);
        $inputLen = strlen($normInput);

        if ($inputLen < 3) {
            return [];
        }

        $scored = [];

        foreach ($candidatesPool as $normCand => $canonicalCand) {
            $candLen = strlen((string) $normCand);

            if ($candLen < 3 || abs($inputLen - $candLen) > max(3, (int) ceil($inputLen * 0.6))) {
                continue;
            }

            $score = $this->calculateHybridSimilarity($normInput, (string) $normCand);

            if ($score < $threshold) {
                continue;
            }

            $key = mb_strtolower($canonicalCand, 'UTF-8');

            if (! isset($scored[$key]) || $score > $scored[$key]['score']) {
                $scored[$key] = [
                    'term' => $canonicalCand,
                    'score' => $score,
                    'lenDiff' => abs($inputLen - $candLen),
                ];
            }
        }

        if ($scored === []) {
            return [];
        }

        $list = array_values($scored);

        usort($list, static function (array $a, array $b): int {
            if ($b['score'] !== $a['score']) {
                return $b['score'] <=> $a['score'];
            }

            return $a['lenDiff'] <=> $b['lenDiff'];
        });

        $bestScore = $list[0]['score'];
        $closeMatches = array_values(array_filter(
            $list,
            static fn (array $item): bool => ($bestScore - $item['score']) <= 0.06
        ));

        return array_slice($closeMatches, 0, $limit);
    }

    /**
     * @param  array<int, string>  $suggestions
     * @param  array<int, string>  $expansionTerms
     * @return array{
     *     original: string,
     *     corrected: string,
     *     original_query: string,
     *     corrected_query: string,
     *     is_corrected: bool,
     *     suggestions: array<int, string>,
     *     expansion_terms: array<int, string>
     * }
     */
    private function formatTypoResult(
        string $original,
        string $corrected,
        bool $isCorrected,
        array $suggestions,
        array $expansionTerms
    ): array {
        return [
            'original' => $original,
            'corrected' => $corrected,
            'original_query' => $original,
            'corrected_query' => $corrected,
            'is_corrected' => $isCorrected,
            'suggestions' => $suggestions,
            'expansion_terms' => $expansionTerms,
        ];
    }

    /**
     * @param  array<int, string>  $terms
     * @return array<int, string>
     */
    private function sanitizeExpansionTerms(array $terms, string $fallback): array
    {
        $cleaned = [];

        foreach ($terms as $term) {
            $safe = $this->sanitizeInput($term);

            if ($safe !== '' && mb_strlen($safe, 'UTF-8') >= 3) {
                $cleaned[] = $safe;
            }
        }

        $unique = array_values(array_unique($cleaned));

        if ($unique === [] && $fallback !== '') {
            return [$fallback];
        }

        return array_slice($unique, 0, 5);
    }

    private function stripExtraPunctuation(string $text): string
    {
        $withoutPunct = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;

        return trim((string) preg_replace('/\s+/u', ' ', $withoutPunct));
    }

    private function normalizeSimilarityText(string $text): string
    {
        $diacriticMap = [
            'ö' => 'o', 'Ö' => 'o',
            'ä' => 'a', 'Ä' => 'a',
            'ü' => 'u', 'Ü' => 'u',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
            'Á' => 'a', 'À' => 'a', 'Â' => 'a', 'Ã' => 'a',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
            'Ó' => 'o', 'Ò' => 'o', 'Ô' => 'o', 'Õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u',
            'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
            'ç' => 'c', 'Ç' => 'c',
        ];

        $ascii = strtr($text, $diacriticMap);
        $lower = mb_strtolower($ascii, 'UTF-8');
        $clean = preg_replace('/[^a-z0-9\s]+/', ' ', $lower) ?? $lower;
        $collapsed = trim((string) preg_replace('/\s+/', ' ', $clean));
        $oldSpelling = str_replace(['oe', 'dj'], ['u', 'j'], $collapsed);

        return (string) preg_replace('/(.)\1+/', '$1', $oldSpelling);
    }

    private function sortCharacters(string $text): string
    {
        $chars = str_split($text);
        sort($chars);

        return implode('', $chars);
    }

    private function calculateEditDistance(string $source, string $target): int
    {
        $levDistance = levenshtein($source, $target);

        if ($levDistance <= 1) {
            return $levDistance;
        }

        $lenA = strlen($source);
        $lenB = strlen($target);

        if ($lenA === 0 || $lenB === 0 || abs($lenA - $lenB) > 4) {
            return $levDistance;
        }

        $dp = [];

        for ($i = 0; $i <= $lenA; $i++) {
            $dp[$i][0] = $i;
        }

        for ($j = 0; $j <= $lenB; $j++) {
            $dp[0][$j] = $j;
        }

        for ($i = 1; $i <= $lenA; $i++) {
            for ($j = 1; $j <= $lenB; $j++) {
                $cost = $source[$i - 1] === $target[$j - 1] ? 0 : 1;

                $dp[$i][$j] = min(
                    $dp[$i - 1][$j] + 1,
                    $dp[$i][$j - 1] + 1,
                    $dp[$i - 1][$j - 1] + $cost
                );

                if (
                    $i > 1
                    && $j > 1
                    && $source[$i - 1] === $target[$j - 2]
                    && $source[$i - 2] === $target[$j - 1]
                ) {
                    $dp[$i][$j] = min($dp[$i][$j], $dp[$i - 2][$j - 2] + 1);
                }
            }
        }

        return min($levDistance, $dp[$lenA][$lenB]);
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
     * @param  array<int, string>  $expansionTerms
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
    private function aggregateSearchBindings(array $bindings, bool $hasKeyword, array $expansionTerms = []): array
    {
        $grouped = [];
        $normExpansion = array_values(array_filter(array_unique(array_map(
            fn (string $term): string => $this->normalizeSimilarityText($term),
            $expansionTerms
        ))));

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

            $matchedTermsCount = 0;

            if (count($normExpansion) > 1) {
                $haystack = $this->normalizeSimilarityText(
                    $item['name'].' '.$tribeLabel.' '.$regionLabel.' '.implode(' ', $item['matchPaths'])
                );

                foreach ($normExpansion as $term) {
                    if ($term !== '' && str_contains($haystack, $term)) {
                        $matchedTermsCount++;
                    }
                }
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
                '_matchedTermsCount' => $matchedTermsCount,
            ];
        }

        usort($results, static function (array $a, array $b): int {
            if ($b['_matchedTermsCount'] !== $a['_matchedTermsCount']) {
                return $b['_matchedTermsCount'] <=> $a['_matchedTermsCount'];
            }

            if ($b['relevanceScore'] !== $a['relevanceScore']) {
                return $b['relevanceScore'] <=> $a['relevanceScore'];
            }

            return strcmp($a['name'], $b['name']);
        });

        return array_map(static function (array $row): array {
            unset($row['_matchedTermsCount']);

            return $row;
        }, $results);
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

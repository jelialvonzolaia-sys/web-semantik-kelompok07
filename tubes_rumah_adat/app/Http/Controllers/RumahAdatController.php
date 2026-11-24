<?php

namespace App\Http\Controllers;

use App\Services\SparqlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RumahAdatController extends Controller
{
    public function __construct(private readonly SparqlService $sparql) {}

    public function index(): View
    {
        $stats = $this->sparql->getStats();
        $statsAvailable = $this->sparql->isFusekiAvailable();
        $statsError = $this->sparql->getLastError();

        $featuredRegions = $this->sparql->getFeaturedRegions(6);
        $fusekiAvailable = $statsAvailable && $this->sparql->isFusekiAvailable();
        $fusekiError = $statsError ?? $this->sparql->getLastError();

        return view('pages.home', [
            'stats' => $stats,
            'featuredRegions' => $featuredRegions,
            'fusekiAvailable' => $fusekiAvailable,
            'fusekiError' => $fusekiError,
        ]);
    }

    public function search(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));
        $filters = [
            'suku' => $this->normalizeFilterValues($request->query('suku', [])),
            'wilayah' => $this->normalizeFilterValues($request->query('wilayah', [])),
            'tradisi' => $this->normalizeFilterValues($request->query('tradisi', [])),
            'ornamen' => $this->normalizeFilterValues($request->query('ornamen', [])),
            'bahasa' => $this->normalizeFilterValues($request->query('bahasa', [])),
            'marga' => $this->normalizeFilterValues($request->query('marga', [])),
        ];

        $houses = $this->sparql->search($keyword, $filters);
        $typoResolution = $this->sparql->getLastTypoResolution() ?? [
            'original' => $keyword,
            'corrected' => $keyword,
            'is_corrected' => false,
        ];
        $searchAvailable = $this->sparql->isFusekiAvailable();
        $searchError = $this->sparql->getLastError();

        $filterOptions = $this->sparql->getFilterOptions();
        $fusekiAvailable = $searchAvailable && $this->sparql->isFusekiAvailable();
        $fusekiError = $searchError ?? $this->sparql->getLastError();

        return view('pages.search', [
            'houses' => $houses,
            'filterOptions' => $filterOptions,
            'keyword' => $keyword,
            'original' => $typoResolution['original'],
            'corrected' => $typoResolution['corrected'],
            'is_corrected' => $typoResolution['is_corrected'],
            'typoResolution' => $typoResolution,
            'activeFilters' => $filters,
            'fusekiAvailable' => $fusekiAvailable,
            'fusekiError' => $fusekiError,
        ]);
    }

    public function detail(string $slug): View
    {
        $house = $this->sparql->getDetail($slug);

        if (! $this->sparql->isFusekiAvailable()) {
            abort(503, $this->sparql->getLastError() ?? 'Server Apache Jena Fuseki belum dapat dihubungi.');
        }

        abort_if($house === null, 404);

        return view('pages.detail', [
            'house' => $house,
            'fusekiAvailable' => true,
        ]);
    }

    public function graph(Request $request): View
    {
        $focus = trim((string) $request->query('focus', ''));
        $keyword = trim((string) $request->query('q', ''));

        return view('pages.graph', [
            'focus' => $focus !== '' ? $focus : null,
            'keyword' => $keyword !== '' ? $keyword : null,
        ]);
    }

    public function apiGraph(Request $request): JsonResponse
    {
        $focus = trim((string) ($request->query('focus') ?? $request->query('id') ?? ''));
        $keyword = trim((string) ($request->query('q') ?? $request->query('keyword') ?? ''));

        $data = $this->sparql->getGraphData(
            $focus !== '' ? $focus : null,
            $keyword !== '' ? $keyword : null
        );

        $data['meta']['fusekiAvailable'] = $this->sparql->isFusekiAvailable();
        $data['meta']['fusekiError'] = $this->sparql->getLastError();

        return response()->json($data);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeFilterValues(mixed $raw): array
    {
        $items = is_array($raw) ? $raw : [$raw];
        $cleaned = [];

        foreach ($items as $item) {
            if (is_scalar($item)) {
                $trimmed = trim((string) $item);

                if ($trimmed !== '') {
                    $cleaned[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($cleaned));
    }
}

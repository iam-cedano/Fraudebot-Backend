<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Http\Resources\Public\ReportCardResource;
use App\Repositories\Search\SearchRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private SearchRepositoryInterface $searchRepository)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $clue = new Clue($request->input('q'));
        $page = max(1, (int) $request->input('p', 1));
        $count = max(1, min(100, (int) $request->input('c', 10)));

        $items = $this->searchRepository->find($clue, $page, $count);

        return response()->json([
            'data' => ReportCardResource::collection($items)->resolve(),
            'total' => $items->count(),
            'page' => $page,
            'count' => $count,
        ]);
    }
}

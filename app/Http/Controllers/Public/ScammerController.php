<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ContactResource;
use App\Http\Resources\Public\MapResource;
use App\Http\Resources\Public\ScammerResource;
use App\Repositories\Scammer\ScammerRepositoryInterface;
use Illuminate\Http\Request;

class ScammerController extends Controller
{
    public function __construct(private ScammerRepositoryInterface $scammerRepository)
    {
    }

    public function show(Request $request, string $id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid scammer ID'], 400);
        }

        $scammer = $this->scammerRepository->findScammerById((int) $id);

        if (!$scammer) {
            return response()->json(['message' => 'Scammer not found'], 404);
        }

        return response()->json((new ScammerResource($scammer))->resolve());
    }

    public function calendar(Request $request, string $id, string $year)
    {
        if (
            filter_var($id, FILTER_VALIDATE_INT) === false
            || filter_var($year, FILTER_VALIDATE_INT) === false
            || (int) $year < 2000
            || (int) $year > now()->year + 1
        ) {
            return response()->json(['message' => 'Invalid scammer ID or year'], 400);
        }

        $calendar = $this->scammerRepository->findCalendarByScammerIdAndYear((int) $id, (int) $year);

        if (!$calendar) {
            return response()->json(['message' => 'Scammer calendar not found'], 404);
        }

        return response()->json($calendar);
    }

    public function contacts(Request $request, string $id)
    {
        $page = $request->input('p', 1);
        $count = $request->input('c', 10);
        $platform = $request->input('platform', null);

        if ($platform) {
            $platform = strtolower($platform);
        }

        if (
            filter_var($id, FILTER_VALIDATE_INT) === false ||
            filter_var($page, FILTER_VALIDATE_INT) === false ||
            filter_var($count, FILTER_VALIDATE_INT) === false ||
            (int) $page < 1 ||
            (int) $page > 100000 ||
            (int) $count < 1 ||
            (int) $count > 100
        ) {
            return response()->json(['message' => 'Invalid scammer ID, page or count'], 400);
        }

        $contacts = $this->scammerRepository->findPaginatedContactsById((int) $id, (int) $page, (int) $count, $platform);

        if (!$contacts) {
            return response()->json(['message' => 'Scammer contacts not found'], 404);
        }

        return response()->json([
            'data' => ContactResource::collection($contacts->items)->resolve(),
            'total' => $contacts->total,
            'page' => (int) $page,
            'count' => (int) $count,
        ]);
    }

    public function map(Request $request, string $id)
    {
        $depth = $request->input('depth', 1);
        $limit = $request->input('limit', 20);

        if (
            filter_var($id, FILTER_VALIDATE_INT) === false ||
            filter_var($depth, FILTER_VALIDATE_INT) === false ||
            filter_var($limit, FILTER_VALIDATE_INT) === false ||
            (int) $depth < 1 ||
            (int) $limit < 1 ||
            (int) $id < 1
        ) {
            return response()->json(['message' => 'Invalid scammer ID, depth or limit'], 400);
        }

        $map = $this->scammerRepository->findMapById((int) $id, (int) $depth, (int) $limit);

        if (!$map) {
            return response()->json(['message' => 'Scammer map not found'], 404);
        }

        if ($map->isEmpty()) {
            return response()->json(['message' => 'Scammer map is empty'], 404);
        }

        return response()->json($map->toJson());
    }
}

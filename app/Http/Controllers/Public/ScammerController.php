<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
        if (filter_var($id, FILTER_VALIDATE_INT) === false || filter_var($year, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid scammer ID or year'], 400);
        }

        $calendar = $this->scammerRepository->findCalendarByScammerIdAndYear((int) $id, (int) $year);

        if (!$calendar) {
            return response()->json(['message' => 'Scammer not found'], 404);
        }

        return response()->json($calendar);
    }
}
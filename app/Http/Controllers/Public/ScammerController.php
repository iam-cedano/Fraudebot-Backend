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

    public function show(Request $request, int $id)
    {
        $scammer = $this->scammerRepository->findScammerById($id);

        if (!$scammer) {
            return response()->json(['message' => 'Scammer not found'], 404);
        }

        return response()->json((new ScammerResource($scammer))->resolve());
    }
}
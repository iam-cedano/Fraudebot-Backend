<?php

namespace App\Http\Controllers\Public;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Http\Controllers\Controller;
use App\Models\Scammer;
use App\Repositories\Scammer\ScammerRepositoryInterface;
use Illuminate\Http\Request;

class ScammerController extends Controller
{
    public function __construct(private ScammerRepositoryInterface $scammerRepository) {}

    /**
     * Display a listing of the resource. If parameter "q" is provided, it will be used as a clue to search for scammers. Otherwise, it will return a paginated list of active scammers.
     */
    public function index(Request $request)
    {
        $clue = $request->input('q');

        $page = $request->input('p', 1);
        $count = 10;

        $scammers = collect([]);

        if ($clue) {
            $clueObject = new Clue($clue);
            $scammers = $this->scammerRepository->find($clueObject, $page, $count);
        } else {
            $scammers = $this->scammerRepository->findAll($page, $count);
        }

        return response()->json($scammers);
    }
}

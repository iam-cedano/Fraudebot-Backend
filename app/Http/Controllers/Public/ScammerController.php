<?php

namespace App\Http\Controllers\Public;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Http\Controllers\Controller;
use App\Http\Resources\BasicScammerPaymentMethodResource;
use App\Models\Scammer;
use App\Repositories\Scammer\ScammerRepositoryInterface;
use App\Http\Resources\BasicScammerProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

    public function show(Request $request, Scammer $scammer)
    {
        $response = [
            'id' => $scammer->id,
            'name' => $scammer->name,
            'iso_country' => $scammer->iso_country,
            'is_active' => $scammer->is_active,
        ];

        if ($request->query('withProfiles') ==   'basic') {
            $response['profiles'] = BasicScammerProfileResource::collection($scammer->profiles);
        }

        if ($request->query('withPaymentMethods') == 'basic') {
            $response['payment_methods'] = BasicScammerPaymentMethodResource::collection($scammer->paymentMethods);
        }

        return response()->json($response);
    }
}
<?php

namespace App\Http\Controllers\Public;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\BasicScammerResource;
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
        $scammers = collect([]);    
        $relationships = [];

        $clue = $request->input('q');
        $page = $request->input('p', 1);

        $count = 10;

        if ($request->query('withProfiles') == 'basic') {
            $relationships[] = 'profiles';
        }

        if ($request->query('withPaymentMethods') == 'basic') {
            $relationships[] = 'paymentMethods';
        }

        if ($request->query('withOrganizations') == 'basic') {
            $relationships[] = 'organizations';
        }

        if ($clue) {
            $clueObject = new Clue($clue);
            $scammers = $this->scammerRepository->find($clueObject, $page, $count, $relationships);
        } else {
            $scammers = $this->scammerRepository->findAll($page, $count, $relationships);
        }

        return BasicScammerResource::collection($scammers);
    }

    public function show(Request $request, Scammer $scammer)
    {
        if ($request->query('withProfiles') == 'basic') {
            $scammer->load('profiles');
        }

        if ($request->query('withPaymentMethods') == 'basic') {
            $scammer->load('paymentMethods');
        }

        if ($request->query('withOrganizations') == 'basic') {
            $scammer->load('organizations');
        }

        return new BasicScammerResource($scammer);
    }
}
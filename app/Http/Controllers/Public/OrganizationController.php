<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\BasicOrganizationResource;
use App\Http\Resources\Public\BasicPaymentMethodResource;
use App\Http\Resources\Public\BasicScammerResource;
use App\Models\Organization;
use App\Models\Scammer;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $relationships = [];

        if ($request->query('withScammers') === 'basic') {
            $relationships[] = 'scammers';
        }

        if ($request->query('withPaymentMethods') === 'basic') {
            $relationships[] = 'paymentMethods';
        }

        return response()->json($this->organizationRepository->getActiveOrganizations($relationships));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Organization $organization)
    {
        $organizationData = (new BasicOrganizationResource($organization))->toArray($request);

        if ($request->query('withScammers') === 'basic') {
            $organizationData['scammers'] = BasicScammerResource::collection($organization->scammers);
        }

        if ($request->query('withPaymentMethods') === 'basic') {
            $organizationData['paymentMethods'] = BasicPaymentMethodResource::collection($organization->paymentMethods);
        }

        return response()->json($organizationData);
    }
}

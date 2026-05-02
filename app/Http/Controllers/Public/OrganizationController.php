<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Scammer;
use App\Repositories\Organization\OrganizationRepositoryInterface;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json($this->organizationRepository->getActiveOrganizations());
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        if (!$organization->is_active) {
            abort(404);
        }

        $organizationData = $organization->only(['id', 'name', 'description', 'is_active']);

        if (request()->query('withScammers') === 'basic') {
            $organizationData['scammers'] = $this->organizationRepository->getActiveScammers($organization)
                ->map(fn (Scammer $scammer) => [
                    'id' => $scammer->id,
                    'name' => $scammer->name,
                ])
                ->all();
        }

        return response()->json($organizationData);
    }
}

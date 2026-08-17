<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Http\Resources\Public\OrganizationResource;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {
    }

    public function show(Request $request, int $id)
    {
        $organization = $this->organizationRepository->findOrganizationById($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json((new OrganizationResource($organization))->resolve());
    }
}

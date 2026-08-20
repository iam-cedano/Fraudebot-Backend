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

    public function show(Request $request, string $id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid organization ID'], 400);
        }

        $organization = $this->organizationRepository->findOrganizationById((int) $id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json((new OrganizationResource($organization))->resolve());
    }

    public function calendar(Request $request, string $id, string $year)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || filter_var($year, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid organization ID or year'], 400);
        }

        $calendar = $this->organizationRepository->findCalendarByOrganizationIdAndYear((int) $id, (int) $year);

        if (!$calendar) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json($calendar);
    }
}

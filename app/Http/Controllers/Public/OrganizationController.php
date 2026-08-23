<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ContactResource;
use App\Http\Resources\Public\OrganizationResource;
use App\Repositories\Organization\OrganizationRepositoryInterface;
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

    public function contacts(Request $request, string $id)
    {
        $page = $request->input('p', 1);
        $count = $request->input('c', 10);
        $platform = $request->input('platform', null);

        if ($platform) {
            $platform = strtolower($platform);
        }

        if (filter_var($id, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid organization ID'], 400);
        }
        if (filter_var($page, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid page'], 400);
        }
        if (filter_var($count, FILTER_VALIDATE_INT) === false) {
            return response()->json(['message' => 'Invalid count'], 400);
        }


        $contacts = $this->organizationRepository->findPaginatedContactsById((int) $id, (int) $page, (int) $count, $platform);

        if (!$contacts) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json([
            'data' => ContactResource::collection($contacts)->resolve(),
            'total' => $contacts->count(),
            'page' => (int) $page,
            'count' => (int) $count,
        ]);
    }
}

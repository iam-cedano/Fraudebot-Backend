<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Scammer;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Organization::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $organization = Organization::create($request->all());

        return response()->json($organization, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        $organizationData = $organization->toArray();

        if (request()->query('withScammers') === 'basic') {
            $organizationData['scammers'] = $organization
                ->scammers()
                ->orderBy('scammers.id')
                ->get()
                ->map(fn (Scammer $scammer) => [
                    'id' => $scammer->id,
                    'name' => $scammer->name,
                ])
                ->all();
        }

        return response()->json($organizationData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $organization->update($request->all());

        return response()->json($organization);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();

        return response()->json(null, 204);
    }

    /**
     * Add a scammer to the given organization.
     */
    public function addScammer(int $organizationId, int $scammerId)
    {
        $organization = Organization::findOrFail($organizationId);
        $scammer = Scammer::findOrFail($scammerId);

        $organization->scammers()->syncWithoutDetaching([$scammer->id]);

        return response()->json(['message' => 'Scammer added successfully'], 201);
    }

    /**
     * Display all scammers that belong to the organization.
     */
    public function getScammers(Organization $organization)
    {
        return response()->json($organization->scammers);
    }
}

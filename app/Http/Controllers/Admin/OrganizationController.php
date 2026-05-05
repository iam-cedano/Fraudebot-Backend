<?php

namespace App\Http\Controllers\Admin;

use App\Domain\OrganizationPaymentMethod\OrganizationPaymentMethodEntity;
use App\Domain\PaymentMethod\PaymentMethodEntity;
use App\Domain\PaymentMethod\ValueObjects\Reference;
use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BasicOrganizationResource;
use App\Http\Resources\Admin\BasicPaymentMethodResource;
use App\Http\Resources\Admin\BasicScammerResource;
use App\Models\Organization;
use App\Models\Scammer;
use Illuminate\Http\Request;
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

        $resource = new BasicOrganizationResource($organization);

        return response()->json($resource, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        $organizationData = $organization->toArray();

        if (request()->query('withScammers') === 'basic') {
            $organizationData['scammers'] = BasicScammerResource::collection($organization->scammers);
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
            'is_active' => 'sometimes|boolean',
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
     * Restore the specified resource from storage.
     */
    public function restore(int $id)
    {
        $organization = Organization::onlyTrashed()->findOrFail($id);
        $organization->restore();

        $resource = new BasicOrganizationResource($organization);

        return response()->json($resource);
    }

    /**
     * Add a scammer to the given organization.
     */
    public function addScammer(Organization $organization, Scammer $scammer)
    {
        $organization->scammers()->syncWithoutDetaching([$scammer->id]);

        return response()->json(['message' => 'Scammer added successfully'], 201);
    }

    public function createPaymentMethod(Request $request, Organization $organization)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string|max:255',
            'payment_type' => 'required',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $inputPaymentType = $request->input('payment_type');

        $paymentMethodType = new PaymentMethodEntity($inputPaymentType);

        $reference = new Reference($request->input('reference'));

        $paymentMethodEntity = new OrganizationPaymentMethodEntity(
            id: null,
            organizationId: $organization->id,
            reference: $reference->getValue(),
            paymentType: $paymentMethodType->getValue(),
            isActive: $request->input('is_active', true),
        );    
    
        $paymentMethod = $organization->paymentMethods()->create($paymentMethodEntity->toArray());

        $resource = new BasicPaymentMethodResource($paymentMethod);

        return response()->json($resource, 201);
    }

    /**
     * Display all scammers that belong to the organization.
     */
    public function getScammers(Organization $organization)
    {
        return response()->json($organization->scammers);
    }
}

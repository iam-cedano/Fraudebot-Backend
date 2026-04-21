<?php

namespace App\Http\Controllers\Admin;

use App\Domain\ScammerProfile\Enums\SocialMediaType;
use App\Domain\ScammerProfile\ScammerProfileEntity;
use App\Http\Controllers\Controller;
use App\Models\Scammer;
use App\Models\ScammerPaymentMethod;
use App\Models\ScammerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScammerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Scammer::with(['profiles', 'paymentMethods', 'organizations'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'iso_country' => 'nullable|string|size:2',
            'is_active' => 'boolean',
            'profiles' => 'sometimes|App\Models\ScammerProfile|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scammer = Scammer::create($request->all());

        return response()->json($scammer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Scammer $scammer)
    {
        return response()->json($scammer->load(['profiles', 'paymentMethods', 'organizations']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Scammer $scammer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'iso_country' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scammer->update($request->all());

        return response()->json($scammer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scammer $scammer)
    {
        $scammer->delete();

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(int $id)
    {
        $scammer = Scammer::onlyTrashed()->findOrFail($id);
        $scammer->restore();

        return response()->json($scammer);
    }

    /**
     * Edit profile information of a scammer
     */
    public function updateProfile(Request $request, ScammerProfile $profile)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:50',
            'social_media' => 'sometimes|integer|between:1,255',
            'contact' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profile->update($request->all());

        return response()->json($profile);
    }

    public function createProfile(Request $request, Scammer $scammer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'social_media' => ['required', Rule::enum(SocialMediaType::class)],
            'contact' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profile = new ScammerProfileEntity(
            id: null,
            scammerId: $scammer->id,
            name: $request->input('name'),
            socialMedia: SocialMediaType::from($request->input('social_media')),
            contact: $request->input('contact'),
            isActive: $request->input('is_active', true),
        );

        $profileModel = $scammer->profiles()->create($profile->toArray());

        return response()->json($profileModel, 201);
    }

    /**
     * Add a payment method to a scammer
     */
    public function createPaymentMethod(Request $request, Scammer $scammer)
    {
        $request->merge([
            'reference' => str_replace(' ', '', $request->input('reference')),
        ]);

        $validator = Validator::make($request->all(), [
            'reference' => 'required|string|max:255',
            'is_active' => 'boolean',   
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentMethod = new ScammerPaymentMethod($request->all());
        
        $scammer->paymentMethods()->save($paymentMethod);

        return response()->json($paymentMethod, 201);
    }
}

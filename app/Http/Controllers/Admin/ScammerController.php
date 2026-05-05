<?php

namespace App\Http\Controllers\Admin;

use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Domain\ScammerPaymentMethod\ScammerPaymentMethodEntity;
use App\Domain\ScammerProfile\Enums\PlatformType;
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
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'iso_country' => 'nullable|string|size:2',
            'is_active' => 'boolean',
            'profiles' => 'sometimes|array',
            'profiles.*.name' => 'required_with:profiles|string|max:50',
            'profiles.*.platform' => 'required_with:profiles',
            'profiles.*.contact' => 'required_with:profiles|string|max:100',
            'profiles.*.is_active' => 'boolean',
            'paymentMethods' => 'sometimes|array',
            'paymentMethods.*.reference' => 'required_with:paymentMethods|string|max:255',
            'paymentMethods.*.payment_type' => 'required_with:paymentMethods',
            'paymentMethods.*.is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scammer = Scammer::create($request->only(['name', 'iso_country', 'is_active']));

        if ($request->has('profiles')) {
            foreach ($request->input('profiles') as $profileData) {
                $inputPlatform = $profileData['platform'];
                if (is_numeric($inputPlatform)) {
                    $platform = PlatformType::tryFrom((int)$inputPlatform) ?? throw new \InvalidArgumentException('Invalid platform type');
                } else {
                    $medias = array_column(PlatformType::cases(), 'value', 'name');
                    $mediaNumber = $medias[strtoupper($inputPlatform)] ?? throw new \InvalidArgumentException('Invalid platform type');
                    $platform = PlatformType::from($mediaNumber);
                }

                $profileEntity = new ScammerProfileEntity(
                    id: null,
                    scammerId: $scammer->id,
                    name: $profileData['name'],
                    platformType: $platform,
                    contact: $profileData['contact'],
                    isActive: $profileData['is_active'] ?? true,
                );
                $scammer->profiles()->create($profileEntity->toArray());
            }
        }

        if ($request->has('paymentMethods')) {
            foreach ($request->input('paymentMethods') as $pmData) {
                $inputPaymentType = $pmData['payment_type'];
                if (is_numeric($inputPaymentType)) {
                    $paymentMethodType = PaymentMethodType::tryFrom((int)$inputPaymentType) ?? throw new \InvalidArgumentException('Invalid payment method type');
                } else {
                    $paymentMethodTypes = array_column(PaymentMethodType::cases(), 'value', 'name');
                    $paymentMethodNumber = $paymentMethodTypes[strtoupper($inputPaymentType)] ?? throw new \InvalidArgumentException('Invalid payment method type');
                    $paymentMethodType = PaymentMethodType::from($paymentMethodNumber);
                }

                $paymentMethodEntity = new ScammerPaymentMethodEntity(
                    id: null,
                    scammerId: $scammer->id,
                    paymentType: $paymentMethodType,
                    reference: trim($pmData['reference']),
                    isActive: $pmData['is_active'] ?? true,
                );
                $scammer->paymentMethods()->create($paymentMethodEntity->toArray());
            }
        }

        return response()->json($scammer->load(['profiles', 'paymentMethods']), 201);
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
            'platform' => 'sometimes',
            'contact' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $platform = $profile->platform;

        if ($request->has('platform')) {
            $inputPlatform = $request->input('platform');

            if (is_numeric($inputPlatform)) {
                $platform = PlatformType::tryFrom($inputPlatform) ?? throw new \InvalidArgumentException('Invalid platform type');
            } else {
                $medias = array_column(PlatformType::cases(), 'value', 'name');
                $mediaNumber = $medias[strtoupper($inputPlatform)] ?? throw new \InvalidArgumentException('Invalid platform type');
                $platform = PlatformType::from($mediaNumber);
            }
        }

        $profileEntity = new ScammerProfileEntity(
            id: $profile->id,
            scammerId: $profile->scammer_id,
            name: $request->input('name', $profile->name),
            platformType: $platform,
            contact: $request->input('contact', $profile->contact),
            isActive: $request->input('is_active', $profile->is_active),
        );

        $profile->update($profileEntity->toArray());

        return response()->json([
            'id' => $profile->id,
            'scammer_id' => $profile->scammer_id,
            'name' => $profile->name,
            'platform' => $profile->platform_name,
            'contact' => $profile->contact,
            'is_active' => $profile->is_active,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at,
        ]);
    }

    // Create profile of a scammer
    public function createProfile(Request $request, Scammer $scammer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'platform' => 'required',
            'contact' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $inputPlatform = $request->input('platform');

        $platform = null;

        if (is_numeric($inputPlatform)) {
            $platform = PlatformType::tryFrom($inputPlatform) ?? throw new \InvalidArgumentException('Invalid platform type');
        } else {
            $medias = array_column(PlatformType::cases(), 'value', 'name');

            $mediaNumber = $medias[strtoupper($inputPlatform)] ?? throw new \InvalidArgumentException('Invalid platform type');
            
            $platform = PlatformType::from($mediaNumber);
        }

        $profile = new ScammerProfileEntity(
            id: null,
            scammerId: $scammer->id,
            name: $request->input('name'),
            platformType: $platform,
            contact: $request->input('contact'),
            isActive: $request->input('is_active', true),
        );

        $profileModel = $scammer->profiles()->create($profile->toArray());

        return response()->json([
            'id' => $profileModel->id,
            'scammer_id' => $profileModel->scammer_id,
            'name' => $profileModel->name,
            'platform' => $profileModel->platform_name,
            'contact' => $profileModel->contact,
            'is_active' => $profileModel->is_active,
            'created_at' => $profileModel->created_at,
            'updated_at' => $profileModel->updated_at,
        ], 201);
    }

    /**
     * Add a payment method to a scammer
     */
    public function createPaymentMethod(Request $request, Scammer $scammer)
    {
        $request->merge([
            'reference' => trim($request->input('reference')),
        ]);

        $validator = Validator::make($request->all(), [
            'reference' => 'required|string|max:255',
            'payment_type' => 'required',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $inputPaymentType = $request->input('payment_type');

        $paymentMethodType = null;

        if (is_numeric($inputPaymentType)) {
            $paymentMethodType = PaymentMethodType::tryFrom($inputPaymentType) ?? throw new \InvalidArgumentException('Invalid payment method type');
        } else {
            $paymentMethodTypes = array_column(PaymentMethodType::cases(), 'value', 'name');

            $paymentMethodNumber = $paymentMethodTypes[strtoupper($inputPaymentType)] ?? throw new \InvalidArgumentException('Invalid payment method type');
        
            $paymentMethodType = PaymentMethodType::from($paymentMethodNumber);
        }

        $paymentMethod = new ScammerPaymentMethodEntity(
            id: null,
            scammerId: $scammer['id'],
            paymentType: $paymentMethodType,
            reference: $request->input('reference'),
            isActive: $request->input('is_active', true),
        );


        if ($scammer->paymentMethods()->where(['reference' => $paymentMethod->reference, 'payment_type' => $paymentMethod->paymentType->value])->exists()) {
            return response()->json(['error' => 'Payment method with the same reference already exists for this scammer'], 422);
        }

        $paymentMethodModel = $scammer->paymentMethods()->create($paymentMethod->toArray());

        $response = $paymentMethodModel->only(['id', 'scammer_id', 'reference', 'payment_type_name', 'is_active', 'created_at', 'updated_at']);

        return response()->json($response, 201);
    }
}

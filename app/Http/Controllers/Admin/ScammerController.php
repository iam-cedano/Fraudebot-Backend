<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Contact\ContactEntity;
use App\Domain\Contact\Enums\PlatformType;
use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Scammer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScammerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Scammer::with(['contacts', 'paymentMethods', 'organizations'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|size:2',
            'is_active' => 'boolean',
            'contacts' => 'sometimes|array',
            'contacts.*.name' => 'required_with:contacts|string|max:50',
            'contacts.*.platform' => 'required_with:contacts',
            'contacts.*.reference' => 'required_with:contacts|string|max:255',
            'contacts.*.is_active' => 'boolean',
            'paymentMethods' => 'sometimes|array',
            'paymentMethods.*.reference' => 'required_with:paymentMethods|string|max:255',
            'paymentMethods.*.type' => 'required_with:paymentMethods',
            'paymentMethods.*.is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $scammer = Scammer::create($request->only(['name', 'country', 'is_active']));

        if ($request->has('contacts')) {
            foreach ($request->input('contacts') as $contactData) {
                $inputPlatform = $contactData['platform'];
                if (is_numeric($inputPlatform)) {
                    $platform = PlatformType::tryFrom((int)$inputPlatform) ?? throw new \InvalidArgumentException('Invalid platform type');
                } else {
                    $medias = array_column(PlatformType::cases(), 'value', 'name');
                    $mediaNumber = $medias[strtoupper($inputPlatform)] ?? throw new \InvalidArgumentException('Invalid platform type');
                    $platform = PlatformType::from($mediaNumber);
                }

                $contactEntity = new ContactEntity(
                    id: null,
                    name: $contactData['name'],
                    platformType: $platform,
                    reference: $contactData['reference'],
                    isActive: $contactData['is_active'] ?? true,
                );
                $scammer->contacts()->create($contactEntity->toArray());
            }
        }

        if ($request->has('paymentMethods')) {
            foreach ($request->input('paymentMethods') as $pmData) {
                $inputPaymentType = $pmData['type'];
                if (is_numeric($inputPaymentType)) {
                    $paymentMethodType = PaymentMethodType::tryFrom((int)$inputPaymentType) ?? throw new \InvalidArgumentException('Invalid payment method type');
                } else {
                    $paymentMethodTypes = array_column(PaymentMethodType::cases(), 'value', 'name');
                    $paymentMethodNumber = $paymentMethodTypes[strtoupper($inputPaymentType)] ?? throw new \InvalidArgumentException('Invalid payment method type');
                    $paymentMethodType = PaymentMethodType::from($paymentMethodNumber);
                }

                $scammer->paymentMethods()->create([
                    'type' => $paymentMethodType,
                    'reference' => trim($pmData['reference']),
                    'is_active' => $pmData['is_active'] ?? true,
                ]);
            }
        }

        return response()->json($scammer->load(['contacts', 'paymentMethods']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Scammer $scammer)
    {
        return response()->json($scammer->load(['contacts', 'paymentMethods', 'organizations']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Scammer $scammer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'country' => 'nullable|string|size:2',
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
     * Edit contact information of a scammer
     */
    public function updateContact(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:50',
            'platform' => 'sometimes',
            'reference' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $platform = $contact->platform;

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

        $contactEntity = new ContactEntity(
            id: $contact->id,
            name: $request->input('name', $contact->name),
            platformType: $platform,
            reference: $request->input('reference', $contact->reference),
            isActive: $request->input('is_active', $contact->is_active),
        );

        $contact->update($contactEntity->toArray());

        return response()->json([
            'id' => $contact->id,
            'name' => $contact->name,
            'platform' => $contact->platform_name,
            'reference' => $contact->reference,
            'is_active' => $contact->is_active,
            'created_at' => $contact->created_at,
            'updated_at' => $contact->updated_at,
        ]);
    }

    // Create contact of a scammer
    public function createContact(Request $request, Scammer $scammer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'platform' => 'required',
            'reference' => 'required|string|max:255',
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

        $contact = new ContactEntity(
            id: null,
            name: $request->input('name'),
            platformType: $platform,
            reference: $request->input('reference'),
            isActive: $request->input('is_active', true),
        );

        $contactModel = $scammer->contacts()->create($contact->toArray());

        return response()->json([
            'id' => $contactModel->id,
            'name' => $contactModel->name,
            'platform' => $contactModel->platform_name,
            'reference' => $contactModel->reference,
            'is_active' => $contactModel->is_active,
            'created_at' => $contactModel->created_at,
            'updated_at' => $contactModel->updated_at,
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
            'type' => 'required',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $inputPaymentType = $request->input('type');

        $paymentMethodType = null;

        if (is_numeric($inputPaymentType)) {
            $paymentMethodType = PaymentMethodType::tryFrom($inputPaymentType) ?? throw new \InvalidArgumentException('Invalid payment method type');
        } else {
            $paymentMethodTypes = array_column(PaymentMethodType::cases(), 'value', 'name');

            $paymentMethodNumber = $paymentMethodTypes[strtoupper($inputPaymentType)] ?? throw new \InvalidArgumentException('Invalid payment method type');
        
            $paymentMethodType = PaymentMethodType::from($paymentMethodNumber);
        }

        if ($scammer->paymentMethods()->where(['reference' => $request->input('reference'), 'type' => $paymentMethodType->value])->exists()) {
            return response()->json(['error' => 'Payment method with the same reference already exists for this scammer'], 422);
        }

        $paymentMethodModel = $scammer->paymentMethods()->create([
            'type' => $paymentMethodType,
            'reference' => $request->input('reference'),
            'is_active' => $request->input('is_active', true),
        ]);

        $response = $paymentMethodModel->only(['id', 'reference', 'type_name', 'is_active', 'created_at']);
        $response['updated_at'] = $paymentMethodModel->modified_at;

        return response()->json($response, 201);
    }
}

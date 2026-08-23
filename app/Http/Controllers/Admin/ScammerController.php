<?php

namespace App\Http\Controllers\Admin;

use App\Application\Admin\AttachContactAction;
use App\Application\Admin\AttachPaymentMethodAction;
use App\Application\Admin\CreateScammerAction;
use App\Domain\Contact\ContactEntity;
use App\Domain\Contact\Enums\PlatformType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactRequest;
use App\Http\Requests\Admin\PaymentMethodRequest;
use App\Http\Requests\Admin\StoreScammerRequest;
use App\Http\Requests\Admin\UpdateScammerRequest;
use App\Models\Contact;
use App\Models\Scammer;

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
    public function store(StoreScammerRequest $request, CreateScammerAction $action)
    {
        return response()->json($action->execute($request->validated()), 201);
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
    public function update(UpdateScammerRequest $request, Scammer $scammer)
    {
        $scammer->update($request->validated());

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
    public function restore(int $scammer)
    {
        $model = Scammer::onlyTrashed()->findOrFail($scammer);
        $model->restore();

        return response()->json($model);
    }

    /**
     * Edit contact information of a scammer
     */
    public function updateContact(ContactRequest $request, Scammer $scammer, Contact $contact)
    {
        abort_unless($scammer->contacts()->whereKey($contact->id)->exists(), 404);

        $platform = $contact->platform;

        if ($request->has('platform')) {
            $platform = PlatformType::from((int) $request->validated('platform'));
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
    public function createContact(ContactRequest $request, Scammer $scammer, AttachContactAction $action)
    {
        $contactModel = $action->execute($scammer, $request->validated());

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
    public function createPaymentMethod(PaymentMethodRequest $request, Scammer $scammer, AttachPaymentMethodAction $action)
    {
        $data = $request->validated();
        if ($scammer->paymentMethods()->where(['reference' => $data['reference'], 'type' => $data['type']])->exists()) {
            return response()->json(['error' => 'Payment method with the same reference already exists for this scammer'], 422);
        }

        $paymentMethodModel = $action->execute($scammer, $data);

        $response = $paymentMethodModel->only(['id', 'reference', 'type_name', 'is_active', 'created_at']);
        $response['updated_at'] = $paymentMethodModel->modified_at;

        return response()->json($response, 201);
    }
}

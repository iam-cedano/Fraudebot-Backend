<?php

namespace App\Application\Admin;

use App\Domain\Contact\ContactEntity;
use App\Domain\Contact\Enums\PlatformType;
use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Models\Contact;
use App\Models\PaymentMethod;
use App\Models\Scammer;
use App\Repositories\Search\SearchCache;
use Illuminate\Support\Facades\DB;

final class CreateScammerAction
{
    public function execute(array $data): Scammer
    {
        return DB::transaction(function () use ($data): Scammer {
            $scammer = Scammer::create([
                'name' => trim($data['name']),
                'country' => $data['country'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($data['contacts'] ?? [] as $contactData) {
                $entity = new ContactEntity(
                    id: null,
                    name: $contactData['name'],
                    platformType: PlatformType::from((int) $contactData['platform']),
                    reference: $contactData['reference'],
                    isActive: $contactData['is_active'] ?? true,
                );
                $values = $entity->toArray();
                unset($values['id']);

                $contact = Contact::withTrashed()->firstOrCreate(
                    ['platform' => $values['platform'], 'reference' => $values['reference']],
                    ['name' => $values['name'], 'is_active' => $values['is_active']],
                );
                if ($contact->trashed()) {
                    $contact->restore();
                }
                $scammer->contacts()->syncWithoutDetaching([$contact->id]);
            }

            foreach ($data['paymentMethods'] ?? [] as $paymentMethodData) {
                $paymentMethod = PaymentMethod::withTrashed()->firstOrCreate(
                    [
                        'type' => PaymentMethodType::from((int) $paymentMethodData['type']),
                        'reference' => trim($paymentMethodData['reference']),
                    ],
                    ['is_active' => $paymentMethodData['is_active'] ?? true],
                );
                if ($paymentMethod->trashed()) {
                    $paymentMethod->restore();
                }
                $scammer->paymentMethods()->syncWithoutDetaching([$paymentMethod->id]);
            }

            SearchCache::invalidate();

            return $scammer->load(['contacts', 'paymentMethods']);
        });
    }
}

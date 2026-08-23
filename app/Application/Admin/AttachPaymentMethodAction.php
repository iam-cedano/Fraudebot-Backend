<?php

namespace App\Application\Admin;

use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Scammer;
use App\Repositories\Search\SearchCache;
use Illuminate\Support\Facades\DB;

final class AttachPaymentMethodAction
{
    public function execute(Scammer|Organization $owner, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($owner, $data): PaymentMethod {
            $identity = [
                'type' => PaymentMethodType::from((int) $data['type']),
                'reference' => trim($data['reference']),
            ];
            $paymentMethod = PaymentMethod::withTrashed()->firstOrCreate(
                $identity,
                ['is_active' => $data['is_active'] ?? true],
            );
            if ($paymentMethod->trashed()) {
                $paymentMethod->restore();
            }

            $owner->paymentMethods()->syncWithoutDetaching([$paymentMethod->id]);
            SearchCache::invalidate();

            return $paymentMethod;
        });
    }
}

<?php

namespace App\Application\Admin;

use App\Domain\Contact\ContactEntity;
use App\Domain\Contact\Enums\PlatformType;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\Scammer;
use App\Repositories\Search\SearchCache;
use Illuminate\Support\Facades\DB;

final class AttachContactAction
{
    public function execute(Scammer|Organization $owner, array $data): Contact
    {
        return DB::transaction(function () use ($owner, $data): Contact {
            $entity = new ContactEntity(
                id: null,
                name: $data['name'],
                platformType: PlatformType::from((int) $data['platform']),
                reference: $data['reference'],
                isActive: $data['is_active'] ?? true,
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

            $owner->contacts()->syncWithoutDetaching([$contact->id]);
            SearchCache::invalidate();

            return $contact;
        });
    }
}

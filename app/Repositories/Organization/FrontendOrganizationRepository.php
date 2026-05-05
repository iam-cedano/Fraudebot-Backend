<?php
namespace App\Repositories\Organization;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Organization;

class FrontendOrganizationRepository implements OrganizationRepositoryInterface {

    private function getEagerLoads(array $relationships): array
    {
        $eagerLoads = [];
        foreach ($relationships as $relationship) {
            if ($relationship === 'scammers') {
                $eagerLoads['scammers'] = function ($q) {
                    $q->select('scammers.id', 'name', 'iso_country', 'is_active');
                };
            } elseif ($relationship === 'paymentMethods') {
                $eagerLoads['paymentMethods'] = function ($q) {
                    $q->select('id', 'organization_id', 'payment_type', 'reference', 'is_active');
                };
            }
        }
        return $eagerLoads;
    }

    public function getActiveOrganizations(array $relationships = []): Collection {
        $query = Organization::where('is_active', true);
        
        $eagerLoads = $this->getEagerLoads($relationships);
        if (!empty($eagerLoads)) {
            $query->with($eagerLoads);
        }

        $organizations = $query->get();

        $organizations->each(function ($organization) {
            if ($organization->relationLoaded('scammers')) {
                $organization->scammers->makeHidden(['pivot']);
            }
            if ($organization->relationLoaded('paymentMethods')) {
                $organization->paymentMethods->makeHidden(['payment_type', 'organization_id']);
            }
        });

        return $organizations;
    }

    public function getActiveScammers(Organization $organization): Collection {
        return $organization->scammers()
            ->where('is_active', true)
            ->orderBy('scammers.id')
            ->get();
    }
}

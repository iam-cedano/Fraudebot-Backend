<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\Scammer;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $scammers = (int) env('SEED_SCAMMERS', 1);
        $contacts = (int) env('SEED_CONTACTS', 3);
        $organizations = (int) env('SEED_ORGANIZATIONS', 1);

        Scammer::factory()
            ->count($scammers)
            ->has(Organization::factory())
            ->has(Contact::factory()->count($contacts)->state(['organization_id' => null]))
            ->create();

        Organization::factory()
            ->count($organizations)
            ->has(Contact::factory()->count($contacts)->state(['scammer_id' => null]))
            ->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\Report;
use App\Models\Scammer;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $scammers = (int) env('SEED_SCAMMERS', 1);
        $contacts = (int) env('SEED_CONTACTS', 3);
        $organizations = (int) env('SEED_ORGANIZATIONS', 1);
        $reports = (int) env('SEED_REPORTS', 2);

        if ($scammers == 0 || $contacts == 0 || $organizations == 0 || $reports == 0) {
            $this->command->error('SEED_SCAMMERS, SEED_CONTACTS, SEED_ORGANIZATIONS, and SEED_REPORTS must be greater than 0');

            exit(1);
        }

        Scammer::factory()
            ->count($scammers)
            ->has(Organization::factory())
            ->has(Contact::factory()->count($contacts)->state(['organization_id' => null]))
            ->has(Report::factory()->count($reports)->state(['organization_id' => null]))
            ->create();

        Organization::factory()
            ->count($organizations)
            ->has(Contact::factory()->count($contacts)->state(['scammer_id' => null]))
            ->has(Report::factory()->count($reports)->state(['scammer_id' => null]))
            ->create();
    }
}

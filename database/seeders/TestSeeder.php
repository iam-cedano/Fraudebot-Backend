<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Report;
use App\Models\Scammer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $scammers = (int) env('SEED_SCAMMERS', 1);
        $contacts = (int) env('SEED_CONTACTS', 3);
        $organizations = (int) env('SEED_ORGANIZATIONS', 1);
        $reports = (int) env('SEED_REPORTS', 2);
        $users = (int) env('SEED_USERS', 1);
        $products = (int) env('SEED_PRODUCTS', 1);

        if ($scammers == 0 || $contacts == 0 || $organizations == 0 || $reports == 0 || $users == 0 || $products == 0) {
            $this->command->error('SEED_SCAMMERS, SEED_CONTACTS, SEED_ORGANIZATIONS, SEED_REPORTS, SEED_USERS, and SEED_PRODUCTS must be greater than 0');

            exit(1);
        }

        $userIds = User::factory()->count($users)->create()->pluck('id');
        $productIds = Product::factory()->count($products)->create()->pluck('id');

        Scammer::factory()
            ->count($scammers)
            ->has(Organization::factory())
            ->has(Contact::factory()->count($contacts)->state(['organization_id' => null]))
            ->has(Report::factory()->count($reports)->state(function (array $attributes, Scammer $scammer) use ($userIds, $productIds) {
                return [
                    'organization_id' => $scammer->organizations()->first()->id,
                    'user_id' => $userIds->random(),
                    'product_id' => $productIds->random(),
                ];
            }))
            ->create();

        Organization::factory()
            ->count($organizations)
            ->has(Contact::factory()->count($contacts)->state(['scammer_id' => null]))
            ->has(Report::factory()->count($reports)->state(fn() => [
                'scammer_id' => null,
                'user_id' => $userIds->random(),
                'product_id' => $productIds->random(),
            ]))
            ->create();
    }
}

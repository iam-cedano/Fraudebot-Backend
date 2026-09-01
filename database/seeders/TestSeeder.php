<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Report;
use App\Models\Scammer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection as SupportCollection;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $scammers = (int) env('SEED_SCAMMERS', 1);
        $contacts = (int) env('SEED_CONTACTS', 3);
        $paymentMethods = (int) env('SEED_PAYMENT_METHODS', 3);
        $organizations = (int) env('SEED_ORGANIZATIONS', 1);
        $reports = (int) env('SEED_REPORTS', 2);
        $users = (int) env('SEED_USERS', 1);
        $products = (int) env('SEED_PRODUCTS', 1);

        if ($scammers == 0 || $contacts == 0 || $paymentMethods == 0 || $organizations == 0 || $reports == 0 || $users == 0 || $products == 0) {
            $this->command->error('SEED_SCAMMERS, SEED_CONTACTS, SEED_PAYMENT_METHODS, SEED_ORGANIZATIONS, SEED_REPORTS, SEED_USERS, and SEED_PRODUCTS must be greater than 0');

            exit(1);
        }

        $userIds = User::factory()->count($users)->create()->pluck('id');
        $productIds = Product::factory()->count($products)->create()->pluck('id');

        $createdScammers = Scammer::factory()
            ->count($scammers)
            ->has(Organization::factory())
            ->has(Contact::factory()->count($contacts))
            ->has(PaymentMethod::factory()->count($paymentMethods))
            ->has(Report::factory()->count($reports)->state(fn () => [
                'user_id' => $userIds->random(),
            ]))
            ->afterCreating(function (Scammer $scammer) use ($productIds) {
                $this->attachProductsToReports($scammer->reports, $productIds);

                $organization = $scammer->organizations()->first();

                if ($organization === null) {
                    return;
                }

                $organization->reports()->syncWithoutDetaching(
                    $scammer->reports()->pluck('reports.id')->all(),
                );
            })
            ->create();

        Organization::factory()
            ->count($organizations)
            ->has(Contact::factory()->count($contacts))
            ->has(PaymentMethod::factory()->count($paymentMethods))
            ->has(Report::factory()->count($reports)->state(fn () => [
                'user_id' => $userIds->random(),
            ]))
            ->afterCreating(function (Organization $organization) use ($productIds) {
                $this->attachProductsToReports($organization->reports, $productIds);
            })
            ->create();

        $this->attachExtraScammersToSomeOrganizations($createdScammers);
    }

    /**
     * @param  Collection<int, Scammer>  $scammers
     */
    private function attachExtraScammersToSomeOrganizations(Collection $scammers): void
    {
        if ($scammers->count() < 2) {
            return;
        }

        $organizations = Organization::query()->get();

        if ($organizations->isEmpty()) {
            return;
        }

        $shareCount = $organizations->count() === 1
            ? 1
            : (int) ceil($organizations->count() / 2);

        foreach ($organizations->random($shareCount)->values() as $organization) {
            $linkedIds = $organization->scammers()->pluck('scammers.id');
            $candidates = $scammers->whereNotIn('id', $linkedIds->all())->values();

            if ($candidates->isEmpty()) {
                continue;
            }

            $minimum = max(1, 2 - $linkedIds->count());
            $take = $candidates->count() <= $minimum
                ? $candidates->count()
                : fake()->numberBetween($minimum, $candidates->count());

            $organization->scammers()->syncWithoutDetaching(
                $candidates->random($take)->pluck('id')->all(),
            );
        }
    }

    /**
     * @param  Collection<int, Report>  $reports
     * @param  SupportCollection<int, int>  $productIds
     */
    private function attachProductsToReports(Collection $reports, SupportCollection $productIds): void
    {
        foreach ($reports as $report) {
            $take = $productIds->count() === 1
                ? 1
                : fake()->numberBetween(1, $productIds->count());

            $report->products()->syncWithoutDetaching(
                $productIds->random($take)->values()->all(),
            );
        }
    }
}

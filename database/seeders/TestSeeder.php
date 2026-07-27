<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    private array $scammers;
    private array $organizationContacts;
    private array $organizations;
    public function __construct()
    {
        $this->scammers = [
            'Ecohuerta' => [
                [
                    'name' => 'Mario Lopez',
                    'iso_country' => 'MX',
                    'is_active' => true,
                    'contacts' => [
                        [
                            'name' => 'Mario Lopez',
                            'platform' => 1,
                            'contact' => '+521111111111'
                        ]
                    ],
                    'payment_methods' => [
                        [
                            'bank_number' => '1234567890',
                            'iso_country' => 'MX',
                            'is_active' => true
                        ]
                    ]
                ]
            ]
        ];
        $this->organizationContacts = [
            'Ecohuerta' => [
                [
                    'name' => 'Website',
                    'platform' => 9,
                    'contact' => 'https://example.com',
                    'is_active' => true
                ],
                [
                    'name' => 'WhatsApp Group',
                    'platform' => 1,
                    'contact' => '+521111111111',
                    'is_active' => true
                ]
            ]
        ];
        $this->organizations = [
            'Ecohuerta' => [
                'name' => 'Ecohuerta',
                'description' => 'Ecohuertas is a PlatformType that runs a Ponzi scheme',
                'is_active' => true,
            ]
        ];
    }

    public function run(): void
    {
        foreach ($this->organizations as $key => $data) {
            $org = Organization::create($data);
            
            if (isset($this->organizationContacts[$key])) {
                foreach ($this->organizationContacts[$key] as $contact) {
                    $org->contacts()->create($contact);
                }
            }

            if (isset($this->scammers[$key])) {
                foreach ($this->scammers[$key] as $scammerData) {
                    $contacts = $scammerData['contacts'] ?? [];
                    $paymentMethods = $scammerData['payment_methods'] ?? [];
                    
                    unset($scammerData['contacts'], $scammerData['payment_methods']);
                    
                    $scammer = \App\Models\Scammer::create($scammerData);
                    
                    foreach ($contacts as $contact) {
                        $scammer->contacts()->create($contact);
                    }
                    
                    foreach ($paymentMethods as $paymentMethod) {
                        $scammer->paymentMethods()->create($paymentMethod);
                    }
                    
                    $org->scammers()->attach($scammer->id);
                }
            }
        }
    }
}

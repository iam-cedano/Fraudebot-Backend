<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    private array $scammers;
    private array $organizationAccessPoints;
    private array $organizations;
    public function __construct()
    {
        $this->scammers = [
            'Ecohuerta' => [
                [
                    'name' => 'Mario Lopez',
                    'iso_country' => 'MX',
                    'is_active' => true,
                    'profiles' => [
                        [
                            'name' => 'Mario Lopez',
                            'social_media' => 'whatsapp',
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
        $this->organizationAccessPoints = [
            'Ecohuerta' => [
                [
                    'PlatformType' => 'website',
                    'contact' => 'https://example.com',
                    'is_active' => true
                ],
                [
                    'PlatformType' => 'whatsapp-group',
                    'contact' => '',
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
            
            if (isset($this->organizationAccessPoints[$key])) {
                foreach ($this->organizationAccessPoints[$key] as $accessPoint) {
                    $org->accessPoints()->create($accessPoint);
                }
            }

            if (isset($this->scammers[$key])) {
                foreach ($this->scammers[$key] as $scammerData) {
                    $profiles = $scammerData['profiles'] ?? [];
                    $paymentMethods = $scammerData['payment_methods'] ?? [];
                    
                    unset($scammerData['profiles'], $scammerData['payment_methods']);
                    
                    $scammer = \App\Models\Scammer::create($scammerData);
                    
                    foreach ($profiles as $profile) {
                        $scammer->profiles()->create($profile);
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

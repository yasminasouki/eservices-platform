<?php

namespace Database\Seeders;

use App\Models\GovernmentOffice;
use App\Models\Municipality;
use App\Models\OfficeUserAssignment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────────────────
        $admin = User::updateOrCreate([
            'email' => 'admin@eservices.gov',
        ], [
            'name' => 'System Admin',
            'password' => Hash::make('Admin@12345'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ── Municipality ─────────────────────────────────────────────────────
        $municipality = Municipality::updateOrCreate(
            ['name' => 'Beirut Municipality'],
            [
                'region' => 'Beirut',
                'admin_user_id' => $admin->id,
            ]
        );

        // ── Office user (staff) — safe to re-run (unique email) ───────────────
        $officeUser = User::firstOrCreate(
            ['email' => 'manager@beirut.gov'],
            [
                'name' => 'Office Manager',
                'password' => Hash::make('password'),
                'role' => 'office_user',
                'is_active' => true,
                'phone' => '+961 1 000 000',
            ]
        );
        $officeUser->forceFill(['email_verified_at' => now()])->save();

        // ── Government Office ─────────────────────────────────────────────────
        $office = GovernmentOffice::updateOrCreate(
            ['name' => 'Beirut Civil Registry'],
            [
                'address' => 'Riad Al Solh Square, Beirut',
                'email' => 'registry@beirut.gov',
                'phone' => '+961 1 123 456',
                'google_maps_url' => 'https://maps.google.com/?q=Beirut+Civil+Registry',
                'latitude' => 33.8938,
                'longitude' => 35.5018,
                'working_hours' => [
                    ['day' => 'Monday', 'open' => '08:00', 'close' => '15:00'],
                    ['day' => 'Tuesday', 'open' => '08:00', 'close' => '15:00'],
                    ['day' => 'Wednesday', 'open' => '08:00', 'close' => '15:00'],
                    ['day' => 'Thursday', 'open' => '08:00', 'close' => '15:00'],
                    ['day' => 'Friday', 'open' => '08:00', 'close' => '13:00'],
                ],
                'contact_info' => ['hotline' => '1717', 'fax' => '+961 1 123 457'],
                'is_active' => true,
                'municipality_id' => $municipality->id,
            ]
        );

        OfficeUserAssignment::firstOrCreate(
            [
                'government_office_id' => $office->id,
                'user_id' => $officeUser->id,
            ],
            ['role_in_office' => 'manager']
        );

        // ── Service Category & Services ───────────────────────────────────────
        $category = ServiceCategory::firstOrCreate(
            [
                'name' => 'Civil Documents',
                'government_office_id' => $office->id,
            ],
            [
                'description' => 'Official civil documentation services.',
            ]
        );

        Service::updateOrCreate(
            [
                'name' => 'Birth Certificate',
                'government_office_id' => $office->id,
            ],
            [
                'description' => 'Official birth certificate issuance.',
                'price' => 15.00,
                'duration' => 3,
                'duration_unit' => 'days',
                'required_documents' => ['National ID', 'Hospital Birth Record'],
                'is_active' => true,
                'service_category_id' => $category->id,
            ]
        );

        Service::updateOrCreate(
            [
                'name' => 'Marriage Certificate',
                'government_office_id' => $office->id,
            ],
            [
                'description' => 'Official marriage certificate issuance.',
                'price' => 25.00,
                'duration' => 5,
                'duration_unit' => 'days',
                'required_documents' => ['National ID (both parties)', 'Religious Marriage Contract'],
                'is_active' => true,
                'service_category_id' => $category->id,
            ]
        );

        // ── Demo citizen (known login for /login → citizen portal) ─────────────
        // `social_provider` is set so RequireTwoFactor treats this like a social
        // account and skips TOTP (local/testing convenience only).
        User::updateOrCreate(
            ['email' => 'citizen@eservices.demo'],
            [
                'name' => 'Demo Citizen',
                'password' => Hash::make('Citizen@12345'),
                'role' => 'citizen',
                'is_active' => true,
                'phone' => '+961 70 000 000',
                'email_verified_at' => now(),
                'id_document' => 'seed/demo-id-on-file.placeholder',
                'id_document_status' => 'verified',
                'social_provider' => 'seed',
                'social_provider_id' => 'local-demo-citizen',
            ]
        );

        // ── Extra sample citizens (once; avoids dozens of duplicates when re-seeding)
        if (User::where('role', 'citizen')->whereNot('email', 'citizen@eservices.demo')->doesntExist()) {
            User::factory()->count(9)->create();
        }
    }
}

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
        $admin = User::factory()->admin()->create([
            'name'  => 'System Admin',
            'email' => 'admin@eservices.gov',
        ]);

        // ── Municipality ─────────────────────────────────────────────────────
        $municipality = Municipality::create([
            'name'          => 'Beirut Municipality',
            'region'        => 'Beirut',
            'admin_user_id' => $admin->id,
        ]);

        // ── Office user (staff) ───────────────────────────────────────────────
        $officeUser = User::factory()->officeUser()->create([
            'name'  => 'Office Manager',
            'email' => 'manager@beirut.gov',
        ]);

        // ── Government Office ─────────────────────────────────────────────────
        $office = GovernmentOffice::create([
            'name'            => 'Beirut Civil Registry',
            'address'         => 'Riad Al Solh Square, Beirut',
            'email'           => 'registry@beirut.gov',
            'phone'           => '+961 1 123 456',
            'google_maps_url' => 'https://maps.google.com/?q=Beirut+Civil+Registry',
            'latitude'        => 33.8938,
            'longitude'       => 35.5018,
            'working_hours'   => [
                ['day' => 'Monday',    'open' => '08:00', 'close' => '15:00'],
                ['day' => 'Tuesday',   'open' => '08:00', 'close' => '15:00'],
                ['day' => 'Wednesday', 'open' => '08:00', 'close' => '15:00'],
                ['day' => 'Thursday',  'open' => '08:00', 'close' => '15:00'],
                ['day' => 'Friday',    'open' => '08:00', 'close' => '13:00'],
            ],
            'contact_info'    => ['hotline' => '1717', 'fax' => '+961 1 123 457'],
            'is_active'       => true,
            'municipality_id' => $municipality->id,
        ]);

        // Assign office user to office
        OfficeUserAssignment::create([
            'government_office_id' => $office->id,
            'user_id'              => $officeUser->id,
            'role_in_office'       => 'manager',
        ]);

        // ── Service Category & Services ───────────────────────────────────────
        $category = ServiceCategory::create([
            'name'                 => 'Civil Documents',
            'description'          => 'Official civil documentation services.',
            'government_office_id' => $office->id,
        ]);

        Service::create([
            'name'                 => 'Birth Certificate',
            'description'          => 'Official birth certificate issuance.',
            'price'                => 15.00,
            'duration'             => 3,
            'duration_unit'        => 'days',
            'required_documents'   => ['National ID', 'Hospital Birth Record'],
            'is_active'            => true,
            'service_category_id'  => $category->id,
            'government_office_id' => $office->id,
        ]);

        Service::create([
            'name'                 => 'Marriage Certificate',
            'description'          => 'Official marriage certificate issuance.',
            'price'                => 25.00,
            'duration'             => 5,
            'duration_unit'        => 'days',
            'required_documents'   => ['National ID (both parties)', 'Religious Marriage Contract'],
            'is_active'            => true,
            'service_category_id'  => $category->id,
            'government_office_id' => $office->id,
        ]);

        // ── Sample Citizens ───────────────────────────────────────────────────
        User::factory()->count(10)->create();
    }
}

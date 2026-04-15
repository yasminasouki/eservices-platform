<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\GovernmentOffice;
use App\Models\Municipality;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenAppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_cannot_book_overlapping_appointment(): void
    {
        $citizen = User::factory()->create([
            'social_provider' => 'seed',
        ]);

        $municipality = Municipality::query()->create([
            'name' => 'Beirut',
        ]);

        $officeA = GovernmentOffice::query()->create([
            'name' => 'Civil Registry A',
            'address' => 'Street 1',
            'municipality_id' => $municipality->id,
            'is_active' => true,
        ]);

        $officeB = GovernmentOffice::query()->create([
            'name' => 'Civil Registry B',
            'address' => 'Street 2',
            'municipality_id' => $municipality->id,
            'is_active' => true,
        ]);

        $slotA = OfficerTimeSlot::query()->create([
            'government_office_id' => $officeA->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '10:30',
            'is_available' => false,
        ]);

        Appointment::query()->create([
            'user_id' => $citizen->id,
            'government_office_id' => $officeA->id,
            'officer_time_slot_id' => $slotA->id,
            'status' => 'scheduled',
        ]);

        $slotB = OfficerTimeSlot::query()->create([
            'government_office_id' => $officeB->id,
            'date' => $slotA->date->toDateString(),
            'start_time' => '10:15',
            'end_time' => '10:45',
            'is_available' => true,
        ]);

        $this->actingAs($citizen)
            ->postJson(route('citizen.appointments.store', [$officeB, $slotB]))
            ->assertStatus(422)
            ->assertJson([
                'message' => 'You already have an overlapping appointment at this time.',
            ]);

        $this->assertDatabaseMissing('appointments', [
            'officer_time_slot_id' => $slotB->id,
            'user_id' => $citizen->id,
        ]);

        $this->assertDatabaseHas('officer_time_slots', [
            'id' => $slotB->id,
            'is_available' => true,
        ]);
    }

    public function test_citizen_can_cancel_their_scheduled_appointment(): void
    {
        $citizen = User::factory()->create([
            'social_provider' => 'seed',
        ]);

        $municipality = Municipality::query()->create([
            'name' => 'Beirut',
        ]);

        $office = GovernmentOffice::query()->create([
            'name' => 'Civil Registry',
            'address' => 'Street 1',
            'municipality_id' => $municipality->id,
            'is_active' => true,
        ]);

        $slot = OfficerTimeSlot::query()->create([
            'government_office_id' => $office->id,
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '11:00',
            'end_time' => '11:30',
            'is_available' => false,
        ]);

        $appointment = Appointment::query()->create([
            'user_id' => $citizen->id,
            'government_office_id' => $office->id,
            'officer_time_slot_id' => $slot->id,
            'status' => 'scheduled',
        ]);

        $this->actingAs($citizen)
            ->patch(route('citizen.appointments.cancel', [$office, $appointment]), [
                'cancellation_reason' => 'Cannot attend',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Cannot attend',
        ]);

        $this->assertDatabaseHas('officer_time_slots', [
            'id' => $slot->id,
            'is_available' => true,
        ]);
    }
}

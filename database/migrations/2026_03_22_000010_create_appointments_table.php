<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')             // citizen who booked
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('government_office_id')
                ->constrained('government_offices')
                ->cascadeOnDelete();
            $table->foreignId('officer_time_slot_id') // one-to-one: slot reserved by this appointment
                ->unique()
                ->constrained('officer_time_slots')
                ->cascadeOnDelete();
            $table->foreignId('service_request_id')  // optional link to a service request
                ->nullable()
                ->constrained('service_requests')
                ->nullOnDelete();
            $table->enum('status', ['scheduled', 'confirmed', 'cancelled', 'completed'])
                ->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('government_office_id')
                ->constrained('government_offices')
                ->cascadeOnDelete();
            $table->foreignId('user_id')         // the assigned officer (office_user)
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_time_slots');
    }
};

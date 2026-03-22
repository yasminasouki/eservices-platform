<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('government_office_id')
                ->constrained('government_offices')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('role_in_office')->nullable(); // e.g. manager, clerk, officer
            $table->timestamps();

            $table->unique(['government_office_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_user_assignments');
    }
};

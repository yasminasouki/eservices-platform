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
            $table->foreignId('government_office_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role_in_office')->nullable();
            $table->timestamps();

            $table->unique(['government_office_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_user_assignments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('id_document_path');        // uploaded ID image path
            $table->string('api_provider')->nullable(); // e.g. "jumio", "onfido"
            $table->json('api_response')->nullable();   // raw API response
            $table->string('extracted_name')->nullable();
            $table->date('extracted_dob')->nullable();
            $table->string('extracted_id_number')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_verification_requests');
    }
};

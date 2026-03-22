<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')            // the citizen who submitted
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();
            $table->foreignId('government_office_id')
                ->constrained('government_offices')
                ->cascadeOnDelete();
            $table->foreignId('assigned_officer_id') // office staff handling the request
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('status', [
                'pending',
                'in_review',
                'missing_documents',
                'approved',
                'rejected',
                'completed',
            ])->default('pending');
            $table->string('qr_code')->unique();     // unique QR code for tracking
            $table->text('notes')->nullable();        // citizen-provided notes
            $table->text('rejection_reason')->nullable();
            $table->text('missing_docs_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

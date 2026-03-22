<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();
            $table->foreignId('changed_by')          // user who changed the status
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('from_status')->nullable(); // null on first status entry
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // no updated_at — logs are immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_status_logs');
    }
};

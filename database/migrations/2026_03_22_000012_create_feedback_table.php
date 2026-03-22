<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')             // citizen who left feedback
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('government_office_id')
                ->constrained('government_offices')
                ->cascadeOnDelete();
            $table->foreignId('service_request_id')  // optional link to specific request
                ->nullable()
                ->constrained('service_requests')
                ->nullOnDelete();
            $table->unsignedTinyInteger('rating');    // 1–5 stars
            $table->text('comment')->nullable();
            $table->text('office_reply')->nullable();
            $table->boolean('reply_is_public')->default(true);
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};

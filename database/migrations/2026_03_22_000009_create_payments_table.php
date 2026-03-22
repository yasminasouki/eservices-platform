<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();
            $table->foreignId('user_id')             // payer
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->decimal('exchange_rate', 15, 6)->nullable(); // used for crypto conversions
            $table->enum('method', ['card', 'cryptocurrency', 'cash']);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->unique()->nullable();
            $table->string('crypto_wallet_address')->nullable();
            $table->json('gateway_response')->nullable(); // raw response from payment gateway
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

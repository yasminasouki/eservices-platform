<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('service_category_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration')->nullable();
            $table->enum('duration_unit', ['minutes', 'hours', 'days'])->default('minutes');
            $table->json('required_documents')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('government_office_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

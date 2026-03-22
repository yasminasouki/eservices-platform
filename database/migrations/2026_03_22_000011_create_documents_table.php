<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();
            $table->foreignId('user_id')             // uploader; null if system-generated
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // in bytes
            $table->text('description')->nullable();
            // uploaded   = citizen-uploaded supporting document
            // generated  = system-generated document (e.g., auto-filled form)
            // certificate= official certificate issued by office
            // receipt    = payment receipt
            $table->enum('type', ['uploaded', 'generated', 'certificate', 'receipt'])
                ->default('uploaded');
            $table->enum('uploaded_by', ['citizen', 'office'])->default('citizen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

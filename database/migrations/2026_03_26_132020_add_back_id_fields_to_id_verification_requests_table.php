<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('id_verification_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('id_verification_requests', 'api_response_back')) {
                $table->json('api_response_back')->nullable()->after('api_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('id_verification_requests', function (Blueprint $table) {
            $table->dropColumn('api_response_back');
        });
    }
};

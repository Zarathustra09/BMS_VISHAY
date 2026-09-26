<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // SHA-256 hash of the device's API key (never store the plaintext key).
            $table->string('device_key', 64)->nullable()->unique()->after('is_critical');
            $table->timestamp('last_seen_at')->nullable()->after('device_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn(['device_key', 'last_seen_at']);
        });
    }
};

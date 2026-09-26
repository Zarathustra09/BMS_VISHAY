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
            // Encrypted (recoverable) copy of the device key, for authorized users to view later.
            // `device_key` remains a SHA-256 hash used only for fast/indexed auth lookups.
            $table->text('device_key_encrypted')->nullable()->after('device_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('device_key_encrypted');
        });
    }
};

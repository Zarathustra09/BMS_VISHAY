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
        // A simple key-value store rather than one column per setting — SettingInterface::KEYS
        // is the single source of truth for which keys exist, so adding a new setting never
        // needs a migration. Every value is JSON-encoded (even a plain string or int) so
        // SettingService can decode uniformly regardless of type.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

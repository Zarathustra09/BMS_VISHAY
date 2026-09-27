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
        // Both tables were empty placeholders (id + timestamps only) from the very start —
        // machines.status and machine_logs.log_type have always been plain enum columns
        // instead. Their controllers/models were dead code too (removed alongside this
        // migration); nothing in the app ever queried either table.
        Schema::dropIfExists('machine_statuses');
        Schema::dropIfExists('log_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('machine_statuses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('log_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};

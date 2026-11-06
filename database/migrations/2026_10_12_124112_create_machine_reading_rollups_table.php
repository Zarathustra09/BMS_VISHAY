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
        // One row per machine + number parameter + minute. Built from machine_logs by
        // `app:rollup-readings`, so long chart windows (24h, 7d, 30d) can be drawn from a few
        // thousand rows a day while the detailed per-second logs are pruned much sooner.
        Schema::create('machine_reading_rollups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->string('metric', 64);

            // Epoch seconds of the minute's start. An integer (not a timestamp) so charts can
            // bucket in SQL with `period_start - period_start % size` on MySQL and SQLite alike.
            $table->unsignedInteger('period_start');

            $table->double('min');
            $table->double('max');
            $table->double('sum');
            $table->unsignedInteger('count');

            $table->unique(['machine_id', 'metric', 'period_start'], 'machine_reading_rollups_unique');
            $table->index('period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_reading_rollups');
    }
};

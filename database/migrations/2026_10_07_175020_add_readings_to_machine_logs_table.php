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
        Schema::table('machine_logs', function (Blueprint $table) {
            // Parameter key => value, for the parameters defined on the log's machine
            // (see machine_parameters). Never holds a null value.
            $table->json('readings')->nullable()->after('current_status');

            // Set only on a row that merges several readings sent within the same second
            // (a fast device): parameter key => {min, max, n}. `readings` then holds the
            // averages, and this keeps the spikes the average hides. Null for a single reading.
            $table->json('reading_stats')->nullable()->after('readings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_logs', function (Blueprint $table) {
            $table->dropColumn(['readings', 'reading_stats']);
        });
    }
};

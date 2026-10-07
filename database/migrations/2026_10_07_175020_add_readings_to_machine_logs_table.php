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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_logs', function (Blueprint $table) {
            $table->dropColumn('readings');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the two columns that replace the free-text `type` string and the nine dedicated
     * spec columns — see the two migrations that follow this one. `machine_type_id` is
     * nullable in the schema (tightened only by the `required` validation rule in
     * MachineController::validateMachine(), not a NOT NULL constraint) so the backfill
     * migration can populate it in a separate, chunked step rather than needing every machine
     * typed before this migration can even run — see .claude/skills/laravel-best-practices'
     * migrations rule on staging changes to populated tables.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->foreignId('machine_type_id')->nullable()->after('machine_code')
                ->constrained()->restrictOnDelete();
            // No separate index: constrained() already indexes the FK column on MySQL, and
            // a second one would just duplicate it (see the migrations best-practice rule).

            $table->json('specifications')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machine_type_id');
            $table->dropColumn('specifications');
        });
    }
};

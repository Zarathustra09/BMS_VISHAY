<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops the free-text `type` column and the nine dedicated spec columns, now that
     * backfill_machine_types_on_machines_table has copied every machine's data into
     * `machine_type_id` + `specifications`. `power_rating` and `spec_voltage` are kept —
     * every machine has them regardless of type, so they never moved into the per-type JSON.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // The index on `type` must be dropped before the column itself.
            $table->dropIndex(['type']);

            $table->dropColumn([
                'type',
                'capacity_tons',
                'fuel',
                'autostart',
                'capacity_persons',
                'speed_m_s',
                'flow_lpm',
                'head_m',
                'cells',
                'makeup_valve',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restores the columns with their original definitions (see
     * 2026_02_19_142824_create_machines_table.php) and the `type` index, but **not their
     * data** — that only happens if backfill_machine_types_on_machines_table's own down() is
     * also rolled back, since a rollback runs in reverse migration order and that migration
     * runs before this one.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->string('type')->after('machine_code');
            $table->decimal('capacity_tons', 8, 2)->nullable();
            $table->string('fuel')->nullable();
            $table->boolean('autostart')->default(false);
            $table->integer('capacity_persons')->nullable();
            $table->decimal('speed_m_s', 5, 2)->nullable();
            $table->integer('flow_lpm')->nullable();
            $table->decimal('head_m', 6, 2)->nullable();
            $table->integer('cells')->nullable();
            $table->boolean('makeup_valve')->default(false);

            $table->index(['type']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The group whose members are emailed when this machine raises an alert (see
     * AlertService::notify()). Nullable: a machine with no group alerts the admins/managers only,
     * as before. Deleting a group just clears the field (GroupService also refuses to delete a
     * group a machine still uses).
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->foreignId('assignment_group_id')
                ->nullable()
                ->after('floor_id')
                ->constrained('groups')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_group_id');
        });
    }
};

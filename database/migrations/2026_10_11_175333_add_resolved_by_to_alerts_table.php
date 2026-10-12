<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Who resolved an alert and how. `resolved_by` is null when the system did it (a reading back
     * in range, or the device coming back online); `resolution` says which path closed it, so a
     * manual resolve, a completed work order and an automatic one are distinguishable. Both stay
     * null on alerts resolved before this existed.
     */
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->string('resolution', 30)->nullable()->after('resolved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn('resolution');
        });
    }
};

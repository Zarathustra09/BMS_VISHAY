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
        Schema::create('machine_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('icon', 16)->nullable();
            $table->text('description')->nullable();
            // An ordered list of {key, label, type, unit?, step?, min?, required} objects —
            // see the seed_default_machine_types migration for the shape. Nullable because
            // MySQL can't default a JSON column; application code always writes an array and
            // reads it back with `?? []` (see MachineType::specFields()).
            $table->json('spec_fields')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_types');
    }
};

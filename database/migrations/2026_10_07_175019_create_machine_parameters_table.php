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
        Schema::create('machine_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();

            // Generated once from the label and never changed — it's what machine_logs.readings
            // is indexed by, and what a device sends in its POST body.
            $table->string('key', 50);
            $table->string('label');
            $table->string('unit', 30)->nullable();
            $table->enum('data_type', ['number', 'boolean', 'text'])->default('number');
            $table->boolean('required')->default(false);

            // Accepted range for a number parameter — a reading outside it is rejected by the API.
            $table->decimal('min', 16, 4)->nullable();
            $table->decimal('max', 16, 4)->nullable();

            // Alert bounds (number only). Any bound left null means that side isn't checked.
            $table->decimal('warning_min', 16, 4)->nullable();
            $table->decimal('warning_max', 16, 4)->nullable();
            $table->decimal('critical_min', 16, 4)->nullable();
            $table->decimal('critical_max', 16, 4)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['machine_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_parameters');
    }
};

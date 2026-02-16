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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('machine_code')->unique();
            $table->string('type'); // HVAC, Generator, Elevator, Pump, etc.
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location'); // Building/Floor/Room
            $table->string('zone')->nullable(); // Zone within building
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'fault'])->default('active');
            $table->decimal('power_rating', 10, 2)->nullable(); // in kW
            $table->string('ip_address')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval_days')->nullable();
            $table->decimal('capacity_tons', 8, 2)->nullable();      // for HVAC
            $table->decimal('spec_voltage', 8, 2)->nullable();       // generic voltage spec
            $table->string('fuel')->nullable();                      // generator fuel type
            $table->boolean('autostart')->default(false);            // generator autostart (default false)
            $table->integer('capacity_persons')->nullable();         // elevator capacity
            $table->decimal('speed_m_s', 5, 2)->nullable();          // elevator speed m/s
            $table->integer('flow_lpm')->nullable();                 // pump flow L/min
            $table->decimal('head_m', 6, 2)->nullable();             // pump head meters
            $table->integer('cells')->nullable();                    // cooling tower cells
            $table->boolean('makeup_valve')->default(false);         // cooling tower makeup valve (default false)
            $table->boolean('is_critical')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes helpful for queries in a manufacturing BMS
            $table->index(['type']);
            $table->index(['location']);
            $table->index(['zone']);
            $table->index(['is_critical']);
            $table->index(['manufacturer']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};

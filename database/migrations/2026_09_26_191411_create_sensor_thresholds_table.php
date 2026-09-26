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
        Schema::create('sensor_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');

            // Matches the sensor-reading columns on machine_logs — see AlertInterface::METRICS.
            $table->enum('metric', [
                'temperature', 'humidity', 'pressure', 'power_consumption',
                'voltage', 'current', 'frequency', 'vibration_rms', 'bearing_temp', 'oil_level',
            ]);

            // Any bound left null means that side isn't checked (e.g. a metric with no
            // meaningful lower bound just sets warning_max/critical_max).
            $table->decimal('warning_min', 12, 3)->nullable();
            $table->decimal('warning_max', 12, 3)->nullable();
            $table->decimal('critical_min', 12, 3)->nullable();
            $table->decimal('critical_max', 12, 3)->nullable();

            $table->timestamps();

            $table->unique(['machine_id', 'metric']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_thresholds');
    }
};

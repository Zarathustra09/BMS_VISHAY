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
        Schema::create('machine_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->enum('log_type', ['status_change', 'maintenance', 'alarm', 'sensor_reading', 'operation', 'error']);
            $table->string('event')->nullable(); // Event description
            $table->enum('severity', ['info', 'warning', 'error', 'critical'])->default('info');
            $table->string('previous_status')->nullable();
            $table->string('current_status')->nullable();
            $table->decimal('temperature', 8, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('pressure', 10, 2)->nullable();
            $table->decimal('power_consumption', 10, 2)->nullable(); // in kW
            $table->decimal('voltage', 8, 2)->nullable();
            $table->decimal('current', 8, 2)->nullable();
            $table->decimal('frequency', 8, 2)->nullable();
            $table->integer('runtime_hours')->nullable();
            $table->decimal('vibration_rms', 8, 3)->nullable();
            $table->decimal('bearing_temp', 8, 2)->nullable();
            $table->decimal('oil_level', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('performed_by')->nullable(); // User or system
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['machine_id', 'logged_at']);
            $table->index(['log_type', 'severity']);

            // Additional indexes useful for BMS queries and alerts
            $table->index('logged_at');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_logs');
    }
};

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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');

            // Null for an 'offline' alert (Phase 6) — a threshold alert is raised by one
            // specific reading.
            $table->foreignId('machine_log_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['threshold', 'offline'])->default('threshold');

            // Null for an 'offline' alert; one of AlertInterface::METRICS for a threshold alert.
            $table->string('metric')->nullable();
            $table->decimal('value', 12, 3)->nullable();

            $table->enum('severity', ['warning', 'critical']);
            $table->enum('status', ['open', 'acknowledged', 'resolved'])->default('open');
            $table->text('message');

            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // AlertService::evaluate() looks up "the open alert for this machine+metric" on
            // every reading, so that lookup needs to be fast.
            $table->index(['machine_id', 'metric', 'status']);
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};

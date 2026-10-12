<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The trail of everything that happened to an alert and who did it: raised, escalated,
     * acknowledged, resolved (manually, by a work order, or automatically) and a device coming
     * back online. `user_id` is null when the system acted. Rows are only ever appended.
     */
    public function up(): void
    {
        Schema::create('alert_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->string('severity', 20)->nullable();
            $table->decimal('value', 12, 3)->nullable();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['alert_id', 'created_at']);
        });

        $this->backfillExistingAlerts();
    }

    /**
     * Give alerts that predate the trail a basic history from the columns they already have, so
     * the timeline is never empty for them.
     */
    private function backfillExistingAlerts(): void
    {
        DB::table('alerts')->orderBy('id')->each(function ($alert) {
            $events = [[
                'alert_id' => $alert->id,
                'user_id' => null,
                'action' => 'raised',
                'from_status' => null,
                'to_status' => 'open',
                'severity' => $alert->severity,
                'value' => $alert->value,
                'note' => null,
                'created_at' => $alert->created_at,
            ]];

            if ($alert->acknowledged_at) {
                $events[] = [
                    'alert_id' => $alert->id,
                    'user_id' => $alert->acknowledged_by,
                    'action' => 'acknowledged',
                    'from_status' => 'open',
                    'to_status' => 'acknowledged',
                    'severity' => $alert->severity,
                    'value' => null,
                    'note' => null,
                    'created_at' => $alert->acknowledged_at,
                ];
            }

            if ($alert->resolved_at) {
                $events[] = [
                    'alert_id' => $alert->id,
                    'user_id' => null,
                    'action' => 'resolved',
                    'from_status' => $alert->acknowledged_at ? 'acknowledged' : 'open',
                    'to_status' => 'resolved',
                    'severity' => $alert->severity,
                    'value' => null,
                    'note' => 'Resolved before the alert trail existed',
                    'created_at' => $alert->resolved_at,
                ];
            }

            DB::table('alert_events')->insert($events);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_events');
    }
};

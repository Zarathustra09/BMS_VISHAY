<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The fixed sensor columns being replaced, with the label/unit/scale each one had in
     * AlertInterface::METRIC_LABELS and the original machine_logs migration. Kept here (not
     * read from a constant) because this is a point-in-time data migration.
     *
     * @var array<string, array{label: string, unit: ?string, scale: int}>
     */
    private const METRICS = [
        'temperature' => ['label' => 'Temperature', 'unit' => '°C', 'scale' => 2],
        'humidity' => ['label' => 'Humidity', 'unit' => '%', 'scale' => 2],
        'pressure' => ['label' => 'Pressure', 'unit' => null, 'scale' => 2],
        'power_consumption' => ['label' => 'Power Consumption', 'unit' => 'kW', 'scale' => 2],
        'voltage' => ['label' => 'Voltage', 'unit' => 'V', 'scale' => 2],
        'current' => ['label' => 'Current', 'unit' => 'A', 'scale' => 2],
        'frequency' => ['label' => 'Frequency', 'unit' => 'Hz', 'scale' => 2],
        'runtime_hours' => ['label' => 'Runtime', 'unit' => 'h', 'scale' => 0],
        'vibration_rms' => ['label' => 'Vibration RMS', 'unit' => null, 'scale' => 3],
        'bearing_temp' => ['label' => 'Bearing Temp', 'unit' => '°C', 'scale' => 2],
        'oil_level' => ['label' => 'Oil Level', 'unit' => null, 'scale' => 2],
    ];

    /**
     * Run the migrations.
     *
     * DB::table() only, never the models — their $fillable/$casts describe the schema *after*
     * this migration. A machine gets a parameter for every legacy column it has ever reported
     * (or has a threshold on), carrying its threshold bounds over; every log's non-null column
     * values are folded into its `readings` JSON. Then the legacy columns, the
     * sensor_thresholds table and the default_thresholds setting are removed.
     */
    public function up(): void
    {
        $columns = array_keys(self::METRICS);

        $usedByMachine = [];

        foreach ($columns as $column) {
            DB::table('machine_logs')->whereNotNull($column)->distinct()->pluck('machine_id')
                ->each(function ($machineId) use (&$usedByMachine, $column) {
                    $usedByMachine[$machineId][$column] = true;
                });
        }

        $thresholds = DB::table('sensor_thresholds')->get()->groupBy('machine_id');

        foreach ($thresholds as $machineId => $rows) {
            foreach ($rows as $row) {
                $usedByMachine[$machineId][$row->metric] = true;
            }
        }

        $now = now();

        foreach ($usedByMachine as $machineId => $used) {
            $thresholdByMetric = ($thresholds[$machineId] ?? collect())->keyBy('metric');
            $order = 0;

            foreach ($columns as $column) {
                if (! isset($used[$column])) {
                    continue;
                }

                $meta = self::METRICS[$column];
                $threshold = $thresholdByMetric->get($column);

                DB::table('machine_parameters')->insert([
                    'machine_id' => $machineId,
                    'key' => $column,
                    'label' => $meta['label'],
                    'unit' => $meta['unit'],
                    'data_type' => 'number',
                    'required' => false,
                    'warning_min' => $threshold->warning_min ?? null,
                    'warning_max' => $threshold->warning_max ?? null,
                    'critical_min' => $threshold->critical_min ?? null,
                    'critical_max' => $threshold->critical_max ?? null,
                    'sort_order' => $order++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('machine_logs')->orderBy('id')->chunkById(500, function ($logs) use ($columns) {
            foreach ($logs as $log) {
                $readings = [];

                foreach ($columns as $column) {
                    if ($log->{$column} !== null) {
                        $readings[$column] = self::METRICS[$column]['scale'] === 0
                            ? (int) $log->{$column}
                            : round((float) $log->{$column}, self::METRICS[$column]['scale']);
                    }
                }

                if ($readings !== []) {
                    DB::table('machine_logs')->where('id', $log->id)->update(['readings' => json_encode($readings)]);
                }
            }
        });

        Schema::table('machine_logs', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });

        Schema::dropIfExists('sensor_thresholds');

        DB::table('settings')->where('key', 'default_thresholds')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * Restores the schema and copies readings/thresholds back for parameters whose key is one
     * of the legacy column names; any other parameter's data stays in `readings` (dropped
     * together with that column by the previous migration's own rollback).
     */
    public function down(): void
    {
        Schema::create('sensor_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->enum('metric', [
                'temperature', 'humidity', 'pressure', 'power_consumption',
                'voltage', 'current', 'frequency', 'vibration_rms', 'bearing_temp', 'oil_level',
            ]);
            $table->decimal('warning_min', 12, 3)->nullable();
            $table->decimal('warning_max', 12, 3)->nullable();
            $table->decimal('critical_min', 12, 3)->nullable();
            $table->decimal('critical_max', 12, 3)->nullable();
            $table->timestamps();

            $table->unique(['machine_id', 'metric']);
        });

        Schema::table('machine_logs', function (Blueprint $table) {
            $table->decimal('temperature', 8, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('pressure', 10, 2)->nullable();
            $table->decimal('power_consumption', 10, 2)->nullable();
            $table->decimal('voltage', 8, 2)->nullable();
            $table->decimal('current', 8, 2)->nullable();
            $table->decimal('frequency', 8, 2)->nullable();
            $table->integer('runtime_hours')->nullable();
            $table->decimal('vibration_rms', 8, 3)->nullable();
            $table->decimal('bearing_temp', 8, 2)->nullable();
            $table->decimal('oil_level', 6, 2)->nullable();
        });

        $columns = array_keys(self::METRICS);
        $now = now();

        foreach (DB::table('machine_parameters')->whereIn('key', $columns)->get() as $parameter) {
            $hasBounds = $parameter->warning_min !== null || $parameter->warning_max !== null
                || $parameter->critical_min !== null || $parameter->critical_max !== null;

            if ($hasBounds && $parameter->key !== 'runtime_hours') {
                DB::table('sensor_thresholds')->insert([
                    'machine_id' => $parameter->machine_id,
                    'metric' => $parameter->key,
                    'warning_min' => $parameter->warning_min,
                    'warning_max' => $parameter->warning_max,
                    'critical_min' => $parameter->critical_min,
                    'critical_max' => $parameter->critical_max,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('machine_logs')->whereNotNull('readings')->orderBy('id')->chunkById(500, function ($logs) use ($columns) {
            foreach ($logs as $log) {
                $readings = json_decode($log->readings, true) ?: [];
                $values = array_intersect_key($readings, array_flip($columns));

                if ($values !== []) {
                    DB::table('machine_logs')->where('id', $log->id)->update($values);
                }
            }
        });
    }
};

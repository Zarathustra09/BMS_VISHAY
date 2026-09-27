<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The old dedicated spec columns being read here, and which of the seeded types' JSON
     * field keys each one feeds — see seed_default_machine_types. A key not present in a
     * type's own spec_fields is simply never read for that machine (e.g. a Chiller's
     * `fuel` column, if it somehow had one, is not carried into its `specifications`).
     */
    private const NUMBER_COLUMNS = ['capacity_tons', 'cells', 'capacity_persons', 'speed_m_s', 'flow_lpm', 'head_m'];

    private const BOOLEAN_COLUMNS = ['autostart', 'makeup_valve'];

    private const TEXT_COLUMNS = ['fuel'];

    /**
     * Run the migrations.
     *
     * Chunked by id, using DB::table() only (never the Machine model — this is a point-in-time
     * data migration, and the model's `$fillable`/`$casts` will themselves change once the
     * next migration drops these columns). For each machine: match its old `type` string to a
     * machine_types row by name (case-insensitively, compared in PHP — never in SQL, per the
     * Phase 4 lesson that this table's utf8mb4_unicode_ci collation can't be trusted for
     * exact-match comparisons); create one if nothing matches; build `specifications` from
     * only that type's own field keys, skipping any column that's null.
     */
    public function up(): void
    {
        $typesByLowerName = DB::table('machine_types')->get()->keyBy(fn ($type) => strtolower($type->name));

        DB::table('machines')->orderBy('id')->chunkById(100, function ($machines) use (&$typesByLowerName) {
            foreach ($machines as $machine) {
                $lookupKey = strtolower((string) $machine->type);
                $type = $typesByLowerName->get($lookupKey);

                if (! $type) {
                    $newId = DB::table('machine_types')->insertGetId([
                        'name' => $machine->type ?: 'Unknown',
                        'icon' => 'cog',
                        'description' => null,
                        'spec_fields' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $type = (object) [
                        'id' => $newId,
                        'name' => $machine->type ?: 'Unknown',
                        'spec_fields' => '[]',
                    ];
                    $typesByLowerName->put($lookupKey, $type);
                }

                $specFields = json_decode($type->spec_fields ?? '[]', true) ?: [];
                $specifications = [];

                foreach ($specFields as $field) {
                    $key = $field['key'] ?? null;

                    if (! $key || ! property_exists($machine, $key)) {
                        continue;
                    }

                    $value = $machine->{$key};

                    if ($value === null) {
                        continue;
                    }

                    if (in_array($key, self::BOOLEAN_COLUMNS, true)) {
                        $specifications[$key] = ((int) $value === 1) ? 'Yes' : 'No';
                    } elseif (in_array($key, self::NUMBER_COLUMNS, true)) {
                        $specifications[$key] = is_numeric($value) ? (float) $value : $value;
                    } elseif (in_array($key, self::TEXT_COLUMNS, true)) {
                        $specifications[$key] = (string) $value;
                    }
                }

                DB::table('machines')->where('id', $machine->id)->update([
                    'machine_type_id' => $type->id,
                    'specifications' => json_encode((object) $specifications),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Copies `specifications` values back onto the old columns for machines whose
     * `machine_type_id` this migration itself set (skips a machine reassigned to a different
     * type since). Only meaningful while the old columns still exist — i.e. run before
     * drop_type_and_spec_columns_from_machines_table's own down() re-adds them, since a
     * rollback undoes migrations in reverse order.
     */
    public function down(): void
    {
        $machines = DB::table('machines')
            ->whereNotNull('machine_type_id')
            ->get(['id', 'machine_type_id', 'specifications']);

        $typesById = DB::table('machine_types')->get()->keyBy('id');

        foreach ($machines as $machine) {
            $type = $typesById->get($machine->machine_type_id);
            $specifications = json_decode($machine->specifications ?? '{}', true) ?: [];

            $update = ['type' => $type?->name];

            foreach ($specifications as $key => $value) {
                if (in_array($key, self::BOOLEAN_COLUMNS, true)) {
                    $update[$key] = $value === 'Yes';
                } elseif (in_array($key, array_merge(self::NUMBER_COLUMNS, self::TEXT_COLUMNS), true)) {
                    $update[$key] = $value;
                }
            }

            DB::table('machines')->where('id', $machine->id)->update($update);
        }
    }
};

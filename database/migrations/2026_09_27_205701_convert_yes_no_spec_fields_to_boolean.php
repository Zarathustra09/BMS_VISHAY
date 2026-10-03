<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The seeded types whose Yes/No-flavoured Text field becomes a real Boolean field, and
     * which of that type's spec_fields keys it is. Matched by name, never by an icon/emoji
     * value — see the Phase 4 collation lesson in docs/03-data-model.md.
     *
     * @var array<string, string>
     */
    private const TARGETS = [
        'Generator' => 'autostart',
        'Cooling Tower' => 'makeup_valve',
    ];

    /**
     * Run the migrations.
     *
     * Generator's `autostart` and Cooling Tower's `makeup_valve` were seeded as Text fields
     * holding the string "Yes"/"No" (spec fields only supported Text/Number at the time — see
     * 2026_09_27_144010_seed_default_machine_types.php). Now that Boolean is a real field
     * kind, both become one, and every machine of that type gets its stored string value
     * converted to a real boolean in `specifications`.
     */
    public function up(): void
    {
        foreach (self::TARGETS as $typeName => $key) {
            $type = DB::table('machine_types')->where('name', $typeName)->first();

            if (! $type) {
                continue;
            }

            $fields = json_decode($type->spec_fields ?? '[]', true) ?: [];
            $changed = false;

            foreach ($fields as &$field) {
                if (($field['key'] ?? null) === $key && ($field['type'] ?? null) === 'text') {
                    $field['type'] = 'boolean';
                    $field['required'] = false;
                    $changed = true;
                }
            }
            unset($field);

            if ($changed) {
                DB::table('machine_types')->where('id', $type->id)->update([
                    'spec_fields' => json_encode($fields),
                ]);
            }

            if (! $changed) {
                continue;
            }

            DB::table('machines')->where('machine_type_id', $type->id)
                ->orderBy('id')
                ->chunkById(100, function ($machines) use ($key) {
                    foreach ($machines as $machine) {
                        $specifications = json_decode($machine->specifications ?? '{}', true) ?: [];

                        if (! array_key_exists($key, $specifications)) {
                            continue;
                        }

                        $specifications[$key] = in_array(
                            strtolower((string) $specifications[$key]), ['yes', '1', 'true'], true
                        );

                        DB::table('machines')->where('id', $machine->id)->update([
                            'specifications' => json_encode($specifications),
                        ]);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Restores the field's kind to Text and each converted machine's boolean back to a
     * "Yes"/"No" string. `required` is not restored to its pre-conversion value, since that
     * value was never recorded anywhere to restore.
     */
    public function down(): void
    {
        foreach (self::TARGETS as $typeName => $key) {
            $type = DB::table('machine_types')->where('name', $typeName)->first();

            if (! $type) {
                continue;
            }

            $fields = json_decode($type->spec_fields ?? '[]', true) ?: [];
            $changed = false;

            foreach ($fields as &$field) {
                if (($field['key'] ?? null) === $key && ($field['type'] ?? null) === 'boolean') {
                    $field['type'] = 'text';
                    $changed = true;
                }
            }
            unset($field);

            if ($changed) {
                DB::table('machine_types')->where('id', $type->id)->update([
                    'spec_fields' => json_encode($fields),
                ]);
            }

            if (! $changed) {
                continue;
            }

            DB::table('machines')->where('machine_type_id', $type->id)
                ->orderBy('id')
                ->chunkById(100, function ($machines) use ($key) {
                    foreach ($machines as $machine) {
                        $specifications = json_decode($machine->specifications ?? '{}', true) ?: [];

                        if (! array_key_exists($key, $specifications)) {
                            continue;
                        }

                        $specifications[$key] = $specifications[$key] ? 'Yes' : 'No';

                        DB::table('machines')->where('id', $machine->id)->update([
                            'specifications' => json_encode($specifications),
                        ]);
                    }
                });
        }
    }
};

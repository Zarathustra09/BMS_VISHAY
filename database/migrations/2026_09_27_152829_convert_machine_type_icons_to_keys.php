<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Machine type name => new predetermined icon key (App\Enums\MachineTypeIcon), for the
     * nine default types seeded by seed_default_machine_types. Matched by name, not by the
     * old emoji `icon` value — `utf8mb4_unicode_ci` (this table's collation) is built from an
     * older Unicode Collation Algorithm with no weights for most supplementary-plane code
     * points, which is where almost all emoji live, so it silently treats many *different*
     * emoji as equal. A `WHERE icon = '🧊'` match under this collation isn't limited to rows
     * that actually held 🧊 — it also caught rows holding 🏗️, 🔧, 🛗, 🌡️, 💧 and 🔥, and the
     * first version of this migration rewrote all of them to 'cube' before that was noticed.
     * Matching on `name` (plain ASCII) sidesteps the collation entirely.
     */
    private const NAME_TO_KEY = [
        'Chiller' => 'cube',
        'Cooling Tower' => 'office-building',
        'Generator' => 'lightning-bolt',
        'Elevator' => 'switch-vertical',
        'Pump' => 'refresh',
        'HVAC' => 'sun',
        'Compressor' => 'filter',
        'Boiler' => 'fire',
        'Other' => 'cog',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::NAME_TO_KEY as $name => $key) {
            DB::table('machine_types')->where('name', $name)->update(['icon' => $key]);
        }

        // Any other row (a type an admin already created before this migration ran, so its
        // name isn't one of the nine defaults above) gets the enum's own fallback, so every
        // row ends up holding a valid MachineTypeIcon key.
        DB::table('machine_types')
            ->whereNotIn('name', array_keys(self::NAME_TO_KEY))
            ->update(['icon' => 'cog']);
    }

    /**
     * Reverse the migrations.
     *
     * Only reverses the nine default types, and only where the icon still holds the key this
     * migration assigned — a type an admin has since given a different icon is left alone,
     * since rolling this back shouldn't discard a real edit.
     */
    public function down(): void
    {
        $keyToEmoji = [
            'cube' => '🧊', 'office-building' => '🏗️', 'lightning-bolt' => '⚡',
            'switch-vertical' => '🛗', 'refresh' => '💧', 'sun' => '🌡️',
            'filter' => '🔧', 'fire' => '🔥', 'cog' => '⚙️',
        ];

        foreach (self::NAME_TO_KEY as $name => $key) {
            DB::table('machine_types')
                ->where('name', $name)
                ->where('icon', $key)
                ->update(['icon' => $keyToEmoji[$key]]);
        }
    }
};

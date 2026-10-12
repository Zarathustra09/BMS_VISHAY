<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Installs that already saved the Settings page still hold the old default company name
     * ("NexBuild BMS"). Move those to the new product name; a name someone typed themselves is
     * left alone.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'company_name')
            ->where('value', json_encode('NexBuild BMS'))
            ->update(['value' => json_encode('LogXense Enterprise')]);

        Cache::forget('app.settings');
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'company_name')
            ->where('value', json_encode('LogXense Enterprise'))
            ->update(['value' => json_encode('NexBuild BMS')]);

        Cache::forget('app.settings');
    }
};

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds a clean install: the roles and permissions, one admin, one viewer and one demo machine.
 *
 * BuildingSeeder, FloorSeeder, MachineAndLogSeeder and UserRoleTestSeeder (bulk fake data for
 * development) are no longer part of this — run them on demand with `db:seed --class=`.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assignRole('admin');

        User::create([
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
            'password' => 'password',
        ])->assignRole('viewer');

        $this->call(DemoMachineSeeder::class);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Machine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_device_can_submit_a_log_with_a_valid_key(): void
    {
        $machine = Machine::factory()->create();
        $plainKey = $machine->generateDeviceKey();

        $response = $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'temperature' => 22.5,
        ], ['X-Device-Key' => $plainKey]);

        $response->assertStatus(201)->assertJson(['success' => true]);
        $this->assertDatabaseHas('machine_logs', [
            'machine_id' => $machine->id,
            'temperature' => 22.5,
        ]);
        $this->assertNotNull($machine->fresh()->last_seen_at);
    }

    public function test_a_missing_device_key_is_rejected(): void
    {
        $response = $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'temperature' => 22.5,
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_an_invalid_device_key_is_rejected(): void
    {
        Machine::factory()->create();

        $response = $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'temperature' => 22.5,
        ], ['X-Device-Key' => 'not-a-real-key']);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_a_devices_key_only_writes_logs_for_its_own_machine(): void
    {
        $machineA = Machine::factory()->create();
        $machineB = Machine::factory()->create();
        $plainKey = $machineA->generateDeviceKey();

        $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'temperature' => 30,
        ], ['X-Device-Key' => $plainKey])->assertStatus(201);

        $this->assertDatabaseHas('machine_logs', ['machine_id' => $machineA->id]);
        $this->assertDatabaseMissing('machine_logs', ['machine_id' => $machineB->id]);
    }
}

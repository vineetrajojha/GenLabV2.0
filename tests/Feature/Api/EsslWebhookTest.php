<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EsslWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'attendance.essl.webhook_secret' => 'test-secret',
            'attendance.essl.allowed_ips' => [],
        ]);
    }

    public function test_rejects_invalid_secret(): void
    {
        $response = $this->postJson('/api/attendance/essl/webhook', [
            'secret' => 'wrong',
            'events' => [
                [
                    'employee_code' => 'EMP000',
                    'punch_time' => now()->format('Y-m-d H:i:s'),
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_ingests_valid_payload(): void
    {
        $employee = Employee::factory()->create(['employee_code' => 'EMP123']);

        $payload = [
            'secret' => 'test-secret',
            'device_serial' => 'ESSL-001',
            'events' => [
                [
                    'employee_code' => 'EMP123',
                    'punch_time' => now()->format('Y-m-d H:i:s'),
                ],
            ],
        ];

        $response = $this->postJson('/api/attendance/essl/webhook', $payload);

        $response->assertOk()
            ->assertJsonPath('data.stored', 1)
            ->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->getKey(),
            'attendance_date' => now()->toDateString(),
            'source' => AttendanceRecord::SOURCE_ESSL,
        ]);
    }
}

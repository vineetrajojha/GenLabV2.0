<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EsslSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EsslLogIngestor
{
    public function ingest(array $events, ?string $deviceSerial = null, ?string $sourceIp = null, ?string $deviceName = null): array
    {
        $summary = [
            'total' => count($events),
            'stored' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped_manual' => 0,
            'missing_employees' => 0,
            'invalid' => 0,
            'errors' => 0,
            'missing_employee_codes' => [],
        ];

        $defaultStatus = config('attendance.essl.default_status', AttendanceRecord::STATUS_PRESENT);
        $processedEvents = [];

        foreach ($events as $index => $rawEvent) {
            $normalized = $this->normalizeEvent($rawEvent, $defaultStatus);

            if (!$normalized['employee_code'] || !$normalized['punch_at']) {
                $summary['invalid']++;
                continue;
            }

            /** @var Employee|null $employee */
            $employee = Employee::query()
                ->where('employee_code', $normalized['employee_code'])
                ->first();

            if (!$employee) {
                $summary['missing_employees']++;
                $summary['missing_employee_codes'][] = $normalized['employee_code'];
                continue;
            }

            try {
                $result = $this->storeEvent($employee, $normalized, $deviceSerial ?: $normalized['device_serial']);
            } catch (\Throwable $exception) {
                $summary['errors']++;
                Log::error('Failed to persist eSSL attendance event', [
                    'employee_code' => $normalized['employee_code'],
                    'device_serial' => $deviceSerial,
                    'exception' => $exception->getMessage(),
                ]);
                continue;
            }

            if ($result === 'skipped_manual') {
                $summary['skipped_manual']++;
                continue;
            }

            $summary['stored']++;
            $summary[$result]++;
            $processedEvents[] = [
                'employee_code' => $normalized['employee_code'],
                'punch_at' => $normalized['punch_at']->toIso8601String(),
                'device_serial' => $deviceSerial ?: $normalized['device_serial'],
                'direction' => $normalized['direction'],
                'status' => $normalized['status'],
            ];
        }

        EsslSyncLog::create([
            'device_serial' => $deviceSerial ?: Arr::get($processedEvents, '0.device_serial'),
            'device_name' => $deviceName,
            'source_ip' => $sourceIp,
            'total_events' => $summary['total'],
            'stored_records' => $summary['stored'],
            'skipped_manual' => $summary['skipped_manual'],
            'missing_employees' => $summary['missing_employees'],
            'invalid_events' => $summary['invalid'],
            'error_events' => $summary['errors'],
            'status' => $summary['errors'] > 0 ? 'partial' : 'success',
            'meta' => [
                'processed_events' => array_slice($processedEvents, -10),
                'missing_employee_codes' => array_slice(array_values(array_unique($summary['missing_employee_codes'])), 0, 20),
            ],
        ]);

        return [
            'total' => $summary['total'],
            'stored' => $summary['stored'],
            'created' => $summary['created'],
            'updated' => $summary['updated'],
            'skipped_manual' => $summary['skipped_manual'],
            'missing_employees' => $summary['missing_employees'],
            'invalid' => $summary['invalid'],
            'errors' => $summary['errors'],
        ];
    }

    protected function storeEvent(Employee $employee, array $event, ?string $deviceSerial): string
    {
        return DB::transaction(function () use ($employee, $event, $deviceSerial) {
            $attendance = AttendanceRecord::firstOrNew([
                'employee_id' => $employee->getKey(),
                'attendance_date' => $event['punch_at']->toDateString(),
            ]);

            if ($attendance->exists && $attendance->source === AttendanceRecord::SOURCE_MANUAL) {
                return 'skipped_manual';
            }

            $attendance->status = $event['status'];
            $attendance->source = AttendanceRecord::SOURCE_ESSL;

            if (!$attendance->check_in_at || $event['punch_at']->lt($attendance->check_in_at)) {
                $attendance->check_in_at = $event['punch_at'];
            }

            if (!$attendance->check_out_at || $event['punch_at']->gt($attendance->check_out_at)) {
                $attendance->check_out_at = $event['punch_at'];
            }

            $metadata = $attendance->metadata ?? [];
            $metadata['essl_events'] = $this->appendEvent($metadata['essl_events'] ?? [], [
                'punch_at' => $event['punch_at']->toIso8601String(),
                'direction' => $event['direction'],
                'device_serial' => $deviceSerial,
                'raw' => $this->pruneRawPayload($event['raw']),
            ]);
            $metadata['essl_device_serial'] = $deviceSerial;
            $attendance->metadata = $metadata;

            $attendance->save();

            return $attendance->wasRecentlyCreated ? 'created' : 'updated';
        });
    }

    protected function normalizeEvent(array $event, string $defaultStatus): array
    {
        $employeeCode = $this->extractValue($event, [
            'employee_code',
            'emp_code',
            'employee',
            'code',
            'pin',
            'user_id',
            'userId',
        ]);

        $punchAtRaw = $this->extractValue($event, [
            'punch_time',
            'timestamp',
            'log_time',
            'datetime',
            'punch_at',
            'scan_time',
        ]);
        $punchAt = $this->parseDateTime($punchAtRaw);

        $status = $this->normalizeStatus($this->extractValue($event, [
            'status',
            'attendance_status',
            'state',
            'day_type',
            'punch_state',
        ]), $defaultStatus);

        $direction = $this->extractValue($event, [
            'direction',
            'io_mode',
            'in_out',
            'punch_type',
        ]);

        $deviceSerial = $this->extractValue($event, [
            'device_serial',
            'device_sn',
            'terminal_sn',
            'serial',
        ]);

        return [
            'employee_code' => $employeeCode,
            'punch_at' => $punchAt,
            'status' => $status,
            'direction' => $direction ? strtolower((string) $direction) : null,
            'device_serial' => $deviceSerial,
            'raw' => $event,
        ];
    }

    protected function appendEvent(array $events, array $payload): array
    {
        $events[] = $payload;

        if (count($events) > 20) {
            $events = array_slice($events, -20);
        }

        return $events;
    }

    protected function pruneRawPayload(array $raw): array
    {
        $keys = [
            'employee_code',
            'emp_code',
            'code',
            'pin',
            'user_id',
            'timestamp',
            'punch_time',
            'io_mode',
            'direction',
            'device_serial',
        ];

        return array_intersect_key($raw, array_flip($keys));
    }

    protected function extractValue(array $event, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $event) && $event[$key] !== null && $event[$key] !== '') {
                return is_string($event[$key]) ? trim($event[$key]) : (string) $event[$key];
            }
        }

        return null;
    }

    protected function parseDateTime(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function normalizeStatus(?string $status, string $fallback): string
    {
        if (!$status) {
            return $fallback;
        }

        $slug = str_replace('-', '_', strtolower(trim($status)));

        if (in_array($slug, AttendanceRecord::statusValues(), true)) {
            return $slug;
        }

        return match ($slug) {
            'leave', 'onleave' => AttendanceRecord::STATUS_ON_LEAVE,
            'wfh', 'work_from_home' => AttendanceRecord::STATUS_WORK_FROM_HOME,
            'holiday' => AttendanceRecord::STATUS_HOLIDAY,
            'absent' => AttendanceRecord::STATUS_ABSENT,
            default => $fallback,
        };
    }
}

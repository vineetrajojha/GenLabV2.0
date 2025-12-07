<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\EsslLogIngestor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EsslAdmsController extends Controller
{
    public function __invoke(Request $request, EsslLogIngestor $ingestor)
    {
        $deviceSerial = $request->query('SN') ?? $request->input('SN');
        $table = strtoupper($request->query('table', $request->input('table', 'ATTLOG')));

        if ($request->isMethod('GET') && trim($request->getContent()) === '') {
            Log::debug('eSSL ADMS heartbeat', [
                'device_serial' => $deviceSerial,
                'ip' => $request->ip(),
                'query' => $request->query(),
            ]);

            return response('OK');
        }

        if ($table !== 'ATTLOG') {
            Log::info('eSSL ADMS non-attlog payload skipped', [
                'device_serial' => $deviceSerial,
                'ip' => $request->ip(),
                'table' => $table,
            ]);

            return response('OK');
        }

        $rawBody = trim($request->getContent() ?? '');

        if ($rawBody === '') {
            return response('OK');
        }

        $events = $this->parseAttlogBody($rawBody, $deviceSerial);

        if (empty($events)) {
            return response('OK');
        }

        $summary = $ingestor->ingest($events, $deviceSerial, $request->ip());

        Log::info('eSSL ADMS sync complete', [
            'device_serial' => $deviceSerial,
            'ip' => $request->ip(),
            'summary' => $summary,
        ]);

        return response('OK');
    }

    protected function parseAttlogBody(string $body, ?string $deviceSerial): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $body);
        $events = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pairs = preg_split('/\s+/', $line);
            $data = [];

            foreach ($pairs as $pair) {
                if (!str_contains($pair, '=')) {
                    continue;
                }

                [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
                $key = strtolower(trim($key));
                $value = $value !== null ? trim($value) : null;
                $data[$key] = $value;
            }

            if (!isset($data['pin']) && !isset($data['employee_code'])) {
                continue;
            }

            $events[] = [
                'employee_code' => $data['employee_code'] ?? $data['pin'] ?? null,
                'punch_time' => $data['time'] ?? $data['punch_time'] ?? null,
                'status' => $data['status'] ?? null,
                'direction' => $this->resolveDirection($data['status'] ?? null),
                'device_serial' => $deviceSerial ?? ($data['sn'] ?? null),
                'raw' => $data,
            ];
        }

        return $events;
    }

    protected function resolveDirection(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        return match ((string) $status) {
            '0' => 'in',
            '1' => 'out',
            default => null,
        };
    }
}

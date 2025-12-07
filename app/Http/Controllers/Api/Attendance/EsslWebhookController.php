<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\EsslLogIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EsslWebhookController extends Controller
{
    public function __invoke(Request $request, EsslLogIngestor $ingestor): JsonResponse
    {
        $secret = config('attendance.essl.webhook_secret');

        if (!$secret) {
            abort(503, 'eSSL webhook secret is not configured.');
        }

        $allowedIps = config('attendance.essl.allowed_ips', []);

        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps, true)) {
            abort(403, 'This IP address is not allowed to push attendance events.');
        }

        $payload = $this->validatePayload($request);

        $providedSecret = $payload['secret'] ?? $request->header('X-ESSL-SECRET');

        if (!$providedSecret || !hash_equals($secret, $providedSecret)) {
            abort(401, 'Invalid eSSL webhook secret.');
        }

        $events = $payload['events'] ?? [];

        if (empty($events)) {
            abort(422, 'No attendance events were provided.');
        }

        $summary = $ingestor->ingest(
            $events,
            $payload['device_serial'] ?? null,
            $request->ip(),
            $payload['device_name'] ?? null
        );

        return response()->json([
            'message' => 'Attendance payload processed successfully.',
            'data' => $summary,
        ]);
    }

    protected function validatePayload(Request $request): array
    {
        $events = $request->input('events') ?? $request->input('logs') ?? $request->input('data');

        if (is_array($events) && array_is_list($events) === false) {
            $events = [$events];
        }

        $request->merge(['events' => $events]);

        try {
            return $request->validate([
                'secret' => ['nullable', 'string'],
                'device_serial' => ['nullable', 'string', 'max:255'],
                'device_name' => ['nullable', 'string', 'max:255'],
                'events' => ['required', 'array', 'min:1'],
                'events.*' => ['array'],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }
}

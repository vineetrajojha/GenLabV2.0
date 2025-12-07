# eSSL Attendance Webhook

The attendance dashboard can now ingest biometric punches directly from an eSSL (or compatible) device. This document explains how to enable and verify the webhook flow.

## 1. Configure your environment

Set the following keys in `.env` (the same keys exist in `.env.example`):

```
ESSL_WEBHOOK_SECRET=choose-a-strong-random-string
ESSL_ALLOWED_IPS=203.0.113.10,203.0.113.11   # optional CSV list
ESSL_DEFAULT_STATUS=present                  # must match an AttendanceRecord status
```

The secret is mandatory. When `ESSL_ALLOWED_IPS` is provided, requests originating from other addresses will be rejected with HTTP 403.

## 2. Share the webhook URL with the device

The webhook endpoint lives under the API namespace:

```
POST {APP_URL}/api/attendance/essl/webhook
Headers:
    Content-Type: application/json
    X-ESSL-SECRET: <ESSL_WEBHOOK_SECRET>
```

The body accepts either an `events` array or legacy `logs`/`data` keys. Each event should include at least `employee_code` (matching `employees.employee_code`) and `punch_time`. A minimal payload looks like:

```json
{
  "device_serial": "ESSL-01",
  "secret": "<ESSL_WEBHOOK_SECRET>",
  "events": [
    {
      "employee_code": "EMP001",
      "punch_time": "2025-12-03 09:05:11",
      "direction": "in"
    },
    {
      "employee_code": "EMP001",
      "punch_time": "2025-12-03 18:04:08",
      "direction": "out"
    }
  ]
}
```

Any of the following keys are accepted for backwards compatibility:

| Purpose        | Supported keys                              |
|----------------|---------------------------------------------|
| Employee code  | `employee_code`, `emp_code`, `code`, `pin`, `user_id` |
| Punch time     | `punch_time`, `timestamp`, `log_time`, `datetime`, `scan_time` |
| Direction      | `direction`, `io_mode`, `in_out`, `punch_type` |
| Status override| `status`, `attendance_status`, `state`, `day_type`, `punch_state` |

## 3. What happens to each punch?

* Employees are resolved via `employee_code`.
* A daily `attendance_records` row is created/updated per employee.
* The earliest punch becomes `check_in_at`; the latest becomes `check_out_at` for that day.
* Manual entries (`source = manual`) are **never** overwritten.
* Raw payloads are stored in the record metadata for traceability.

Every webhook call is also logged in `essl_sync_logs`. The attendance dashboard now surfaces:

* Webhook URL & copy helper.
* Default status and last sync time.
* Rolling stats (received/stored/missing/invalid rows).
* A table of the five most recent sync attempts (success or partial).

## 4. Testing locally

You can hit the endpoint with `curl`:

```bash
curl -X POST "http://localhost/api/attendance/essl/webhook" \
  -H "Content-Type: application/json" \
  -H "X-ESSL-SECRET: ${ESSL_WEBHOOK_SECRET}" \
  -d '{
    "device_serial": "LAB-ESSL-1",
    "events": [
      {"employee_code": "EMP001", "punch_time": "2025-12-03T09:00:00"}
    ]
  }'
```

Expect a JSON summary similar to:

```json
{
  "message": "Attendance payload processed successfully.",
  "data": {
    "total": 1,
    "stored": 1,
    "created": 1,
    "updated": 0,
    "skipped_manual": 0,
    "missing_employees": 0,
    "invalid": 0,
    "errors": 0
  }
}
```

Monitor the dashboard card to validate that the sync landed and that the latest statistics updated as expected.

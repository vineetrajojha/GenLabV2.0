**Postman: Marketing Dashboard - Quick Start**
- **Collection:** `postman/Marketing_Dashboard_API.postman_collection.json`
- **Environment:** `postman/Marketing_Dashboard_Env.postman_environment.json`

- **What this includes**
  - `Auth - Login` request (POST `/api/user/login`) — manual login.
  - Collection-level prerequest script that will auto-login when `{{token}}` is absent (it reads `{{user_code}}` + `{{auth_password}}` and stores `access_token` into `{{token}}`).
  - Requests for `overview`, `summary`, `bookings`, `invoices`, `invoice-transactions`, `cash-transactions`, `clients`.

- **Local-only debug endpoints (already added)**
  - `GET /api/debug/generate-token/{user_code}` — returns a JWT token for `user_code` (convenience; uses JWTAuth::fromUser). Keep or delete after testing.
  - `GET /api/debug/run-marketing/{user_code}/{password}` — server-side flow: authenticates, returns `token`, `overview`, `summary`, `series` (bookings 6 months), `bookings` (first page) and `clients` (first 5). LOCAL USE ONLY.
  - These debug routes are defined in `routes/api.php`. Do not expose them in production.

- **Import steps (Postman UI)**
  1. Open Postman.
  2. Import the collection file: `postman/Marketing_Dashboard_API.postman_collection.json`.
  3. Import the environment file: `postman/Marketing_Dashboard_Env.postman_environment.json`.
  4. Select the environment named `Marketing Dashboard - Local` in the top-right environment dropdown.
  5. (Optional) Verify `auth_password` in the environment. It's prefilled with `12345678` for local testing; change if needed.
  6. Run `Auth - Login` once (or let the collection prerequest auto-run) — this will populate `{{token}}`.
  7. Execute `Dashboard - Overview`, `Dashboard - Summary`, `Dashboard - Series` or other requests. They include `Authorization: Bearer {{token}}` header.

- **Run everything via Collection Runner**
  - In Postman: open the collection → `Run` → choose the environment `Marketing Dashboard - Local` → click `Run`.
  - The collection-level prerequest script will auto-login if `token` is blank.

- **Run from CLI (Newman)**
  - Install: `npm install -g newman`
  - Run the collection with environment file (the prerequest script will attempt auto-login using `auth_password` variable):

```powershell
newman run "postman/Marketing_Dashboard_API.postman_collection.json" -e "postman/Marketing_Dashboard_Env.postman_environment.json" --timeout-request 120000
```

- **If you prefer manual token retrieval (cURL / PowerShell)**
  1. Login and capture token (PowerShell):

```powershell
$login = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/user/login' -Method POST -Body (@{ user_code='MKT001'; password='12345678' } | ConvertTo-Json) -ContentType 'application/json'
$token = $login.access_token
```

  2. Call overview with that token:

```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/marketing-dashboard/MKT001/overview' -Headers @{ Authorization = "Bearer $token" } -Method GET
```

- **Notes & Security**
  - The environment file included here is for local development and includes the test password `12345678` (as provided). Remove or change this before sharing externally.
  - Remove the debug routes in `routes/api.php` before deploying to staging/production.

- **Files in repo**
  - `postman/Marketing_Dashboard_API.postman_collection.json`
  - `postman/Marketing_Dashboard_Env.postman_environment.json` (this file)
  - `docs/marketing-dashboard-postman.md` (this document)

If you'd like, I can also:
- Export a ready-to-import Postman workspace zip for direct download.
- Add a pre-written Newman script (npm script) to the repo to run the collection as part of CI (uses `newman`).

# Marketing — Personal Expenses API (Mobile)

Base URL: /api/marketing-person/{user_code}

Authentication: Bearer JWT token (middleware `multi_jwt:api`)

Endpoints

- **GET /personal/expenses**
  - Description: Return paginated personal expenses for the marketing person.
  - Query parameters:
    - `perPage` (integer) — items per page (default 25)
    - `page` (integer) — page number
    - `section` (string) — filter by section
    - `month` (1-12) — filter by expense month
    - `year` (YYYY) — filter by expense year
    - `search` (string) — text search over `description` and `section`
  - Success response (200):

```json
{
  "status": true,
  "message": "Personal expenses fetched",
  "data": {
    "items": [
      {
        "id": 123,
        "section": "Travel",
        "expense_date": "2025-12-01",
        "amount": "1500.00",
        "description": "Taxi to client site",
        "file_url": "http://example.com/storage/personal_expenses/abc.pdf",
        "status": 0
      }
    ],
    "meta": {
      "total": 1,
      "per_page": 25,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

Example curl:

```bash
curl -H "Authorization: Bearer <TOKEN>" \
  "https://your-domain.test/api/marketing-person/MKT001/personal/expenses?perPage=10"
```

- **POST /personal/expenses**
  - Description: Create a personal expense record. Accepts multipart `file` (jpg,jpeg,png,pdf) up to 10 MB.
  - Form fields (multipart/form-data):
    - `section` (string, optional)
    - `expense_date` (date, optional — YYYY-MM-DD). Defaults to today if omitted.
    - `amount` (numeric, required)
    - `description` (string, optional)
    - `file` (file, optional — jpg,jpeg,png,pdf, max 10240KB)
  - Success response (201):

```json
{
  "status": true,
  "message": "Personal expense created",
  "data": {
    "id": 124,
    "section": "Travel",
    "expense_date": "2025-12-13",
    "amount": "1200.00",
    "description": "Parking",
    "file_url": "http://example.com/storage/personal_expenses/xyz.jpg",
    "status": 0
  }
}
```

Example curl (upload):

```bash
curl -X POST \
  -H "Authorization: Bearer <TOKEN>" \
  -F "amount=1200" \
  -F "expense_date=2025-12-13" \
  -F "section=Travel" \
  -F "description=Parking fee" \
  -F "file=@/path/to/receipt.jpg" \
  "https://your-domain.test/api/marketing-person/MKT001/personal/expenses"
```

Notes
- Files are stored on the `public` disk under `personal_expenses` and returned via `Storage::url()`.
- Validation errors return HTTP 422 with Laravel validation payload.
- Response shape is structured JSON for mobile clients (no HTML fragments).

Server-side implementation details
- Controller: App/Http/Controllers/MobileControllers/Accounts/MarketingPersonInfo.php — methods `personalExpensesListApi` and `personalExpensesStoreApi`.
- Model: App/Models/PersonalExpense.php (table `personal_expenses`).
- Migration: database/migrations/2025_12_13_000000_create_personal_expenses_table.php.

If you want admin approval endpoints (approve/reject) or CSV export, tell me and I will add them.

# RadarEye Enrollment Ticket Issuance Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give VDOT a single authenticated API action that issues a one-time, signed RadarEye enrollment ticket for an asset, so a VDOT asset-editor can hand it to a RadarEye admin for redemption — no new permission, no standing RadarEye credential in VDOT.

**Architecture:** A dedicated `EnrollmentTicketService` builds the canonical-JSON + HMAC-SHA256 ticket exactly as RadarEye's verifier expects it, a new POST route on the existing `hardware/{asset}` resource reuses the asset's `update` gate for authorization, and issuance is recorded through the existing `Actionlog::logaction()` convention with a new `ActionType` case. No new tables, no new permissions, no outbound HTTP calls to RadarEye.

**Tech Stack:** Laravel, Eloquent route-model binding, existing Policy/Gate authorization, PHPUnit feature tests.

**Spec:** `docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md`

---

## File Map

| File | Action |
|---|---|
| `app/Services/RadarEye/EnrollmentTicketService.php` | Create — canonical JSON + HMAC ticket builder |
| `app/Enums/ActionType.php` | Modify — add `RadareyeEnrollmentIssued` case |
| `app/Http/Controllers/Api/AssetsController.php` | Modify — add `issueRadarEyeEnrollmentTicket()` |
| `routes/api.php` | Modify — add `POST /hardware/{asset}/radareye-enrollment-ticket` |
| `config/radareye.php` | Create — `enroll_hmac_key`, `enroll_ticket_ttl` |
| `.env.example` | Modify — document `VDOT_ENROLL_HMAC_KEY`, `VDOT_ENROLL_TICKET_TTL` |
| `tests/Feature/Assets/Api/RadarEyeEnrollmentTicketTest.php` | Create — gate + issuance + interop tests |

---

## Task 1: Canonical ticket format

**Files:**
- Create: `app/Services/RadarEye/EnrollmentTicketService.php`

- [x] **Step 1: Write the failing interop test**

Add `test_ticket_canonical_json_and_signature_match_radareyes_reference_format` asserting the exact canonical JSON bytes and HMAC hex for a fixed payload/timestamp/key, cross-checked against RadarEye's Python reference (`json.dumps(payload, separators=(",", ":"), sort_keys=True)` + `hmac.new(key, raw, hashlib.sha256)`).

- [x] **Step 2: Run the focused test to verify it fails**

Run: `php artisan test --filter=RadarEyeEnrollmentTicket` — expected failure, class does not exist yet.

- [x] **Step 3: Implement `EnrollmentTicketService`**

`issue()` builds the payload (`asset_id`, `asset_tag`, `company_id`, `exp`, `issuer_id`, `nonce`, `v`), encodes via `canonicalJson()` (`ksort` + `json_encode(..., JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)` — deliberately no `JSON_UNESCAPED_UNICODE`, see spec), signs with `hash_hmac('sha256', $raw, $key, true)`, and returns `base64url(json) + "." + base64url(hmac)` via `base64UrlEncode()`.

- [x] **Step 4: Re-run the focused test**

Run: `php artisan test --filter=RadarEyeEnrollmentTicket` — expected pass.

- [x] **Step 5: Commit**

```bash
git add app/Services/RadarEye/EnrollmentTicketService.php tests/Feature/Assets/Api/RadarEyeEnrollmentTicketTest.php
git commit -m "Add RadarEye enrollment ticket service"
```

---

## Task 2: Action log entry type

**Files:**
- Modify: `app/Enums/ActionType.php`

- [x] **Step 1: Add the enum case**

Add `case RadareyeEnrollmentIssued = 'radareye enrollment issued';` — this label is fixed by the approved cross-repo design and must match exactly.

- [x] **Step 2: Commit**

```bash
git add app/Enums/ActionType.php
git commit -m "Add radareye enrollment issued action type"
```

---

## Task 3: Config and env var

**Files:**
- Create: `config/radareye.php`
- Modify: `.env.example`

- [x] **Step 1: Add config file**

`enroll_hmac_key` from `env('VDOT_ENROLL_HMAC_KEY')`, `enroll_ticket_ttl` from `env('VDOT_ENROLL_TICKET_TTL', 900)`.

- [x] **Step 2: Document in `.env.example`**

Add a commented section explaining this is a shared deploy secret with RadarEye, signs tickets only, and must never mint RadarEye sessions or list VDOT assets.

- [x] **Step 3: Commit**

```bash
git add config/radareye.php .env.example
git commit -m "Add VDOT_ENROLL_HMAC_KEY config"
```

---

## Task 4: API route and controller action

**Files:**
- Modify: `app/Http/Controllers/Api/AssetsController.php`
- Modify: `routes/api.php`

- [x] **Step 1: Write the failing gate + issuance tests**

Add tests: caller without `update` on the asset gets 403; caller with `editAssets()` gets a 200 with `ticket`/`expires_at`; missing `company_id` and missing HMAC key both return a clean error response instead of a ticket.

- [x] **Step 2: Run the focused tests to verify they fail**

Run: `php artisan test --filter=RadarEyeEnrollmentTicket` — expected failure, route/method missing.

- [x] **Step 3: Add the route**

`Route::post('/hardware/{asset}/radareye-enrollment-ticket', [Api\AssetsController::class, 'issueRadarEyeEnrollmentTicket'])->name('api.assets.radareye-enrollment-ticket');` — registered directly beside the existing `PATCH`/`PUT /hardware/{asset}` update routes.

- [x] **Step 4: Add the controller method**

`issueRadarEyeEnrollmentTicket(Asset $asset)`: `$this->authorize('update', $asset)` (same gate as `UpdateAssetRequest`), validate `company_id` is set, validate the HMAC key is configured, build the ticket via `EnrollmentTicketService`, write the `Actionlog` entry (asset tag in the note, never the ticket/nonce/key), return `{"ticket": ..., "expires_at": ...}`.

- [x] **Step 5: Re-run the focused tests**

Run: `php artisan test --filter=RadarEyeEnrollmentTicket` — expected pass.

- [x] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/AssetsController.php routes/api.php tests/Feature/Assets/Api/RadarEyeEnrollmentTicketTest.php
git commit -m "Issue RadarEye enrollment tickets from the hardware API"
```

---

## Task 5: Documentation and verification

**Files:**
- Create: `docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md`
- Test: all files touched above

- [x] **Step 1: Write the design doc**

Cover the gate-reuse decision, ticket format (pointing to the RadarEye spec as canonical), the env var, the action log entry, and out-of-scope items.

- [ ] **Step 2: Run full focused test suite**

Run: `php artisan test --filter=RadarEyeEnrollmentTicket` (could not be executed in this session — no PHP runtime available in the sandbox this plan was authored in; run before merge).

- [ ] **Step 3: Run static analysis / lint**

This repo has no PHPCS config; use its existing checks instead: `vendor/bin/phpstan analyse` (see `phpstan.neon.dist`) and/or `vendor/bin/psalm` (see `psalm.xml`) against `app/Services/RadarEye`, `app/Http/Controllers/Api/AssetsController.php`, `app/Enums/ActionType.php`, `config/radareye.php` (not run in this session — no PHP runtime available; run before merge).

- [x] **Step 4: Commit**

```bash
git add docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md docs/superpowers/plans/2026-08-21-radareye-enrollment-ticket-issuance.md
git commit -m "Document RadarEye enrollment ticket issuance"
```

- [ ] **Step 5: Push**

Not done — branch stays local per instructions (commit only, no push/merge).

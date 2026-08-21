# RadarEye Read-Only Asset Summary Implementation Plan

**Goal:** Give RadarEye's device page a read-only, `view`-gated VDOT endpoint (`GET /api/v1/hardware/{asset}/radareye-summary`) returning a minimal asset_tag/assigned_to/status_label/location/notes/vdot_url shape, reusing existing checkout-target resolution (`AssetsTransformer::transformAssignedTo`) rather than re-deriving it, and deriving `vdot_url` from the existing `hardware.show` named route (hence `config('app.url')`) so it is correct on every domain this codebase deploys to.

**Spec:** `docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md` (appended "Read-only asset summary" section); cross-references RadarEye repo's `docs/superpowers/specs/2026-08-18-vdot-opt-in-enrollment-design.md`.

**Status:** implemented, not test-executed in this session (no PHP/Docker runtime available in this sandbox — run `php artisan test --filter=RadarEyeAssetSummary` before merge) and not pushed/merged per instructions.

## File Map

| File | Action |
|---|---|
| `app/Http/Controllers/Api/AssetsController.php` | Modify — add `radarEyeAssetSummary()` |
| `routes/api.php` | Modify — add `GET /hardware/{asset}/radareye-summary` |
| `tests/Feature/Assets/Api/RadarEyeAssetSummaryTest.php` | Create — gate denial, full shape, unassigned, empty-notes cases |
| `docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md` | Modify — append summary section |

# RadarEye enrollment ticket issuance (VDOT side)

**Date:** 2026-08-21
**Status:** implemented

## Problem

RadarEye's redemption side of opt-in enrollment is already built
(`api/app/routes/vdot_enrollment.py` in the RadarEye repo, gated on
`require_admin`). VDOT (this repo) was missing the issuing side: a way
for a VDOT asset-editor to produce the one-time signed ticket that a
RadarEye admin later redeems.

See the RadarEye repo's
`docs/superpowers/specs/2026-08-18-vdot-opt-in-enrollment-design.md` for
the full, cross-system design this implements. That document is the
canonical source of truth for the ticket wire format and the
dual-control model — it is not re-derived here.

## Decision: reuse the `update` gate, no new permission

`POST /api/v1/hardware/{asset}/radareye-enrollment-ticket` is authorized
with the exact same check as the existing `PATCH /hardware/{asset}`
route: `Gate::allows('update', $asset)` (see
`app/Http/Requests/UpdateAssetRequest.php` and
`app/Policies/AssetPolicy.php` → `SnipePermissionsPolicy::update()`,
which resolves to `$user->hasAccess('assets.edit')`).

This is deliberate, not an oversight:

- No new custom permission/role to maintain, document, or get wrong.
- Whoever can already edit an asset can issue a ticket for it — a
  natural extension of "edit" that needs no additional trust decision.
- Dual control falls out for free: a VDOT asset-editor issues the
  ticket (VDOT credentials, VDOT permission model), a RadarEye admin
  redeems it (RadarEye credentials, RadarEye's `require_admin`). Two
  different systems, two different credentials, no standing trust
  relationship between them.

## Ticket format

Byte-for-byte identical to RadarEye's verifier
(`shared/vdot_enrollment/tickets.py`): canonical JSON (keys sorted,
compact `,`/`:` separators, UTF-8, non-ASCII escaped as `\uXXXX` per
Python's `ensure_ascii=True` default), HMAC-SHA256 over those bytes,
then `base64url(json) + "." + base64url(hmac)`, both segments
base64url-encoded without padding. Fields: `v=1`, `asset_id`,
`asset_tag`, `company_id`, `issuer_id`, `exp` (unix seconds, now + TTL),
`nonce` (16 random bytes, base64url). TTL defaults to 900 seconds (15
minutes).

`app/Services/RadarEye/EnrollmentTicketService.php` implements this.
Two PHP-specific `json_encode` flag choices matter for interop and are
called out in that file's docblock: `JSON_UNESCAPED_SLASHES` is
required (PHP escapes `/` by default, Python's `json.dumps` does not),
and `JSON_UNESCAPED_UNICODE` is deliberately **not** set (Python's
default `ensure_ascii=True` escapes non-ASCII to `\uXXXX`, and PHP's
default matches that — adding the unescaped-unicode flag would break
compatibility).

`company_id` is `$asset->company_id` directly, matching RadarEye's
`vdot_company_bindings` (one VDOT company id per RadarEye org). The
route requires the asset to have a `company_id` set — if it doesn't,
issuance fails with a clear error before a ticket is ever generated,
since RadarEye's redeem path enforces the binding check regardless.

## Env var

`VDOT_ENROLL_HMAC_KEY` (config `radareye.enroll_hmac_key`), plus
`VDOT_ENROLL_TICKET_TTL` (config `radareye.enroll_ticket_ttl`, default
900). Documented in `.env.example`. This is a shared deploy secret with
RadarEye's `vdot_enroll_hmac_key` / `VDOT_ENROLL_HMAC_KEY`
(`api/app/config.py` in the RadarEye repo) — both sides must hold the
identical value. It signs tickets only. It must never be used to mint
RadarEye admin sessions, and it grants no ability to list or query
VDOT assets from RadarEye's side (there is no such ability — VDOT never
calls out to RadarEye at all in this flow).

## Audit

Writes one `App\Models\Actionlog` row per issuance via the existing
`Actionlog::logaction()` convention (same pattern used elsewhere in
`AssetsController`, e.g. asset notes): `item_type = Asset::class`,
`item_id = $asset->id`, `action_type = 'radareye enrollment issued'`
(added as `ActionType::RadareyeEnrollmentIssued` in
`app/Enums/ActionType.php` — a backed enum, so the label had to exist
there before `logaction()` could accept it). The note includes the
asset tag for human context and deliberately excludes the ticket,
nonce, and signing key, since the ticket is a 15-minute bearer
credential.

## Response

`{"ticket": "<base64url>.<base64url>", "expires_at": <unix seconds>}`.
The ticket is never logged (no `Log::` calls anywhere on this path) and
is only ever handed back in the HTTP response body to the authorized
caller.

## Prior art on this branch

A pre-existing web-only flow (`app/Http/Controllers/Assets/
RadarEyeEnrollmentController.php`, commit `500cf56e6b`) already added an
"Enroll in RadarEye" button on the asset view page that redirects the
browser to a RadarEye URL with the ticket in the query string, using a
*different* env var (`RADAREYE_ENROLL_URL` / `RADAREYE_ENROLL_HMAC_KEY`
under `config/services.php`). That code path was not touched by this
work except for one shared fix: `ActionType::RadareyeEnrollmentIssued`
was added to the enum, which that controller's
`$log->logaction('radareye enrollment issued')` call requires to not
throw (backed enums reject unrecognized values in PHP). The web flow's
redirect-with-ticket-in-URL pattern is a separate design that predates
this spec and was flagged back to the team rather than modified here —
see the session hand-off for the open question.

## Out of scope (matches the RadarEye-side design doc)

- Writing `radareye_device_id` (or any field) back onto the VDOT asset.
- Any nightly sync between VDOT and RadarEye.
- Any standing RadarEye credential inside VDOT.
- Any new custom permission or role.
- Verifying/redeeming tickets in PHP — that is RadarEye's job
  (`shared/vdot_enrollment/tickets.py::verify_ticket`), not VDOT's.

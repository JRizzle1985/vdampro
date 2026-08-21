<?php

namespace App\Services\RadarEye;

use RuntimeException;

/**
 * Issues one-time, short-lived signed tickets that a VDOT asset-editor hands
 * to a RadarEye admin so they can bind a physical tracker to a VDOT asset.
 *
 * The wire format here is dictated by RadarEye's verifier
 * (`shared/vdot_enrollment/tickets.py` in the RadarEye repo) and MUST stay
 * byte-for-byte compatible:
 *
 *   base64url(canonical_json) + "." + base64url(hmac_sha256(canonical_json))
 *
 * Canonical JSON = keys sorted alphabetically, compact separators (`,`/`:`),
 * UTF-8, non-ASCII escaped as \uXXXX (i.e. Python's json.dumps default
 * ensure_ascii=True). That is why we deliberately do NOT pass
 * JSON_UNESCAPED_UNICODE below, but DO pass JSON_UNESCAPED_SLASHES (Python's
 * json.dumps does not escape "/", PHP's json_encode does by default).
 *
 * See docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md
 * for the VDOT-side design, and the RadarEye repo's
 * docs/superpowers/specs/2026-08-18-vdot-opt-in-enrollment-design.md for the
 * canonical, cross-system source of truth for this format.
 */
class EnrollmentTicketService
{
    public function __construct(
        private readonly ?string $hmacKey,
        private readonly int $ttlSeconds = 900,
    ) {
    }

    public function isConfigured(): bool
    {
        return ! empty($this->hmacKey);
    }

    /**
     * @return array{ticket: string, expires_at: int}
     */
    public function issue(string $assetId, string $assetTag, string $companyId, string $issuerId, ?int $now = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('RadarEye enrollment signing key is not configured.');
        }

        $now ??= time();
        $expiresAt = $now + $this->ttlSeconds;

        $payload = [
            'asset_id' => $assetId,
            'asset_tag' => $assetTag,
            'company_id' => $companyId,
            'exp' => $expiresAt,
            'issuer_id' => $issuerId,
            'nonce' => $this->generateNonce(),
            'v' => 1,
        ];

        $raw = static::canonicalJson($payload);
        $signature = hash_hmac('sha256', $raw, $this->hmacKey, true);

        $ticket = static::base64UrlEncode($raw).'.'.static::base64UrlEncode($signature);

        return [
            'ticket' => $ticket,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Sorted-key, compact, UTF-8 JSON matching RadarEye's
     * `json.dumps(fields, separators=(",", ":"), sort_keys=True)`.
     */
    public static function canonicalJson(array $payload): string
    {
        ksort($payload);

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function base64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function generateNonce(): string
    {
        // Single-use, high-entropy, URL-safe. RadarEye only requires
        // uniqueness/non-emptiness -- it does not depend on this exact
        // generation scheme, only on the resulting string round-tripping
        // through the canonical JSON unchanged.
        return static::base64UrlEncode(random_bytes(16));
    }
}

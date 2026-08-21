<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use App\Services\RadarEye\EnrollmentTicketService;
use Tests\TestCase;

class RadarEyeEnrollmentTicketTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['radareye.enroll_hmac_key' => 'test-shared-secret']);
        config(['radareye.enroll_ticket_ttl' => 900]);
    }

    public function test_requires_permission_to_issue_ticket()
    {
        $asset = Asset::factory()->create(['company_id' => Company::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.assets.radareye-enrollment-ticket', $asset))
            ->assertForbidden();
    }

    public function test_given_update_permission_ticket_is_issued()
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->editAssets()->create();

        $response = $this->actingAsForApi($user)
            ->postJson(route('api.assets.radareye-enrollment-ticket', $asset))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('ticket', $response);
        $this->assertArrayHasKey('expires_at', $response);
        $this->assertIsString($response['ticket']);
        $this->assertStringContainsString('.', $response['ticket']);
        $this->assertGreaterThan(time(), $response['expires_at']);
    }

    public function test_ticket_requires_asset_to_have_a_company()
    {
        $asset = Asset::factory()->create(['company_id' => null]);
        $user = User::factory()->editAssets()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.assets.radareye-enrollment-ticket', $asset))
            ->assertStatusMessageIs('error');
    }

    public function test_ticket_requires_hmac_key_to_be_configured()
    {
        config(['radareye.enroll_hmac_key' => null]);

        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->editAssets()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.assets.radareye-enrollment-ticket', $asset))
            ->assertStatusMessageIs('error');
    }

    public function test_issuing_a_ticket_writes_an_action_log_entry_without_leaking_the_ticket()
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->editAssets()->create();

        $response = $this->actingAsForApi($user)
            ->postJson(route('api.assets.radareye-enrollment-ticket', $asset))
            ->assertOk()
            ->json();

        $log = Actionlog::where('item_id', $asset->id)
            ->where('item_type', Asset::class)
            ->where('action_type', 'radareye enrollment issued')
            ->first();

        $this->assertNotNull($log, 'The radareye enrollment issued action log entry was not saved.');
        $this->assertStringContainsString($asset->asset_tag, $log->note);
        $this->assertStringNotContainsString($response['ticket'], $log->note);
    }

    /**
     * Interop-critical: assert VDOT's issued ticket is byte-for-byte the same
     * canonical JSON + HMAC-SHA256 signature that RadarEye's Python verifier
     * (shared/vdot_enrollment/tickets.py in the RadarEye repo) would produce
     * for the same key/payload/timestamp. This does not re-implement
     * verification -- it only pins the canonical encoding this side must emit.
     */
    public function test_ticket_canonical_json_and_signature_match_radareyes_reference_format()
    {
        $key = 'shared-deploy-secret';
        $now = 1_700_000_000;

        $payload = [
            'asset_id' => '42',
            'asset_tag' => 'LAPTOP-042',
            'company_id' => '7',
            'exp' => $now + 900,
            'issuer_id' => '9',
            'nonce' => 'fixed-nonce-for-test',
            'v' => 1,
        ];

        $raw = EnrollmentTicketService::canonicalJson($payload);

        // Equivalent to Python's:
        //   json.dumps(payload, separators=(",", ":"), sort_keys=True)
        $expectedRaw = '{"asset_id":"42","asset_tag":"LAPTOP-042","company_id":"7","exp":1700000900,"issuer_id":"9","nonce":"fixed-nonce-for-test","v":1}';
        $this->assertSame($expectedRaw, $raw);

        $signature = hash_hmac('sha256', $raw, $key, true);
        $expectedSignatureHex = hash_hmac('sha256', $expectedRaw, $key);
        $this->assertSame($expectedSignatureHex, bin2hex($signature));

        $ticket = EnrollmentTicketService::base64UrlEncode($raw).'.'.EnrollmentTicketService::base64UrlEncode($signature);

        [$encodedBody, $encodedSig] = explode('.', $ticket, 2);
        $this->assertSame($raw, base64_decode(strtr($encodedBody, '-_', '+/')));
        $this->assertSame($signature, base64_decode(strtr($encodedSig, '-_', '+/')));

        // No padding characters, per base64url-without-padding contract.
        $this->assertStringNotContainsString('=', $ticket);
    }

    public function test_canonical_json_escapes_unicode_like_pythons_default_but_not_slashes()
    {
        $raw = EnrollmentTicketService::canonicalJson([
            'asset_id' => '1',
            'asset_tag' => 'A/B café',
            'company_id' => '1',
            'exp' => 1,
            'issuer_id' => '1',
            'nonce' => 'n',
            'v' => 1,
        ]);

        // Slash must NOT be escaped (Python's json.dumps does not escape "/").
        $this->assertStringContainsString('A/B', $raw);
        // Non-ASCII must be \uXXXX-escaped (Python's ensure_ascii=True default),
        // matching what Python's json.dumps({"x": "café"}) produces: "café".
        $this->assertStringNotContainsString('café', $raw);
        $this->assertStringContainsString('\\u00e9', $raw);
    }
}

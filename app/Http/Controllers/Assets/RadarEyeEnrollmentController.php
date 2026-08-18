<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RadarEyeEnrollmentController extends Controller
{
    public function store(Asset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $enrollUrl = config('services.radareye.enroll_url');
        $hmacKey = config('services.radareye.hmac_key');
        $ttl = (int) config('services.radareye.ticket_ttl', 900);

        if (! $enrollUrl || ! $hmacKey) {
            return back()->with('error', 'RadarEye enrollment is not configured on this VDOT instance.');
        }

        if (! $asset->company_id) {
            return back()->with('error', 'Assign this asset to a VDOT company before issuing a RadarEye ticket.');
        }

        $payload = [
            'asset_id' => (string) $asset->id,
            'asset_tag' => (string) $asset->asset_tag,
            'company_id' => (string) $asset->company_id,
            'exp' => time() + max($ttl, 60),
            'issuer_id' => (string) Auth::id(),
            'nonce' => Str::random(24),
            'v' => 1,
        ];
        ksort($payload);
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ticket = $this->b64url($raw).'.'.$this->b64url(hash_hmac('sha256', $raw, $hmacKey, true));

        $log = new Actionlog;
        $log->item_id = $asset->id;
        $log->item_type = Asset::class;
        $log->created_by = Auth::id();
        $log->note = 'RadarEye enrollment ticket issued';
        $log->logaction('radareye enrollment issued');

        $separator = str_contains($enrollUrl, '?') ? '&' : '?';

        return redirect()->away($enrollUrl.$separator.'ticket='.rawurlencode($ticket));
    }

    private function b64url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

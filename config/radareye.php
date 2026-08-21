<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RadarEye enrollment ticket signing key
    |--------------------------------------------------------------------------
    |
    | This is a shared deploy secret with RadarEye (the tracker register).
    | It signs one-time, short-lived VDOT->RadarEye enrollment tickets only.
    | It must NEVER be used to mint RadarEye admin sessions, and it grants
    | no ability to list or query VDOT assets from RadarEye's side.
    |
    | Mirrors RadarEye's own `vdot_enroll_hmac_key` / VDOT_ENROLL_HMAC_KEY
    | setting (api/app/config.py in the RadarEye repo) -- both sides must
    | hold the identical secret value for ticket verification to succeed.
    |
    | See docs/superpowers/specs/2026-08-21-radareye-enrollment-ticket-issuance-design.md
    | for VDOT's issuing-side design, and the RadarEye repo's
    | docs/superpowers/specs/2026-08-18-vdot-opt-in-enrollment-design.md
    | for the canonical ticket format/verification contract.
    |
    */
    'enroll_hmac_key' => env('VDOT_ENROLL_HMAC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | RadarEye enrollment ticket TTL
    |--------------------------------------------------------------------------
    |
    | Seconds a signed enrollment ticket remains valid for redemption on
    | the RadarEye side. Default matches the approved design: 15 minutes.
    |
    */
    'enroll_ticket_ttl' => (int) env('VDOT_ENROLL_TICKET_TTL', 900),

];

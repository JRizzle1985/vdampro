<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$result = DB::transaction(function (): array {
    $now = now();

    $companyId = DB::table('companies')
        ->where('name', 'Klipfontein Mixed Farm')
        ->whereNull('deleted_at')
        ->value('id');

    if (! $companyId) {
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Klipfontein Mixed Farm',
            'phone' => '+27 31 555 0142',
            'email' => 'office@klipfontein.example',
            'created_by' => 1,
            'notes' => 'Fictional company used by the VDOT and RadarEye farm operations demo.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $microdotField = DB::table('custom_fields')
        ->where('name', 'Microdot pin')
        ->first();

    if (! $microdotField) {
        throw new RuntimeException('The existing Microdot pin custom field was not found.');
    }

    $fieldsetId = DB::table('custom_fieldsets')
        ->where('name', 'Klipfontein Farm Asset Data')
        ->value('id');

    if (! $fieldsetId) {
        $fieldsetId = DB::table('custom_fieldsets')->insertGetId([
            'name' => 'Klipfontein Farm Asset Data',
            'created_by' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    DB::table('custom_field_custom_fieldset')->updateOrInsert(
        [
            'custom_field_id' => $microdotField->id,
            'custom_fieldset_id' => $fieldsetId,
        ],
        ['order' => 1, 'required' => 1]
    );

    $assets = DB::table('assets')
        ->where('asset_tag', 'like', 'KMF-%')
        ->whereNull('deleted_at')
        ->get(['id', 'asset_tag', 'model_id']);

    $modelIds = $assets->pluck('model_id')->unique()->values();
    DB::table('models')->whereIn('id', $modelIds)->update([
        'fieldset_id' => $fieldsetId,
        'updated_at' => $now,
    ]);

    foreach ($assets as $asset) {
        DB::table('assets')->where('id', $asset->id)->update([
            'company_id' => $companyId,
            $microdotField->db_column => sprintf('KMF-2026-%06d', $asset->id),
            'updated_at' => $now,
        ]);
    }

    $locationIds = DB::table('locations')
        ->whereIn('name', ['Admin Office', 'Main Farm Yard', 'Workshop', 'North Camps'])
        ->pluck('id', 'name');

    $staff = [
        [
            'username' => 'kmf.pieter.jacobs',
            'first_name' => 'Pieter',
            'last_name' => 'Jacobs',
            'email' => 'pieter.jacobs@klipfontein.example',
            'jobtitle' => 'Farm Manager',
            'employee_num' => 'KMF-EMP-001',
            'location' => 'Admin Office',
        ],
        [
            'username' => 'kmf.thandi.mkhize',
            'first_name' => 'Thandi',
            'last_name' => 'Mkhize',
            'email' => 'thandi.mkhize@klipfontein.example',
            'jobtitle' => 'Livestock Supervisor',
            'employee_num' => 'KMF-EMP-002',
            'location' => 'North Camps',
        ],
        [
            'username' => 'kmf.sipho.dlamini',
            'first_name' => 'Sipho',
            'last_name' => 'Dlamini',
            'email' => 'sipho.dlamini@klipfontein.example',
            'jobtitle' => 'Workshop Technician',
            'employee_num' => 'KMF-EMP-003',
            'location' => 'Workshop',
        ],
        [
            'username' => 'kmf.nomsa.khumalo',
            'first_name' => 'Nomsa',
            'last_name' => 'Khumalo',
            'email' => 'nomsa.khumalo@klipfontein.example',
            'jobtitle' => 'Farm Administrator',
            'employee_num' => 'KMF-EMP-004',
            'location' => 'Admin Office',
        ],
        [
            'username' => 'kmf.kabelo.mokoena',
            'first_name' => 'Kabelo',
            'last_name' => 'Mokoena',
            'email' => 'kabelo.mokoena@klipfontein.example',
            'jobtitle' => 'Field Operator',
            'employee_num' => 'KMF-EMP-005',
            'location' => 'Main Farm Yard',
        ],
    ];

    $staffIds = [];
    foreach ($staff as $profile) {
        $existing = DB::table('users')
            ->where('username', $profile['username'])
            ->whereNull('deleted_at')
            ->value('id');

        $values = [
            'first_name' => $profile['first_name'],
            'last_name' => $profile['last_name'],
            'display_name' => $profile['first_name'].' '.$profile['last_name'],
            'email' => $profile['email'],
            'jobtitle' => $profile['jobtitle'],
            'employee_num' => $profile['employee_num'],
            'company_id' => $companyId,
            'location_id' => $locationIds[$profile['location']] ?? null,
            'activated' => 1,
            'notes' => 'Fictional staff profile for the Klipfontein farm operations demo. No real mailbox or login credential.',
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('users')->where('id', $existing)->update($values);
            $staffIds[$profile['username']] = $existing;
            continue;
        }

        $staffIds[$profile['username']] = DB::table('users')->insertGetId($values + [
            'username' => $profile['username'],
            'password' => Hash::make(Str::random(64)),
            'permissions' => '{}',
            'created_by' => 1,
            'created_at' => $now,
        ]);
    }

    $managerId = $staffIds['kmf.pieter.jacobs'];
    DB::table('users')
        ->whereIn('id', array_values($staffIds))
        ->where('id', '<>', $managerId)
        ->update(['manager_id' => $managerId, 'updated_at' => $now]);

    return [
        'company_id' => $companyId,
        'fieldset_id' => $fieldsetId,
        'assets_updated' => $assets->count(),
        'models_updated' => $modelIds->count(),
        'staff_profiles' => count($staffIds),
    ];
});

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

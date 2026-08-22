<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

class RadarEyeAssetSummaryTest extends TestCase
{
    public function test_requires_view_permission_to_view_summary()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assets.radareye-summary', $asset))
            ->assertForbidden();
    }

    public function test_given_view_permission_full_summary_shape_is_returned_for_a_user_checkout()
    {
        $status = Statuslabel::factory()->create(['name' => 'Deployed']);
        $location = Location::factory()->create(['name' => 'Warehouse A']);

        $asset = Asset::factory()->create([
            'status_id' => $status->id,
            'location_id' => $location->id,
            'notes' => 'some notes',
        ]);

        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe']);
        $asset->checkOut($assignee, $admin);

        $viewer = User::factory()->viewAssets()->create();

        $response = $this->actingAsForApi($viewer)
            ->getJson(route('api.assets.radareye-summary', $asset))
            ->assertOk()
            ->json();

        $this->assertSame((string) $asset->asset_tag, $response['asset_tag']);
        $this->assertSame('user', $response['assigned_to']['type']);
        $this->assertSame($assignee->display_name, $response['assigned_to']['name']);
        $this->assertSame('Deployed', $response['status_label']);
        $this->assertSame('Warehouse A', $response['location']);
        $this->assertSame('some notes', $response['notes']);
        $this->assertStringContainsString('/hardware/'.$asset->id, $response['vdot_url']);
    }

    public function test_unassigned_asset_returns_null_assigned_to()
    {
        $asset = Asset::factory()->create();
        $viewer = User::factory()->viewAssets()->create();

        $response = $this->actingAsForApi($viewer)
            ->getJson(route('api.assets.radareye-summary', $asset))
            ->assertOk()
            ->json();

        $this->assertNull($response['assigned_to']);
    }

    public function test_empty_notes_come_back_as_null_not_empty_string()
    {
        $asset = Asset::factory()->create(['notes' => '']);
        $viewer = User::factory()->viewAssets()->create();

        $response = $this->actingAsForApi($viewer)
            ->getJson(route('api.assets.radareye-summary', $asset))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('notes', $response);
        $this->assertNull($response['notes']);
    }
}

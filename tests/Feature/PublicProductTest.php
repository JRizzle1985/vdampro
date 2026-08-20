<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicProductTest extends TestCase
{
    public function test_unpublished_asset_returns_neutral_not_found_page(): void
    {
        $asset = Asset::factory()->create([
            'asset_tag' => 'PRIVATE-UNIT',
            'public_product_enabled' => false,
        ]);

        $this->get($this->publicUrl($asset->asset_tag))
            ->assertNotFound()
            ->assertSee('Product information unavailable')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_only_opted_in_unencrypted_fields_are_disclosed(): void
    {
        [$asset, $publicField, $privateField, $encryptedField] = $this->makePublishedProduct();

        DB::table('assets')->where('id', $asset->id)->update([
            $publicField->db_column => '<script>alert("xss")</script> Milk, cocoa',
            $privateField->db_column => 'Internal formulation secret',
            $encryptedField->db_column => 'Encrypted secret',
        ]);

        $this->get($this->publicUrl($asset->asset_tag))
            ->assertOk()
            ->assertSee('Milk, cocoa')
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSee('Internal formulation secret')
            ->assertDontSee('Encrypted secret')
            ->assertHeader('Content-Security-Policy')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_encrypted_field_cannot_be_marked_public_at_model_boundary(): void
    {
        $field = CustomField::factory()->create([
            'field_encrypted' => true,
            'display_public' => true,
            'public_section' => 'overview',
            'public_order' => 4,
        ]);

        $this->assertFalse((bool) $field->display_public);
        $this->assertNull($field->public_section);
        $this->assertSame(0, (int) $field->public_order);
    }

    public function test_public_request_increments_only_aggregate_scan_count(): void
    {
        [$asset] = $this->makePublishedProduct();
        $before = (int) $asset->scan_count;

        $this->get($this->publicUrl($asset->asset_tag))->assertOk();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'scan_count' => $before + 1,
        ]);
    }

    public function test_public_page_does_not_issue_session_or_csrf_cookies(): void
    {
        [$asset] = $this->makePublishedProduct();

        $response = $this->get($this->publicUrl($asset->asset_tag));

        $response->assertOk();
        $response->assertCookieMissing(config('session.cookie'));
        $response->assertCookieMissing('XSRF-TOKEN');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
    }

    private function makePublishedProduct(): array
    {
        $publicField = CustomField::factory()->create([
            'name' => 'Ingredients',
            'display_public' => true,
            'public_section' => 'ingredients',
            'public_order' => 10,
        ]);
        $privateField = CustomField::factory()->create([
            'name' => 'Internal Recipe Notes',
            'display_public' => false,
        ]);
        $encryptedField = CustomField::factory()->create([
            'name' => 'Protected Formula',
            'field_encrypted' => true,
        ]);
        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach([
            $publicField->id => ['order' => 1, 'required' => false],
            $privateField->id => ['order' => 2, 'required' => false],
            $encryptedField->id => ['order' => 3, 'required' => false],
        ]);
        $model = AssetModel::factory()->create([
            'name' => 'Test Beverage',
            'fieldset_id' => $fieldset->id,
        ]);
        $asset = Asset::factory()->create([
            'asset_tag' => 'PUBLIC-UNIT-01',
            'model_id' => $model->id,
            'public_product_enabled' => true,
            'public_product_published_at' => now(),
        ]);

        return [$asset, $publicField, $privateField, $encryptedField];
    }

    private function publicUrl(string $tag): string
    {
        return 'https://'.config('app.public_product_host').'/'.$tag;
    }
}

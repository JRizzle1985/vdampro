<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicProductController extends Controller
{
    public function show(Request $request, string $tag): Response
    {
        $asset = Asset::query()
            ->with([
                'company:id,name',
                'model:id,name,model_number,category_id,manufacturer_id,fieldset_id,image,updated_at',
                'model.category:id,name,image',
                'model.manufacturer:id,name',
                'model.fieldset:id,name',
                'model.fieldset.fields' => fn ($query) => $query
                    ->where('display_public', true)
                    ->where('field_encrypted', false)
                    ->orderBy('public_order')
                    ->orderBy('name'),
            ])
            ->where('asset_tag', $tag)
            ->where('public_product_enabled', true)
            ->first();

        if (! $asset) {
            return response()->view('public.product-not-found', ['tag' => $tag], 404);
        }

        $sections = collect(CustomField::PUBLIC_SECTIONS)
            ->map(fn (string $label) => ['label' => $label, 'fields' => []])
            ->all();

        foreach ($asset->model?->fieldset?->fields ?? [] as $field) {
            $value = $asset->{$field->db_column};
            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $section = array_key_exists($field->public_section, $sections)
                ? $field->public_section
                : 'overview';
            $safeUrl = $this->safePublicUrl($field->format, (string) $value);

            $sections[$section]['fields'][] = [
                'name' => $field->name,
                'value' => (string) $value,
                'url' => $safeUrl,
            ];
        }

        $sections = array_filter($sections, fn (array $section) => count($section['fields']) > 0);

        // Keep scan analytics deliberately aggregate. No query string, referrer,
        // user agent, or client identifier is persisted by this feature.
        $asset->timestamps = false;
        $asset->increment('scan_count');

        return response()->view('public.product', [
            'asset' => $asset,
            'productName' => $asset->model?->name ?: $asset->name ?: 'Product information',
            'brandName' => $asset->model?->manufacturer?->name,
            'companyName' => $asset->company?->name,
            'categoryName' => $asset->model?->category?->name,
            'imagePath' => $this->productImagePath($asset),
            'sections' => $sections,
            'updatedAt' => collect([$asset->updated_at, $asset->model?->updated_at])->filter()->max(),
            'scanCount' => (int) $asset->scan_count,
            'brandLogoPath' => $this->brandLogoPath(),
            'vdotUrl' => rtrim((string) config('app.url'), '/').'/hardware/'.$asset->id,
        ]);
    }

    private function safePublicUrl(?string $format, string $value): ?string
    {
        if ($format !== 'URL' || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $value : null;
    }

    private function productImagePath(Asset $asset): ?string
    {
        if ($asset->image) {
            return '/uploads/assets/'.rawurlencode(basename($asset->image));
        }

        if ($asset->model?->image) {
            return '/uploads/models/'.rawurlencode(basename($asset->model->image));
        }

        if ($asset->model?->category?->image) {
            return '/uploads/categories/'.rawurlencode(basename($asset->model->category->image));
        }

        return null;
    }

    private function brandLogoPath(): ?string
    {
        $logo = Setting::getSettings()?->logo;

        return $logo ? '/uploads/'.rawurlencode(basename($logo)) : null;
    }
}

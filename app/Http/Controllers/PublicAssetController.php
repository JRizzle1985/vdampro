<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Actionlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Gate;

class PublicAssetController extends Controller
{
    /**
     * Display verification details for the scanned asset tag.
     *
     * @param Request $request
     * @param string $tag
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $tag)
    {
        // Search for an exact and unique asset tag match
        $asset = Asset::with(['model.manufacturer', 'supplier', 'model.fieldset'])
            ->where('asset_tag', '=', $tag)
            ->first();

        if (!$asset) {
            return view('public/verify-error', ['tag' => $tag]);
        }

        // Increment public scan verification count
        // Note: disable timestamps touch if we only want to track count, or just standard increment
        $asset->increment('scan_count');

        // Extract manufacturer address from notes or supplier address details
        $manufacturer_address = '';
        if ($asset->model->manufacturer && $asset->model->manufacturer->notes) {
            $manufacturer_address = $asset->model->manufacturer->notes;
        } elseif ($asset->supplier) {
            $addr_parts = array_filter([
                $asset->supplier->address,
                $asset->supplier->address2,
                $asset->supplier->city,
                $asset->supplier->state,
                $asset->supplier->country,
                $asset->supplier->zip
            ]);
            if (!empty($addr_parts)) {
                $manufacturer_address = implode(', ', $addr_parts);
            }
        }

        // Initialize default medication labels
        $expiry_date = trans('general.na');
        $batch_number = trans('general.na');
        $mfg_date = trans('general.na');
        $license_number = trans('general.na');
        $custom_fields = [];

        // Traverse fieldsets dynamically
        if ($asset->model->fieldset) {
            foreach ($asset->model->fieldset->fields as $field) {
                if (! $asset->public_product_enabled || ! $field->display_public || $field->field_encrypted) {
                    continue;
                }

                $name = strtolower($field->name);
                $val = $asset->{$field->db_column_name()};

                if ($field->field_encrypted == '1') {
                    if (Gate::allows('assets.view.encrypted_custom_fields')) {
                        $val = $field->isFieldDecryptable($val) ? Helper::gracefulDecrypt($field, $val) : $val;
                    } else {
                        $val = strtoupper(trans('admin/custom_fields/general.encrypted'));
                    }
                }

                if ($field->format == 'DATE' && !empty($val) && $val != trans('general.na')) {
                    $val = Helper::getFormattedDateObject($val, 'date', false);
                }

                if (empty($val)) {
                    $val = trans('general.na');
                }

                // Map specific competitor fields dynamically if custom fields are set up
                if (str_contains($name, 'expiry') || str_contains($name, 'expiration')) {
                    $expiry_date = $val;
                } elseif (str_contains($name, 'batch') || str_contains($name, 'lot')) {
                    $batch_number = $val;
                } elseif (str_contains($name, 'manufacturing') || str_contains($name, 'mfg') || str_contains($name, 'mfg date')) {
                    $mfg_date = $val;
                } elseif (str_contains($name, 'license')) {
                    $license_number = $val;
                } else {
                    $custom_fields[$field->name] = $val;
                }
            }
        }

        // Get the first uploaded document (e-Leaflet)
        $leaflet = $asset->uploads()->first();

        // Determine product image
        $image_url = asset('img/medication_grid.png'); //Fallback premium medication grid placeholder
        if ($asset->image) {
            $image_url = asset('uploads/assets/' . $asset->image);
        } elseif ($asset->model->image) {
            $image_url = asset('uploads/models/' . $asset->model->image);
        }

        $signedIn = auth()->check();

        return view('public/verify', compact(
            'asset',
            'manufacturer_address',
            'expiry_date',
            'batch_number',
            'mfg_date',
            'license_number',
            'custom_fields',
            'leaflet',
            'image_url',
            'signedIn'
        ));
    }

    /**
     * Download or view e-leaflet PDF publicly.
     *
     * @param Request $request
     * @param string $tag
     * @param int $file_id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadLeaflet(Request $request, $tag, $file_id)
    {
        $asset = Asset::where('asset_tag', '=', $tag)->firstOrFail();

        $log = Actionlog::where('item_id', '=', $asset->id)
            ->where('item_type', '=', Asset::class)
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->findOrFail($file_id);

        $path = 'private_uploads/assets/' . $log->filename;

        if (!Storage::exists($path)) {
            abort(404, trans('general.file_upload_status.file_not_found'));
        }

        $headers = [
            'Content-Disposition' => 'inline',
        ];

        return Storage::download($path, $log->filename, $headers);
    }
}

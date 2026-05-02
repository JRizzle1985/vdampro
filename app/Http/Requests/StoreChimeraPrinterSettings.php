<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreChimeraPrinterSettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('superuser');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chimera_enabled' => 'nullable|boolean',
            'chimera_printer_ip' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $this->input('chimera_delivery_method', 'tcp') === 'tcp' && $this->boolean('chimera_enabled')),
                'ip',
            ],
            'chimera_printer_port' => 'nullable|integer|min:1|max:65535',
            'chimera_scripts_path' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $this->input('chimera_delivery_method') === 'file' && $this->boolean('chimera_enabled')),
            ],
            'chimera_delivery_method' => ['required', Rule::in(['tcp', 'file'])],
            'chimera_qr_prefix' => 'nullable|string|max:255',
        ];
    }
}

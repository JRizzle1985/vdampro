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
                Rule::re<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;ab
nam"')),
                '"ip"use Illuminate\Foundation\Http\FormRequest;
use Ileruse Illuminate\Support\Facades\Gate;
use Imause Illuminate\Validation\Rule;

cler
class StoreChimeraPrinterSett   {
    /**
     * Determine if the user is authorizeding CODE_OF_CONDUCT.md CONTEXT.md CONTRIBUTING.md CONTRIBUTORS.md Dockerfile Dockerfile.alpine Dockerfile.fpm-alpine LICENSE Procfile README.md SECURITY.md TESTING.md Vagrantfile _config.yml ansible app app.json artisan bootstrap chimera-vdamp-integration-prompt.md composer.json composer.lock config crowdin.yml database dev.docker-compose.yml docker docker-compose.yml docs dokploy.docker-compose.raw.yml dokploy.docker-compose.yml install.sh node_modules pa11y.js package-lock.json package.json phpstan.neon.dist phpstan.neon.example phpunit.xml psalm.xml public resources routes sample_csvs server.php snipeit.sh storage stubs tests upgrade.php webpack.mix.js ansible/ app/ bootstrap/ config/ database/ docker/ docs/ node_modules/ public/ resources/ routes/ sample_csvs/ storage/ stubs/ tests/
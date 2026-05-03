<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Support\Collection;

class ChimeraPrintService
{
    protected Setting $settings;

    protected ?string $templateOverride = null;

    public function __construct(?Setting $settings = null)
    {
        $this->settings = $settings ?? Setting::getSettings();
    }

    public function setTemplateOverride(?string $templatePath): self
    {
        $this->templateOverride = $templatePath;

        return $this;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templateOverride ?? $this->settings->chimera_template_path;
    }

    /**
     * Send a collection of assets to the Chimera print controller.
     *
     * @return array{success: bool, message: string, count: int, payload: string, template_path: ?string, target_host: ?string, target_port: ?int, target_path: ?string, delivery_method: string}
     */
    public function printAssets(Collection $assets): array
    {
        if (! $this->settings->chimera_enabled) {
            return ['success' => false, 'message' => 'Chimera printer is disabled.', 'count' => 0, 'payload' => '', 'template_path' => null, 'target_host' => null, 'target_port' => null, 'target_path' => null, 'delivery_method' => 'tcp'];
        }

        if ($assets->isEmpty()) {
            return ['success' => false, 'message' => 'No assets were selected.', 'count' => 0, 'payload' => '', 'template_path' => null, 'target_host' => null, 'target_port' => null, 'target_path' => null, 'delivery_method' => 'tcp'];
        }

        $lines = $assets->map(fn (Asset $asset) => $this->formatLine($asset))->all();
        $payload = implode(PHP_EOL, $lines);

        $result = $this->settings->chimera_delivery_method === 'file'
            ? $this->deliverViaFile($lines)
            : $this->deliverViaTcp($lines);

        $result['count'] = count($lines);
        $result['payload'] = $payload;
        $result['template_path'] = $this->getTemplatePath();
        $result['delivery_method'] = $this->settings->chimera_delivery_method;

        if ($this->settings->chimera_delivery_method === 'tcp') {
            $result['target_host'] = (string) $this->settings->chimera_printer_ip;
            $result['target_port'] = (int) ($this->settings->chimera_printer_port ?: 1680);
            $result['target_path'] = null;
        } else {
            $result['target_host'] = null;
            $result['target_port'] = null;
            $result['target_path'] = trim((string) $this->settings->chimera_scripts_path);
        }

        return $result;
    }

    public function formatLine(Asset $asset): string
    {
        $fields = [
            $this->buildQrContent($asset),
            $asset->company?->name ?? '',
            $asset->asset_tag ?? '',
            $asset->name ?? '',
            $asset->serial ?? '',
        ];

        $escaped = array_map(function (?string $value) {
            $value = str_replace(["\r", "\n"], ' ', (string) $value);
            $value = str_replace('"', '""', $value);

            if (str_contains($value, ',') || str_contains($value, '"')) {
                return '"'.$value.'"';
            }

            return $value;
        }, $fields);

        return implode(',', $escaped);
    }

    private function buildQrContent(Asset $asset): string
    {
        $prefix = trim((string) $this->settings->chimera_qr_prefix);

        if ($prefix === '') {
            return (string) $asset->asset_tag;
        }

        if (filter_var($prefix, FILTER_VALIDATE_URL)) {
            return rtrim($prefix, '/').'/'.$asset->asset_tag;
        }

        return $prefix.$asset->asset_tag;
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{success: bool, message: string}
     */
    private function deliverViaTcp(array $lines): array
    {
        $ip = (string) $this->settings->chimera_printer_ip;
        $port = (int) ($this->settings->chimera_printer_port ?: 1680);

        if ($ip === '') {
            return ['success' => false, 'message' => 'Printer IP address is required for TCP delivery.'];
        }

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client(
            sprintf('tcp://%s:%d', $ip, $port),
            $errno,
            $errstr,
            3
        );

        if (! $socket) {
            return ['success' => false, 'message' => $errstr !== '' ? $errstr : 'Could not connect to the Chimera printer.'];
        }

        stream_set_timeout($socket, 3);

        foreach ($lines as $line) {
            if (@fwrite($socket, $line.PHP_EOL) === false) {
                fclose($socket);

                return ['success' => false, 'message' => 'Could not send print data to the Chimera printer.'];
            }
        }

        fclose($socket);

        return ['success' => true, 'message' => 'Labels sent to the Chimera printer.'];
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{success: bool, message: string}
     */
    private function deliverViaFile(array $lines): array
    {
        $path = trim((string) $this->settings->chimera_scripts_path);

        if ($path === '') {
            return ['success' => false, 'message' => 'Scripts path is required for file delivery.'];
        }

        if (! is_dir($path)) {
            return ['success' => false, 'message' => 'The configured Chimera scripts path does not exist.'];
        }

        if (! is_writable($path)) {
            return ['success' => false, 'message' => 'The configured Chimera scripts path is not writable.'];
        }

        $filename = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'chimera_print_'.date('Ymd_His').'.txt';

        if (@file_put_contents($filename, implode(PHP_EOL, $lines).PHP_EOL) === false) {
            return ['success' => false, 'message' => 'Could not write the Chimera print file.'];
        }

        return ['success' => true, 'message' => 'Labels written to '.$filename.'.'];
    }
}

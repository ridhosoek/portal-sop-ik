<?php

namespace App\Rules;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class AllowedDocumentUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $url = (string) $value;

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $fail('URL dokumen tidak valid.');

            return;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            $fail('URL dokumen harus memiliki host.');

            return;
        }

        if ($scheme !== 'https' && ! $this->matchesAny($host, $this->settingList('allowed_intranet_hosts', config('sop.allowed_intranet_hosts', [])))) {
            $fail('URL dokumen harus HTTPS kecuali host intranet yang disetujui IT.');

            return;
        }

        $allowedHosts = $this->settingList('allowed_document_hosts', config('sop.allowed_document_hosts', []));

        if ($allowedHosts !== [] && ! $this->matchesAny($host, $allowedHosts)) {
            $fail('Domain URL dokumen belum masuk allowlist.');
        }
    }

    private function settingList(string $key, array $fallback): array
    {
        $value = Setting::query()->where('key', $key)->value('value');

        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function matchesAny(string $host, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is(strtolower($pattern), $host)) {
                return true;
            }
        }

        return false;
    }
}

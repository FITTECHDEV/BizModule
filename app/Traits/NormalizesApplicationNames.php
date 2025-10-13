<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait NormalizesApplicationNames
{
    /**
     * Normalize and map an application name to its canonical form.
     *
     * @param  string|null  $name
     * @return string
     */
    protected function normalizeAppName(?string $name): string
    {
        if (is_null($name)) {
            return '';
        }

        // Step 1: Trim, collapse whitespace, lowercase
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $name)));

        // Step 2: Define mapping of known variations to canonical names
        $map = [
            'd365 live' => 'Dynamics 365 Live',
            'd365 test' => 'Dynamics 365 Test',
            'microsoft teams' => 'Microsoft Teams',
            // Add more mappings as needed
        ];

        // Step 3: Log if unmapped
        if (!isset($map[$normalized])) {
            Log::channel('app_normalization')
                ->warning("Unmapped application name encountered: '{$name}' (normalized: '{$normalized}')");
        }

        // Step 4: Return mapped or normalized value
        return $map[$normalized] ?? $normalized;
    }
}

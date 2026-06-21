<?php

namespace App\Support;

use App\Models\Parameter;

/**
 * Persists the admin-editable subset of the "mercator" config tree
 * (cert, cve, cpe, parameters, cartography) in the `parameters` table
 * instead of rewriting config/mercator.php on disk.
 */
class MercatorSettings
{
    private const PARAM_NAME = 'mercator_config';

    /**
     * Stored overrides, decoded, on top of the static config defaults.
     */
    public static function load(): array
    {
        $stored = Parameter::getValue(self::PARAM_NAME);

        if ($stored === null) {
            return [];
        }

        return json_decode($stored, true) ?? [];
    }

    /**
     * Persist the full settings tree and refresh the in-memory config
     * so the rest of the current request sees the new values immediately.
     */
    public static function save(array $cfg): void
    {
        Parameter::setValue(self::PARAM_NAME, json_encode($cfg));

        config(['mercator' => $cfg]);
    }

    /**
     * Merge stored overrides on top of the static config/mercator.php defaults.
     * Called once at boot.
     */
    public static function applyToConfig(): void
    {
        $overrides = self::load();

        if ($overrides === []) {
            return;
        }

        config(['mercator' => array_replace_recursive(config('mercator', []), $overrides)]);
    }
}

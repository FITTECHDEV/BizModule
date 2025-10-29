<?php

namespace App\Traits;

trait UuidConversion
{
    /**
     * Convert a UUID string to a legacy-safe BIGINT surrogate
     *
     * @param string $uuid
     * @return int
     */
    public function uuidToBigInt(string $uuid): int
    {
        return abs(crc32($uuid)); // Simple, deterministic, legacy-safe
    }
}

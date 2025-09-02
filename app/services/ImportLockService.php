<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportLockService
{
    private const DEFAULT_LOCK_KEY = 'import_in_progress';
    private const DEFAULT_LOCK_TIMEOUT = 300; // 5 minutes

    /**
     * Acquire a lock for the specified import process.
     *
     * @param string|null $lockKey Custom lock key for specific import type
     * @param int|null $timeout Lock timeout in seconds
     * @return bool True if lock acquired, false if already locked
     */
    public function acquireLock(?string $lockKey = null, ?int $timeout = null): bool
    {
        $lockKey = $lockKey ?? self::DEFAULT_LOCK_KEY;
        $timeout = $timeout ?? config('import.lock_timeout', self::DEFAULT_LOCK_TIMEOUT);

        $acquired = Cache::add($lockKey, [
            'acquired_at' => Carbon::now('Africa/Nairobi')->toDateTimeString(),
            'process' => getmypid() ?: 'unknown',
        ], $timeout);

        if ($acquired) {
            Log::info('Import lock acquired', ['lock_key' => $lockKey, 'timeout' => $timeout]);
        } else {
            Log::warning('Import lock acquisition failed - another import in progress', [
                'lock_key' => $lockKey,
                'lock_details' => Cache::get($lockKey),
            ]);
        }

        return $acquired;
    }

    /**
     * Release the lock for the specified import process.
     *
     * @param string|null $lockKey Custom lock key for specific import type
     */
    public function releaseLock(?string $lockKey = null): void
    {
        $lockKey = $lockKey ?? self::DEFAULT_LOCK_KEY;
        Cache::forget($lockKey);
        Log::info('Import lock released', ['lock_key' => $lockKey]);
    }

    /**
     * Check if the lock is currently active.
     *
     * @param string|null $lockKey Custom lock key for specific import type
     * @return bool True if locked, false otherwise
     */
    public function isLocked(?string $lockKey = null): bool
    {
        $lockKey = $lockKey ?? self::DEFAULT_LOCK_KEY;
        return Cache::has($lockKey);
    }

    /**
     * Check if the lock is stale and force-release if necessary.
     *
     * @param string|null $lockKey Custom lock key for specific import type
     * @param int $maxAge Maximum age of lock in seconds
     * @return bool True if lock was stale and released, false otherwise
     */
    public function releaseStaleLock(?string $lockKey = null, int $maxAge = 3600): bool
    {
        $lockKey = $lockKey ?? self::DEFAULT_LOCK_KEY;

        if (!Cache::has($lockKey)) {
            return false;
        }

        $lockData = Cache::get($lockKey);
        if (!isset($lockData['acquired_at'])) {
            Cache::forget($lockKey);
            Log::warning('Released invalid lock with missing acquired_at', ['lock_key' => $lockKey]);
            return true;
        }

        $acquiredAt = Carbon::parse($lockData['acquired_at'], 'Africa/Nairobi');
        if ($acquiredAt->diffInSeconds(Carbon::now('Africa/Nairobi')) > $maxAge) {
            Cache::forget($lockKey);
            Log::warning('Released stale lock', [
                'lock_key' => $lockKey,
                'acquired_at' => $lockData['acquired_at'],
                'process' => $lockData['process'],
            ]);
            return true;
        }

        return false;
    }
}
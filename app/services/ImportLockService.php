<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class ImportLockService
{
    private const DEFAULT_LOCK_KEY = 'import_in_progress';
    private const DEFAULT_LOCK_TIMEOUT = 300; // 5 minutes
    private const DEFAULT_MAX_AGE = 3600; // 1 hour
    private const LOCK_PREFIX = 'import_lock:';
    private const HEARTBEAT_INTERVAL = 30; // seconds

    private function getProcessId(): string
    {
        return getmypid() ?: uniqid('proc_', true);
    }

    public function generateLockKey(?string $baseKey = null, array $context = []): string
    {
        $baseKey = $baseKey ?? self::DEFAULT_LOCK_KEY;
        $suffix = !empty($context) ? ':' . md5(json_encode($context)) : '';
        return self::LOCK_PREFIX . $baseKey . $suffix;
    }

    /**
     * Acquire a lock with improved error handling and validation
     */
    public function acquireLock(?string $lockKey = null, ?int $timeout = null, array $context = []): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $timeout = $timeout ?? config('import.lock_timeout', self::DEFAULT_LOCK_TIMEOUT);

        // Validate timeout
        if ($timeout <= 0 || $timeout > 7200) { // Max 2 hours
            throw new \InvalidArgumentException('Lock timeout must be between 1 and 7200 seconds');
        }

        // Clean up any stale locks first
        $this->releaseStaleLock($lockKey, $timeout * 2, $context);

        $owner = $this->getProcessId();
        $lockData = [
            'acquired_at' => Carbon::now('Africa/Nairobi')->toDateTimeString(),
            'expires_at' => Carbon::now('Africa/Nairobi')->addSeconds($timeout)->toDateTimeString(),
            'owner' => $owner,
            'context' => $context,
            'heartbeat_at' => Carbon::now('Africa/Nairobi')->toDateTimeString(),
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
        ];

        $acquired = Cache::add($lockKey, $lockData, $timeout);

        $this->logLockState($lockKey, $context, $acquired, $owner);
        return $acquired;
    }

    /**
     * Enhanced lock release with better error handling
     */
    public function releaseLock(?string $lockKey = null, array $context = []): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey);
        $owner = $this->getProcessId();

        if (!$lockData) {
            Log::info('ℹ️ Attempted to release non-existent lock', [
                'lock_key' => $lockKey,
                'context' => $context
            ]);
            return false;
        }

        if (($lockData['owner'] ?? null) === $owner) {
            Cache::forget($lockKey);
            Log::info('🔓 Import lock released', [
                'lock_key' => $lockKey,
                'context' => $context,
                'owner' => $owner,
                'duration' => $this->calculateDuration($lockData['acquired_at'] ?? null)
            ]);
            return true;
        } else {
            Log::warning('⚠️ Attempted to release lock not owned', [
                'lock_key' => $lockKey,
                'context' => $context,
                'current_pid' => $owner,
                'lock_owner' => $lockData['owner'] ?? 'unknown',
            ]);
            return false;
        }
    }

    /**
     * Check if current process is locked out (not the owner)
     */
    public function isLocked(?string $lockKey = null, array $context = []): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey);
        $owner = $this->getProcessId();

        return $lockData && ($lockData['owner'] ?? null) !== $owner;
    }

    /**
     * Check if current process owns the lock
     */
    public function isLockOwner(?string $lockKey = null, array $context = []): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey);
        return isset($lockData['owner']) && $lockData['owner'] === $this->getProcessId();
    }

    /**
     * Get detailed lock information
     */
    public function getLockDetails(?string $lockKey = null, array $context = []): array
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey) ?? [];
        
        if (!empty($lockData)) {
            $lockData['is_expired'] = $this->isExpired($lockData['acquired_at'] ?? '', self::DEFAULT_MAX_AGE);
            $lockData['time_remaining'] = $this->getTimeRemaining($lockData['expires_at'] ?? '');
        }
        
        return $lockData;
    }

    /**
     * Enhanced stale lock cleanup with better validation
     */
    public function releaseStaleLock(?string $lockKey = null, int $maxAge = self::DEFAULT_MAX_AGE, array $context = []): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey);

        if (!$lockData) return false;

        // Check for malformed lock data
        if (!isset($lockData['acquired_at'])) {
            Cache::forget($lockKey);
            Log::warning('⚠️ Released invalid lock with missing acquired_at', ['lock_key' => $lockKey]);
            return true;
        }

        if ($this->isExpired($lockData['acquired_at'], $maxAge)) {
            Cache::forget($lockKey);
            Log::warning('🧹 Released stale lock', [
                'lock_key' => $lockKey,
                'acquired_at' => $lockData['acquired_at'],
                'expires_at' => $lockData['expires_at'] ?? 'unknown',
                'owner' => $lockData['owner'] ?? 'unknown',
                'context' => $lockData['context'] ?? [],
                'age_seconds' => $this->getAge($lockData['acquired_at'])
            ]);
            return true;
        }

        return false;
    }

    /**
     * Force release with audit logging
     */
    public function forceRelease(?string $lockKey = null, array $context = [], string $reason = 'Manual force release'): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey);
        
        if (!$lockData) {
            Log::info('ℹ️ Attempted to force release non-existent lock', ['lock_key' => $lockKey]);
            return false;
        }

        Cache::forget($lockKey);
        Log::warning('🔧 Lock forcibly released', [
            'lock_key' => $lockKey,
            'context' => $context,
            'reason' => $reason,
            'original_owner' => $lockData['owner'] ?? 'unknown',
            'force_released_by' => $this->getProcessId(),
            'lock_age' => $this->calculateDuration($lockData['acquired_at'] ?? null)
        ]);
        
        return true;
    }

    /**
     * Heartbeat mechanism to show lock is still active
     */
    public function updateHeartbeat(?string $lockKey = null, array $context = []): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $lockData = Cache::get($lockKey);

        if (!$lockData || ($lockData['owner'] ?? null) !== $this->getProcessId()) {
            return false;
        }

        $lockData['heartbeat_at'] = Carbon::now('Africa/Nairobi')->toDateTimeString();
        $lockData['memory_usage'] = memory_get_usage(true);
        
        // Get remaining TTL to preserve it
        $ttl = Cache::getStore() instanceof \Illuminate\Cache\RedisStore 
            ? Redis::ttl($lockKey) 
            : 300; // fallback
            
        Cache::put($lockKey, $lockData, max($ttl, 1));
        
        return true;
    }

    /**
     * Wait for lock with exponential backoff
     */
    public function waitForLock(?string $lockKey = null, array $context = [], int $maxWaitTime = 300, int $pollInterval = 5): bool
    {
        $lockKey = $this->generateLockKey($lockKey, $context);
        $startTime = time();
        $attempt = 0;

        while ((time() - $startTime) < $maxWaitTime) {
            if ($this->acquireLock($lockKey, null, $context)) {
                Log::info('🔓 Lock acquired after waiting', [
                    'lock_key' => $lockKey,
                    'wait_time' => time() - $startTime,
                    'attempts' => $attempt + 1
                ]);
                return true;
            }

            $attempt++;
            $waitTime = min($pollInterval * pow(2, min($attempt - 1, 3)), 30); // Cap at 30 seconds
            
            Log::debug('⏳ Waiting for lock', [
                'lock_key' => $lockKey,
                'attempt' => $attempt,
                'wait_time' => $waitTime,
                'elapsed' => time() - $startTime
            ]);

            sleep($waitTime);
        }

        Log::warning('⏰ Lock wait timeout', [
            'lock_key' => $lockKey,
            'max_wait_time' => $maxWaitTime,
            'attempts' => $attempt
        ]);

        return false;
    }

    // Helper methods
    private function isExpired(string $acquiredAt, int $maxAge): bool
    {
        if (empty($acquiredAt)) return true;
        
        try {
            $acquired = Carbon::parse($acquiredAt, 'Africa/Nairobi');
            return $acquired->diffInSeconds(Carbon::now('Africa/Nairobi')) > $maxAge;
        } catch (\Exception $e) {
            Log::error('Error parsing lock timestamp', ['acquired_at' => $acquiredAt, 'error' => $e->getMessage()]);
            return true; // Treat unparseable dates as expired
        }
    }

    private function getAge(string $acquiredAt): int
    {
        try {
            return Carbon::parse($acquiredAt, 'Africa/Nairobi')->diffInSeconds(Carbon::now('Africa/Nairobi'));
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTimeRemaining(string $expiresAt): int
    {
        try {
            $expires = Carbon::parse($expiresAt, 'Africa/Nairobi');
            return max(0, $expires->diffInSeconds(Carbon::now('Africa/Nairobi'), false));
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateDuration(?string $acquiredAt): ?string
    {
        if (!$acquiredAt) return null;
        
        try {
            $duration = Carbon::parse($acquiredAt, 'Africa/Nairobi')->diffInSeconds(Carbon::now('Africa/Nairobi'));
            return gmdate('H:i:s', $duration);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function logLockState(string $lockKey, array $context, bool $acquired, string $owner): void
    {
        if ($acquired) {
            Log::info('✅ Lock acquired', [
                'lock_key' => $lockKey,
                'context' => $context,
                'owner' => $owner,
                'timestamp' => Carbon::now('Africa/Nairobi')->toDateTimeString()
            ]);
        } else {
            $existing = Cache::get($lockKey);
            Log::warning('⛔ Lock acquisition failed', [
                'lock_key' => $lockKey,
                'context' => $context,
                'attempted_owner' => $owner,
                'existing_lock' => [
                    'owner' => $existing['owner'] ?? 'unknown',
                    'acquired_at' => $existing['acquired_at'] ?? 'unknown',
                    'expires_at' => $existing['expires_at'] ?? 'unknown',
                    'heartbeat_at' => $existing['heartbeat_at'] ?? 'unknown',
                ]
            ]);
        }
    }

    /**
     * Clean up all expired locks
     */
    public function cleanupExpiredLocks(): int
    {
        $cleaned = 0;
        $locks = $this->listActiveLocks();

        foreach ($locks as $lock) {
            if (isset($lock['acquired_at']) && $this->isExpired($lock['acquired_at'], self::DEFAULT_MAX_AGE)) {
                Cache::forget($lock['key']);
                $cleaned++;
                
                Log::info('🧹 Cleaned up expired lock', [
                    'lock_key' => $lock['key'],
                    'owner' => $lock['owner'] ?? 'unknown',
                    'age' => $this->getAge($lock['acquired_at'])
                ]);
            }
        }

        if ($cleaned > 0) {
            Log::info("🧹 Lock cleanup completed", ['cleaned_count' => $cleaned]);
        }

        return $cleaned;
    }

    /**
     * Get import status for monitoring
     */
    public function getImportStatus(?string $lockKey = null, array $context = []): array
    {
        $details = $this->getLockDetails($lockKey, $context);
        
        if (empty($details)) {
            return [
                'status' => 'available',
                'locked' => false,
                'message' => 'No import currently running'
            ];
        }

        $isOwner = $this->isLockOwner($lockKey, $context);
        $timeRemaining = $this->getTimeRemaining($details['expires_at'] ?? '');

        return [
            'status' => $isOwner ? 'running' : 'blocked',
            'locked' => true,
            'is_owner' => $isOwner,
            'acquired_at' => $details['acquired_at'] ?? null,
            'expires_at' => $details['expires_at'] ?? null,
            'owner' => $details['owner'] ?? 'unknown',
            'time_remaining_seconds' => $timeRemaining,
            'context' => $details['context'] ?? [],
            'heartbeat_at' => $details['heartbeat_at'] ?? null,
            'message' => $isOwner 
                ? "Import is running (you own this lock)" 
                : "Import is blocked - another process is running"
        ];
    }

    // ... (keep other existing methods like listActiveLocks, extendLock, etc.)
}
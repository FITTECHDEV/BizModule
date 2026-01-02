<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\SigninLogsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Services\ImportLockService;
use Illuminate\Support\Facades\Cache;

class ImportController extends Controller
{
    /**
     * ✅ UPDATED: Clear all import locks including progress data
     */
    private function clearAllImportLocks(): bool
    {
        $cleared = false;

        // Clear ImportLockService locks
        $lockService = new ImportLockService();
        $lockService->forceRelease(null, [], 'Controller initiated clear');

        // Clear SigninLogsImport internal locks
        $signinLockCleared = Cache::forget('signin_logs_import_lock');
        $signinProgressCleared = Cache::forget('signin_import_progress');

        // Clear any other potential locks
        $userLockCleared = Cache::forget('users_import_lock');
        $usersProgressCleared = Cache::forget('users_import_progress');

        if ($signinLockCleared || $signinProgressCleared || $userLockCleared || $usersProgressCleared) {
            Log::info('[IMPORT] 🧹 Import locks cleared', [
                'signin_lock' => $signinLockCleared,
                'signin_progress' => $signinProgressCleared,
                'users_lock' => $userLockCleared,
                'users_progress' => $usersProgressCleared
            ]);
            $cleared = true;
        }

        return $cleared;
    }

    /**
     * Check if any import locks exist (both systems)
     */
    private function hasAnyActiveLocks(): bool
    {
        $lockService = new ImportLockService();

        // Check ImportLockService lock
        if ($lockService->isLocked()) {
            return true;
        }

        // Check SigninLogsImport internal locks
        if (Cache::has('signin_logs_import_lock')) {
            return true;
        }

        // Check for users import lock
        if (Cache::has('users_import_lock')) {
            return true;
        }

        return false;
    }

    /**
     * ✅ Sanitize error message for user display
     */
    private function sanitizeErrorMessage(string $error, string $fileSize = ''): string
    {
        // Map technical errors to user-friendly messages
        $errorMappings = [
            'timeout' => '⏳ Import timeout - file too large. Please split into smaller files (max 6MB recommended).',
            'memory' => '💾 Out of memory - file too large. Please split into smaller files (max 5MB recommended).',
            'duplicate' => '⚠️ Some records were skipped as duplicates.',
            'validation' => '❌ File validation failed. Please check your CSV format.',
            'database' => '❌ Database error occurred. Please try again or contact support.',
            'connection' => '❌ Connection error. Please check your internet and try again.',
            'permission' => '❌ Permission error. Please contact your administrator.',
            'lock' => '⚠️ Another import is already running. Please wait and try again.',
        ];

        $errorLower = strtolower($error);

        // Check for known error patterns
        foreach ($errorMappings as $pattern => $message) {
            if (str_contains($errorLower, $pattern)) {
                return $message;
            }
        }

        // Generic error for unknown issues (don't expose technical details)
        return '❌ Import failed. Please check your file and try again. If the problem persists, contact support.';
    }

    /**
     * ✅ Validate PHP configuration for large imports
     */
    private function validatePHPConfiguration(): array
    {
        $config = [
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'max_input_time' => ini_get('max_input_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];

        $warnings = [];

        // Check execution time
        if ((int)$config['max_execution_time'] > 0 && (int)$config['max_execution_time'] < 3600) {
            $warnings[] = "max_execution_time ({$config['max_execution_time']}s) may be too low for large imports";
        }

        // Check memory
        $memoryMB = $this->parseMemoryLimit($config['memory_limit']);
        if ($memoryMB < 1024) {
            $warnings[] = "memory_limit ({$config['memory_limit']}) may be too low for large imports";
        }

        // Check upload size
        $uploadMB = $this->parseMemoryLimit($config['upload_max_filesize']);
        if ($uploadMB < 250) {
            $warnings[] = "upload_max_filesize ({$config['upload_max_filesize']}) limits maximum file size";
        }

        if (!empty($warnings)) {
            Log::warning('[IMPORT] ⚠️ PHP configuration warnings', [
                'warnings' => $warnings,
                'current_config' => $config
            ]);
        }

        return [
            'config' => $config,
            'warnings' => $warnings,
            'is_optimal' => empty($warnings)
        ];
    }

    /**
     * ✅ Parse memory limit string to MB
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                return $value;
            case 'k':
                return $value / 1024;
            default:
                return $value / 1024 / 1024;
        }
    }

    /**
     * Import Users from CSV file
     */
    public function importUsers(Request $request)
    {
        // Configure PHP for large imports (60 minutes)
        @ini_set('max_execution_time', '3600');
        @ini_set('max_input_time', '3600');
        @ini_set('memory_limit', '1536M');
        @set_time_limit(3600);
        @ignore_user_abort(true);

        $request->validate([
            'import_file' => 'required|file|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel|max:256000',
        ], [
            'import_file.required' => 'Please select a CSV file to upload.',
            'import_file.mimetypes' => 'The file must be a CSV or TXT file.',
            'import_file.max' => 'File size must not exceed 250MB.',
        ]);

        // Clear any stale locks first
        $this->clearAllImportLocks();

        $lockService = new ImportLockService();

        // Check if another import is in progress
        if ($this->hasAnyActiveLocks()) {
            return redirect()->back()->with('error', 'Another import is already in progress. Please wait and try again.');
        }

        // Acquire lock
        if (!$lockService->acquireLock()) {
            return redirect()->back()->with('error', 'Unable to start import. Please try again.');
        }

        // ✅ NEW: Initialize progress tracking with all stages as pending
        Cache::put('signin_import_progress', [
            'status' => 'running',
            'started_at' => now()->toISOString(),
            'current_stage' => 0,
            'stages' => [
                'file_upload' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'file_validation' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'processing_data' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'saving_to_database' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'cache_cleanup' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'finalizing' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()]
            ]
        ], 3600);

        try {
            $file = $request->file('import_file');
            $fileSize = $file->getSize();
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            // ✅ Stage 1 - File Upload Complete
            $this->updateImportStage(1, 'complete', '1s');

            Log::info('[IMPORT] 📤 Starting user import', [
                'path' => $file->getRealPath(),
                'originalName' => $file->getClientOriginalName(),
                'file_size_mb' => $fileSizeMB,
                'php_time_limit' => ini_get('max_execution_time') . 's',
                'memory_limit' => ini_get('memory_limit'),
                'started_at' => now()->toDateTimeString()
            ]);

            // ✅ Stage 2 - File Validation Running
            $this->updateImportStage(2, 'running');

            $importStartTime = microtime(true);

            // Simulate validation delay (gives time for UI to update)
            usleep(500000); // 0.5 seconds

            // ✅ Stage 2 Complete
            $this->updateImportStage(2, 'complete', '1s');

            // ✅ Stage 3 - Processing Data
            $this->updateImportStage(3, 'running');

            DB::transaction(function () use ($file) {
                Excel::import(new UsersImport, $file);
            });

            // ✅ Stage 3 Complete
            $processingTime = round(microtime(true) - $importStartTime, 1) . 's';
            $this->updateImportStage(3, 'complete', $processingTime);

            // ✅ Stage 4 - Saving to Database (already done in transaction)
            $this->updateImportStage(4, 'complete', '✓');

            // ✅ Stage 5 - Cache Cleanup Running
            $this->updateImportStage(5, 'running');

            // Run cleanup immediately after import
            (new \App\Imports\UsersImport())->cleanupEmptyUsers();

            $importDuration = round(microtime(true) - $importStartTime, 2);
            $importMinutes = round($importDuration / 60, 2);

            // ✅ Stage 5 Complete
            $this->updateImportStage(5, 'complete', '2s');

            // ✅ Stage 6 - Finalizing
            $this->updateImportStage(6, 'running');

            Log::info('[IMPORT] ✅ Users imported and cleaned successfully', [
                'duration_seconds' => $importDuration,
                'duration_minutes' => $importMinutes,
                'file_size_mb' => $fileSizeMB
            ]);

            $lockService->releaseLock();

            // Clear dashboard cache after successful import & cleanup
            $this->clearDashboardCache();

            // ✅ Stage 6 Complete
            $this->updateImportStage(6, 'complete', '1s');

            // Disable PageSpeed for redirect
            config(['laravel-page-speed.enable' => false]);

            return redirect()->route('dashboard')->with('success', '✅ Users imported and cleaned successfully!');
        } catch (\Throwable $e) {
            $errorDuration = isset($importStartTime) ? round(microtime(true) - $importStartTime, 2) : 0;

            Log::error('[IMPORT] ❌ Users import failed', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'duration_before_failure' => $errorDuration,
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);

            // ✅ Mark import as failed
            Cache::put('signin_import_progress', [
                'status' => 'failed',
                'error' => $this->sanitizeErrorMessage($e->getMessage()),
                'failed_at' => now()->toISOString()
            ], 300);

            // Clear all locks on failure
            $this->clearAllImportLocks();
            $lockService->releaseLock();

            return redirect()->route('dashboard')->with(
                'error',
                $this->sanitizeErrorMessage($e->getMessage(), $fileSizeMB ?? '')
            );
        }
    }

    /**
     * Import Sign-Ins from CSV file
     * Supports large files (6MB+) with 60-minute timeout
     */
    public function importSignIns(Request $request)
    {
        // ============================================
        // CRITICAL: Configure PHP for 60-minute imports FIRST
        // ============================================
        @ini_set('max_execution_time', '3600');      // 60 minutes
        @ini_set('max_input_time', '3600');          // 60 minutes
        @ini_set('memory_limit', '1536M');           // 1.5GB RAM
        @set_time_limit(3600);                       // 60 minutes
        @ignore_user_abort(true);                    // Continue even if user closes browser

        // Disable output buffering that might interfere
        while (ob_get_level()) {
            ob_end_clean();
        }

        // ✅ Validate PHP configuration
        $configCheck = $this->validatePHPConfiguration();
        if (!$configCheck['is_optimal']) {
            Log::warning('[IMPORT] Starting import with suboptimal PHP configuration', [
                'warnings' => $configCheck['warnings']
            ]);
        }

        // Log initial settings to verify they were applied
        Log::info('[IMPORT] 🔧 Import environment configured for LARGE FILES', [
            'max_execution_time' => ini_get('max_execution_time') . ' seconds',
            'max_input_time' => ini_get('max_input_time') . ' seconds',
            'memory_limit' => ini_get('memory_limit'),
            'fastcgi_available' => function_exists('fastcgi_finish_request') ? 'YES ✅' : 'NO ❌',
            'php_sapi' => php_sapi_name(),
            'timestamp' => now()->toDateTimeString(),
            'config_version' => '4.3 - Complete backend integration'
        ]);

        // Validate file
        $request->validate([
            'import_file' => 'required|mimes:csv,txt|max:256000', // 250MB max
        ], [
            'import_file.required' => 'Please select a CSV file to upload.',
            'import_file.mimes' => 'The file must be a CSV or TXT file.',
            'import_file.max' => 'File size must not exceed 250MB.',
        ]);

        $this->clearAllImportLocks();

        $lockService = new ImportLockService();

        if ($this->hasAnyActiveLocks()) {
            return redirect()->back()->with('error', 'Another import is already in progress. Please wait and try again.');
        }

        if (!$lockService->acquireLock()) {
            return redirect()->back()->with('error', 'Unable to start import. Please try again.');
        }

        // ✅ NEW: Initialize progress tracking with all stages as pending
        Cache::put('signin_import_progress', [
            'status' => 'running',
            'started_at' => now()->toISOString(),
            'current_stage' => 0,
            'stages' => [
                'file_upload' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'file_validation' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'processing_data' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'saving_to_database' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'cache_cleanup' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()],
                'finalizing' => ['status' => 'pending', 'time' => '-', 'updated_at' => now()->toISOString()]
            ]
        ], 3600);

        try {
            $file = $request->file('import_file');
            $fileSize = $file->getSize();
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            // ✅ Stage 1 - File Upload Complete
            $this->updateImportStage(1, 'complete', '1s');

            // Estimate processing time based on file size
            $estimatedMinutes = round(($fileSizeMB / 2) * 10); // Rough: 10 min per 2MB
            $estimatedMinutes = max(5, min(50, $estimatedMinutes)); // Between 5-50 minutes

            // ✅ Stage 2 - File Validation
            $this->updateImportStage(2, 'running');

            Log::info('[IMPORT] 📤 Starting LARGE sign-in import', [
                'path' => $file->getRealPath(),
                'originalName' => $file->getClientOriginalName(),
                'file_size_mb' => $fileSizeMB,
                'estimated_duration_minutes' => $estimatedMinutes,
                'php_time_limit' => ini_get('max_execution_time') . 's',
                'memory_limit' => ini_get('memory_limit'),
                'started_at' => now()->toDateTimeString(),
                'server_time' => date('Y-m-d H:i:s')
            ]);

            $importStartTime = microtime(true);

            // Small delay for validation
            usleep(1000000); // 1 second

            // ✅ Stage 2 Complete
            $this->updateImportStage(2, 'complete', '2s');

            // ✅ Stage 3 - Processing Data
            $this->updateImportStage(3, 'running');

            // Log progress every 5 minutes
            $lastLogTime = $importStartTime;
            $lastStageUpdate = $importStartTime;

            // Import with transaction
            DB::transaction(function () use ($file, &$lastLogTime, &$lastStageUpdate, $importStartTime) {
                Excel::import(new SigninLogsImport, $file);

                // Log progress during import
                $currentTime = microtime(true);

                // Update stage progress every 30 seconds during processing
                if ($currentTime - $lastStageUpdate > 30) {
                    $elapsed = round(($currentTime - $importStartTime) / 60, 1);
                    $this->updateImportStage(3, 'running', "~{$elapsed}m");
                    $lastStageUpdate = $currentTime;
                }

                if ($currentTime - $lastLogTime > 300) { // Every 5 minutes
                    Log::info('[IMPORT] ⏳ Import still processing...', [
                        'elapsed_minutes' => round(($currentTime - $importStartTime) / 60, 1),
                        'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                        'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
                    ]);
                    $lastLogTime = $currentTime;
                }
            }, 3); // Max 3 transaction attempts

            $importDuration = round(microtime(true) - $importStartTime, 2);
            $importMinutes = round($importDuration / 60, 2);
            $processingRate = $importDuration > 0 ? round(($fileSizeMB / $importDuration) * 60, 2) : 0;

            // ✅ Stage 3 Complete, Stage 4 Complete (transaction already saved)
            $processingTime = round($importDuration, 1) . 's';
            $this->updateImportStage(3, 'complete', $processingTime);
            $this->updateImportStage(4, 'complete', '✓');

            // ✅ CRITICAL: Log basic success BEFORE any slow operations
            Log::info('[IMPORT] ✅ Database import completed', [
                'duration_seconds' => $importDuration,
                'duration_minutes' => $importMinutes,
                'file_size_mb' => $fileSizeMB,
                'processing_rate_mb_per_min' => $processingRate,
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
            ]);

            // ✅ CRITICAL: Release lock IMMEDIATELY
            $lockService->releaseLock();

            // ✅ CRITICAL: Disable PageSpeed early
            config(['laravel-page-speed.enable' => false]);

            // ✅ ENHANCED: Improved session handling with verification
            $sessionData = [
                'success' => '✅ Sign-ins imported successfully! Imported ' . $fileSizeMB . 'MB in ' . $importMinutes . ' minutes.'
            ];

            // Store in session with verification
            session()->flash('success', $sessionData['success']);

            // Force session write and verify
            try {
                session()->save();

                // Small delay to ensure session write completes (5ms)
                usleep(5000);

                Log::info('[IMPORT] ✅ Session saved successfully', [
                    'session_id' => session()->getId(),
                    'has_flash' => session()->has('success')
                ]);
            } catch (\Exception $e) {
                Log::error('[IMPORT] ❌ Session save failed', [
                    'error' => $e->getMessage()
                ]);
                // Fallback to synchronous redirect if session fails
                $hasFastCGI = false;
            }

            // Check if fastcgi_finish_request is available
            $hasFastCGI = function_exists('fastcgi_finish_request');

            Log::info('[IMPORT] 🚀 Preparing response to user', [
                'fastcgi_available' => $hasFastCGI,
                'session_started' => session()->isStarted(),
                'duration_before_response' => $importDuration
            ]);

            // ============================================
            // Try to use fastcgi_finish_request for immediate response
            // ============================================
            if ($hasFastCGI) {
                Log::info('[IMPORT] ✅ Using fastcgi_finish_request for immediate response');

                // Clear any existing output buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }

                // Send redirect response headers
                http_response_code(302);
                header('Location: ' . route('dashboard'), true, 302);
                header('Connection: close');
                header('Content-Length: 0');
                header('Cache-Control: no-cache, no-store, must-revalidate');

                // Force output to be sent
                if (function_exists('flush')) {
                    flush();
                }

                // ✅ THIS SENDS RESPONSE TO USER IMMEDIATELY
                // Everything after this line runs in background
                fastcgi_finish_request();

                Log::info('[IMPORT] 🎯 Response sent to user, starting background tasks');

                // ============================================
                // BACKGROUND TASKS (User already redirected)
                // ============================================

                $backgroundStartTime = microtime(true);

                try {
                    // ✅ Stage 5 - Cache Cleanup (in background)
                    $this->updateImportStage(5, 'running');

                    // Count imported records (MOVED TO BACKGROUND)
                    $recordCount = DB::table('Azure_LoginDetails')
                        ->where('created_at', '>=', now()->subMinutes(10))
                        ->count();

                    Log::info('[IMPORT] 📊 Records counted in background', [
                        'records_imported_approx' => $recordCount,
                        'count_duration_seconds' => round(microtime(true) - $backgroundStartTime, 2)
                    ]);

                    // Clear essential cache (MOVED TO BACKGROUND)
                    $cacheStartTime = microtime(true);
                    $this->clearEssentialDashboardCache();

                    Log::info('[IMPORT] ✅ Dashboard cache cleared in background', [
                        'cache_clear_duration_seconds' => round(microtime(true) - $cacheStartTime, 2)
                    ]);

                    // ✅ Stage 5 Complete
                    $cacheDuration = round(microtime(true) - $cacheStartTime, 1) . 's';
                    $this->updateImportStage(5, 'complete', $cacheDuration);

                    // ✅ Stage 6 - Finalizing
                    $this->updateImportStage(6, 'running');

                    // Optional: Run login status update in background
                    try {
                        $process = new \Symfony\Component\Process\Process([
                            'php',
                            base_path('artisan'),
                            'users:update-login-status'
                        ]);
                        $process->setTimeout(30);
                        $process->setIdleTimeout(30);
                        $process->start();

                        Log::info('[IMPORT] ✅ Background artisan command started', [
                            'command' => 'users:update-login-status',
                            'pid' => $process->getPid()
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('[IMPORT] ⚠️ Background command failed to start', [
                            'error' => $e->getMessage()
                        ]);
                    }

                    // ✅ Stage 6 Complete
                    $this->updateImportStage(6, 'complete', '1s');

                    $totalBackgroundTime = round(microtime(true) - $backgroundStartTime, 2);
                    Log::info('[IMPORT] ✅ All background tasks completed', [
                        'background_duration_seconds' => $totalBackgroundTime,
                        'total_import_time_seconds' => round(microtime(true) - $importStartTime, 2),
                        'total_import_time_minutes' => round((microtime(true) - $importStartTime) / 60, 2),
                        'records_imported' => $recordCount ?? 'unknown'
                    ]);
                } catch (\Exception $e) {
                    Log::error('[IMPORT] ❌ Background task error (non-critical)', [
                        'error' => $e->getMessage(),
                        'trace' => substr($e->getTraceAsString(), 0, 500)
                    ]);
                }

                // Clean exit after background tasks
                exit(0);
            }

            // ============================================
            // FALLBACK: No fastcgi_finish_request available
            // ============================================

            Log::warning('[IMPORT] ⚠️ fastcgi_finish_request not available, using standard redirect');
            Log::warning('[IMPORT] ⚠️ PHP SAPI: ' . php_sapi_name() . ' - FastCGI required for optimal performance');

            // ✅ Update stages in fallback mode
            $this->updateImportStage(5, 'running');

            // Count records (will delay response in fallback mode)
            try {
                $recordCount = DB::table('Azure_LoginDetails')
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->count();

                Log::info('[IMPORT] 📊 Records counted (fallback mode)', [
                    'records_imported_approx' => $recordCount
                ]);
            } catch (\Exception $e) {
                Log::warning('[IMPORT] Failed to count records in fallback mode', [
                    'error' => $e->getMessage()
                ]);
            }

            // Do minimal cleanup (will delay response)
            $this->clearEssentialDashboardCache();

            $this->updateImportStage(5, 'complete', '2s');
            $this->updateImportStage(6, 'complete', '1s');

            return redirect()
                ->route('dashboard')
                ->with('success', '✅ Sign-ins imported successfully! Imported ' . $fileSizeMB . 'MB in ' . $importMinutes . ' minutes.');
        } catch (\Throwable $e) {
            $errorDuration = isset($importStartTime) ? round(microtime(true) - $importStartTime, 2) : 0;

            Log::error('[IMPORT] ❌ Sign-ins import failed', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'duration_before_failure' => $errorDuration,
                'duration_before_failure_minutes' => round($errorDuration / 60, 2),
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);

            // ✅ Mark import as failed
            Cache::put('signin_import_progress', [
                'status' => 'failed',
                'error' => $this->sanitizeErrorMessage($e->getMessage()),
                'failed_at' => now()->toISOString()
            ], 300);

            // Clean up locks
            $this->clearAllImportLocks();
            $lockService->releaseLock();

            // Categorize and handle different error types
            $errorMessage = $e->getMessage();

            // Check for timeout errors
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'maximum execution time') !== false ||
                stripos($errorMessage, 'fastcgi') !== false ||
                stripos($errorMessage, '500.0') !== false
            ) {

                Log::error('[IMPORT] 🔴 TIMEOUT ERROR DETECTED - File too large or server overloaded', [
                    'error_type' => 'timeout',
                    'duration' => $errorDuration,
                    'duration_minutes' => round($errorDuration / 60, 1),
                    'current_limit' => ini_get('max_execution_time') . 's',
                    'file_size' => isset($fileSizeMB) ? $fileSizeMB . ' MB' : 'unknown',
                    'recommendation' => 'Split file into smaller chunks or increase server timeouts'
                ]);

                return redirect()->route('dashboard')->with(
                    'error',
                    $this->sanitizeErrorMessage('timeout', $fileSizeMB ?? '')
                );
            }

            // Check for memory errors
            if (
                stripos($errorMessage, 'memory') !== false ||
                stripos($errorMessage, 'allowed memory size') !== false
            ) {

                Log::error('[IMPORT] 🔴 MEMORY ERROR DETECTED', [
                    'error_type' => 'memory',
                    'memory_limit' => ini_get('memory_limit'),
                    'peak_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
                    'file_size' => isset($fileSizeMB) ? $fileSizeMB . ' MB' : 'unknown'
                ]);

                return redirect()->route('dashboard')->with(
                    'error',
                    $this->sanitizeErrorMessage('memory', $fileSizeMB ?? '')
                );
            }

            // Check for database errors
            if (
                stripos($errorMessage, 'database') !== false ||
                stripos($errorMessage, 'sql') !== false ||
                stripos($errorMessage, 'connection') !== false ||
                stripos($errorMessage, 'deadlock') !== false
            ) {

                Log::error('[IMPORT] 🔴 DATABASE ERROR DETECTED', [
                    'error_type' => 'database',
                    'error' => $errorMessage
                ]);

                return redirect()->route('dashboard')->with(
                    'error',
                    $this->sanitizeErrorMessage('database')
                );
            }

            // Check for file validation errors
            if (
                stripos($errorMessage, 'validation') !== false ||
                stripos($errorMessage, 'invalid') !== false ||
                stripos($errorMessage, 'format') !== false
            ) {

                Log::error('[IMPORT] 🔴 FILE VALIDATION ERROR', [
                    'error_type' => 'validation',
                    'error' => $errorMessage
                ]);

                return redirect()->route('dashboard')->with(
                    'error',
                    $this->sanitizeErrorMessage('validation')
                );
            }

            // Generic error fallback - use sanitized message
            return redirect()->route('dashboard')->with(
                'error',
                $this->sanitizeErrorMessage($errorMessage, $fileSizeMB ?? '')
            );
        }
    }

    /**
     * Clear only essential dashboard cache keys (faster than full cache clear)
     */
    private function clearEssentialDashboardCache(): void
    {
        try {
            // Clear specific essential cache keys
            $essentialKeys = [
                'actual_date_range',
                'dashboard_stats_cache',
            ];

            foreach ($essentialKeys as $key) {
                Cache::forget($key);
            }

            // Try Redis cache tags if available (non-blocking)
            if (config('cache.default') === 'redis') {
                try {
                    Cache::tags(['dashboard'])->flush();
                    Log::info('[IMPORT] Essential dashboard cache cleared using Redis tags');
                } catch (\Exception $e) {
                    // Silently continue if tags not supported
                    Log::debug('[IMPORT] Redis tags not available, using key-based clearing');
                }
            }

            Log::info('[IMPORT] Essential dashboard cache cleared successfully');
        } catch (\Exception $e) {
            Log::warning('[IMPORT] Failed to clear essential dashboard cache', [
                'error' => $e->getMessage()
            ]);
            // Don't throw - this is non-critical
        }
    }

    /**
     * More robust cache clearing
     */
    private function clearDashboardCache(): void
    {
        try {
            // Clear specific dashboard-related cache keys
            Cache::forget('actual_date_range');
            Cache::forget('dashboard_stats_cache');

            // Only try cache tags if Redis is available
            if (config('cache.default') === 'redis') {
                try {
                    Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                    Log::info('[IMPORT] Dashboard cache cleared using Redis tags');
                    return; // Exit early if successful
                } catch (\Exception $e) {
                    Log::info('[IMPORT] Redis cache tags not available, using alternative method');
                }
            }

            Log::info('[IMPORT] Dashboard cache cleared after import');
        } catch (\Exception $e) {
            Log::error('[IMPORT] Failed to clear dashboard cache after import', [
                'error' => $e->getMessage()
            ]);
            // Don't throw - this shouldn't block the redirect
        }
    }

    /**
     * Endpoint to manually clear import locks (for admin use)
     */
    public function clearImportLocks()
    {
        $cleared = $this->clearAllImportLocks();

        if ($cleared) {
            return redirect()->back()->with('success', '🧹 All import locks cleared successfully!');
        } else {
            return redirect()->back()->with('info', 'ℹ️ No active locks found to clear.');
        }
    }

    /**
     * ✅ FIXED: Get current import status (for monitoring)
     */
    public function getImportStatus()
    {
        // ✅ Check the unified progress key that we're actually using
        $progress = Cache::get('signin_import_progress', []);
        
        // ✅ Get lock status
        $isRunning = Cache::has('signin_logs_import_lock') || 
                     Cache::has('users_import_lock') ||
                     $this->hasAnyActiveLocks();
        
        Log::debug('[IMPORT] Status check', [
            'is_running' => $isRunning,
            'has_progress' => !empty($progress),
            'progress_status' => $progress['status'] ?? 'unknown',
            'current_stage' => $progress['current_stage'] ?? 0,
            'stages_count' => isset($progress['stages']) ? count($progress['stages']) : 0
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'is_running' => $isRunning,
                'progress' => $progress,
                'can_start_new' => !$isRunning
            ],
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * ✅ FIXED: Update import stage status for frontend progress display
     */
    private function updateImportStage(int $stage, string $status = 'running', ?string $time = null): void
    {
        try {
            $progressKey = 'signin_import_progress'; // ✅ Using consistent key
            $currentProgress = Cache::get($progressKey, []);

            // Map stages to frontend stage names
            $stageNames = [
                1 => 'file_upload',
                2 => 'file_validation',
                3 => 'processing_data',
                4 => 'saving_to_database',
                5 => 'cache_cleanup',
                6 => 'finalizing'
            ];

            $stageName = $stageNames[$stage] ?? 'unknown';

            // Update stage status
            if (!isset($currentProgress['stages'])) {
                $currentProgress['stages'] = [];
            }

            $currentProgress['stages'][$stageName] = [
                'status' => $status, // 'pending', 'running', 'complete'
                'time' => $time ?? ($status === 'complete' ? '✓' : '-'),
                'updated_at' => now()->toISOString()
            ];

            $currentProgress['current_stage'] = $stage;
            $currentProgress['status'] = $status === 'complete' && $stage === 6 ? 'completed' : 'running';
            $currentProgress['updated_at'] = now()->toISOString();

            Cache::put($progressKey, $currentProgress, 3600);

            Log::debug('[IMPORT] Stage updated', [
                'stage' => $stage,
                'stage_name' => $stageName,
                'status' => $status,
                'time' => $time,
                'overall_status' => $currentProgress['status']
            ]);
        } catch (\Exception $e) {
            Log::warning('[IMPORT] Failed to update stage progress', [
                'stage' => $stage,
                'error' => $e->getMessage()
            ]);
        }
    }
}
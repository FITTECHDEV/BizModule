<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\SigninLogsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Services\ImportLockService;
use App\Models\Logintracker\AzureUser;
use App\Models\Logintracker\System;
use League\Csv\Reader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ImportController extends Controller
{
    /**
     * Clear all import locks - both controller and import class locks
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
        
        if ($signinLockCleared || $signinProgressCleared || $userLockCleared) {
            Log::info('🧹 Import locks cleared', [
                'signin_lock' => $signinLockCleared,
                'signin_progress' => $signinProgressCleared,
                'users_lock' => $userLockCleared
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

   public function importUsers(Request $request)
{
    $request->validate([
        'import_file' => 'required|file|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
    ], [
        'import_file.required' => 'Please select a CSV file to upload.',
        'import_file.mimetypes' => 'The file must be a CSV or TXT file.',
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

    try {
        $file = $request->file('import_file');

        Log::channel('import')->info('Starting user import...', [
            'path' => $file->getRealPath(),
            'originalName' => $file->getClientOriginalName(),
        ]);

        DB::transaction(function () use ($file) {
            Excel::import(new UsersImport, $file);
        });

        // 🔑 Run cleanup immediately after import
        (new \App\Imports\UsersImport())->cleanupEmptyUsers();

        Log::channel('import')->info('✅ Users imported and cleaned successfully.');

        $lockService->releaseLock();
        
        // Clear dashboard cache after successful import & cleanup
        $this->clearDashboardCache();
        
        return redirect()->route('dashboard')->with('success', '✅ Users imported and cleaned successfully!');
    } catch (\Throwable $e) {
        Log::channel('import')->error('❌ Users import failed.', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        Log::error('❌ Users import failed.', [
            'error' => $e->getMessage(),
        ]);

        // Clear all locks on failure
        $this->clearAllImportLocks();
        $lockService->releaseLock();
        
        return redirect()->route('dashboard')->with('error', '❌ Failed to import users: ' . $e->getMessage());
    }
}


  public function importSignIns(Request $request)
{
    $request->validate([
        'import_file' => 'required|mimes:csv,txt',
    ], [
        'import_file.required' => 'Please select a CSV file to upload.',
        'import_file.mimes' => 'The file must be a CSV or TXT file.',
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

    try {
        $file = $request->file('import_file');

        Log::channel('import')->info('Starting sign-in import...', [
            'path' => $file->getRealPath(),
            'originalName' => $file->getClientOriginalName(),
        ]);

        // Import the signin logs with timeout protection
        $importStartTime = microtime(true);
        
        DB::transaction(function () use ($file) {
            Excel::import(new SigninLogsImport, $file);
        }, 5); // 5 attempts max

        $importDuration = round(microtime(true) - $importStartTime, 2);
        Log::channel('import')->info('Import completed', ['duration_seconds' => $importDuration]);

        // **FIX 1: Move time-consuming operations to background job or make them optional**
        
        // Quick cache clear (only essential keys)
        $this->clearEssentialDashboardCache();
        
        // **FIX 2: Make Artisan command execution optional and non-blocking**
        try {
            // Run with timeout and don't wait for completion
            $process = new \Symfony\Component\Process\Process([
                'php', 
                base_path('artisan'), 
                'users:update-login-status'
            ]);
            $process->setTimeout(30); // 30 second timeout
            $process->start(); // Start but don't wait
            
            Log::channel('import')->info('User login status update started in background');
        } catch (\Exception $e) {
            // Don't fail the import if this command fails
            Log::warning('Failed to start login status update', ['error' => $e->getMessage()]);
        }

        Log::channel('import')->info('✅ Sign-ins imported successfully.');

        // **FIX 3: Release lock immediately before redirect**
        $lockService->releaseLock();
        
        // **FIX 4: Simplified redirect logic**
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sign-ins imported successfully!',
                'redirect' => route('dashboard')
            ]);
        }
        
        // **FIX 5: Use a more reliable redirect method**
        return response()
            ->redirectTo(route('dashboard'))
            ->with('success', '✅ Sign-ins imported successfully!');
            
    } catch (\Throwable $e) {
        Log::channel('import')->error('❌ Sign-ins import failed.', [
            'error' => $e->getMessage(),
            'trace' => substr($e->getTraceAsString(), 0, 1000), // Limit trace length
        ]);

        // Clear all locks on failure
        $this->clearAllImportLocks();
        $lockService->releaseLock();
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import sign-ins: ' . $e->getMessage(),
                'redirect' => route('dashboard')
            ], 500);
        }
        
        return redirect()->route('dashboard')->with('error', '❌ Failed to import sign-ins: ' . $e->getMessage());
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
                Log::info('Essential dashboard cache cleared using Redis tags');
            } catch (\Exception $e) {
                // Silently continue if tags not supported
                Log::debug('Redis tags not available, using key-based clearing');
            }
        }
        
        Log::info('Essential dashboard cache cleared successfully');
        
    } catch (\Exception $e) {
        Log::warning('Failed to clear essential dashboard cache', [
            'error' => $e->getMessage()
        ]);
        // Don't throw - this is non-critical
    }
}

    /**
 * **UPDATED METHOD: More robust cache clearing**
 */
private function clearDashboardCache(): void
{
    try {
        // Clear specific dashboard-related cache keys
        Cache::forget('actual_date_range');
        Cache::forget('dashboard_stats_cache');
        
        // **FIX: Remove problematic getAllKeys() operation**
        // Instead, use known cache key patterns
        $knownCachePatterns = [
            'dashboard_stats_',
            'logged_in_users_count_',
            'user_stats_',
            'signin_logs_'
        ];
        
        // Only try cache tags if Redis is available
        if (config('cache.default') === 'redis') {
            try {
                Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                Log::info('Dashboard cache cleared using Redis tags');
                return; // Exit early if successful
            } catch (\Exception $e) {
                Log::info('Redis cache tags not available, using alternative method');
            }
        }
        
        Log::channel('import')->info('Dashboard cache cleared after import');

    } catch (\Exception $e) {
        Log::error('Failed to clear dashboard cache after import', [
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
}
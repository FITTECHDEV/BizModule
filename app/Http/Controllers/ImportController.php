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

            Log::channel('import')->info('✅ Users imported successfully.');

            $lockService->releaseLock();
            
            // Clear dashboard cache after successful import
            $this->clearDashboardCache();
            
            return redirect()->route('dashboard')->with('success', '✅ Users imported successfully!');
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

            // Import the signin logs
            DB::transaction(function () use ($file) {
                Excel::import(new SigninLogsImport, $file);
            });

            // Update user login status based on the newly imported sign-in data
            Log::channel('import')->info('Running user login status update command...');
            $exitCode = Artisan::call('users:update-login-status');
            
            if ($exitCode !== 0) {
                Log::warning('users:update-login-status command returned non-zero exit code', [
                    'exit_code' => $exitCode,
                    'output' => Artisan::output()
                ]);
            }
            
            Log::channel('import')->info('✅ Sign-ins imported successfully and user login status updated.');

            // Release lock before redirect
            $lockService->releaseLock();
            
            // Clear dashboard cache to ensure fresh data
            $this->clearDashboardCache();
            
            Log::channel('import')->info('About to redirect to dashboard', [
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'request_ajax' => $request->ajax(),
                'request_method' => $request->method(),
                'dashboard_route_exists' => \Route::has('dashboard')
            ]);
            
            // Check if user is still authenticated
            if (!auth()->check()) {
                Log::channel('import')->warning('User authentication lost during import, redirecting to login');
                return redirect()->route('login')->with('error', 'Session expired during import. Please log in again.');
            }
            
            // Handle AJAX requests differently
            if ($request->ajax()) {
                Log::channel('import')->info('AJAX request detected, returning JSON response instead of redirect');
                return response()->json([
                    'success' => true,
                    'message' => 'Sign-ins imported successfully and user login status updated!',
                    'redirect' => route('dashboard')
                ]);
            }
            
            // Regular redirect for non-AJAX requests
            $dashboardUrl = route('dashboard');
            Log::channel('import')->info('Performing redirect to dashboard', [
                'dashboard_url' => $dashboardUrl,
                'redirect_type' => 'regular'
            ]);
            
            return redirect()->to($dashboardUrl)->with('success', '✅ Sign-ins imported successfully and user login status updated!');
            
        } catch (\Throwable $e) {
            Log::channel('import')->error('❌ Sign-ins import failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Log::error('❌ Sign-ins import failed.', [
                'error' => $e->getMessage(),
            ]);

            // Clear all locks on failure
            $this->clearAllImportLocks();
            $lockService->releaseLock();
            
            return redirect()->route('dashboard')->with('error', '❌ Failed to import sign-ins: ' . $e->getMessage());
        }
    }

    /**
     * Clear dashboard cache after import
     */
    private function clearDashboardCache(): void
    {
        try {
            // Clear specific dashboard-related cache keys
            Cache::forget('actual_date_range');
            
            // Clear dashboard stats cache (pattern-based)
            $cacheKeys = Cache::getMemcached()->getAllKeys();
            if ($cacheKeys) {
                foreach ($cacheKeys as $key) {
                    if (str_contains($key, 'dashboard_stats_') || str_contains($key, 'logged_in_users_count_')) {
                        Cache::forget($key);
                    }
                }
            }
            
            // If using cache tags (Redis/Memcached)
            try {
                Cache::tags(['dashboard', 'user_stats', 'signin_logs'])->flush();
                Log::info('Dashboard cache cleared using tags');
            } catch (\Exception $e) {
                Log::info('Dashboard cache cleared individually (no tag support)');
            }

            Log::channel('import')->info('Dashboard cache cleared after import');

        } catch (\Exception $e) {
            Log::error('Failed to clear dashboard cache after import', [
                'error' => $e->getMessage()
            ]);
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
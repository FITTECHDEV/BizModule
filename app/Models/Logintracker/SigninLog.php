<?php

namespace App\Models\Logintracker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Model representing sign-in records for application users.
 */
class SigninLog extends Model
{
    use HasFactory;

    protected $table = 'Azure_LoginDetails';

    protected $fillable = [
        'date_utc',
        'request_id',
        'user_id',
        'user',
        'username',
        'user_type',
        'application',
        'application_id',
        'ip_address',
        'location',
        'status',
        'client_app',
        'device_id',
        'browser',
        'operating_system',
        'authentication_requirement',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date_utc' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model - auto-generate request_id and handle user_id
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            try {
                // Auto-generate request_id if not provided
                if (empty($model->request_id)) {
                    $model->request_id = 'REQ_' . Str::uuid();
                }

                // Handle user_id - must be a valid string, not null
                if (empty($model->user_id) && !empty($model->username)) {
                    $user = static::resolveUser($model->username, $model->user);
                    $model->user_id = $user ? $user->id : static::getOrCreateUnknownUserId($model->username);
                } elseif (empty($model->user_id)) {
                    // No username provided, use system unknown user
                    $model->user_id = static::getOrCreateUnknownUserId();
                }

                // Final validation - ensure user_id is never null
                if (is_null($model->user_id) || $model->user_id === '') {
                    Log::warning('SigninLog: user_id is null/empty, using fallback', [
                        'username' => $model->username,
                        'user' => $model->user,
                    ]);
                    $model->user_id = static::getSystemUnknownUserId();
                }
            } catch (\Exception $e) {
                Log::error('SigninLog boot error: ' . $e->getMessage(), [
                    'username' => $model->username ?? 'N/A',
                    'user' => $model->user ?? 'N/A',
                    'trace' => $e->getTraceAsString(),
                ]);

                // Absolute fallback: use system unknown user to prevent constraint violation
                $model->user_id = static::getSystemUnknownUserId();
            }
        });
    }

    /**
     * Resolve user by username with fallback handling
     */
    protected static function resolveUser($username, $displayName = null)
    {
        try {
            // Use the existing findByIdentifier method first
            $user = AzureUser::findByIdentifier($username);

            if ($user) {
                return $user;
            }

            // If not found, try to create using the existing findOrCreateUser method
            return AzureUser::findOrCreateUser($username, $displayName);
        } catch (\Exception $e) {
            Log::error('Failed to resolve user: ' . $e->getMessage(), [
                'username' => $username,
                'displayName' => $displayName,
            ]);
            return null;
        }
    }

    /**
     * Create a new user record
     */
    protected static function createNewUser($username, $displayName = null)
    {
        try {
            // Generate UUID for the user
            $userId = (string) Str::uuid();

            // Extract name parts from username/email
            $nameParts = static::extractNameFromUsername($username);

            $userData = [
                'id' => $userId,
                'userPrincipalName' => $username,
                'displayName' => $displayName ?? $nameParts['displayName'],
                'surname' => $nameParts['surname'],
                'givenName' => $nameParts['givenName'],
                'mail' => static::isValidEmail($username) ? $username : null,
                'userType' => 'Member',
                'accountEnabled' => true,
                'companyName' => 'Freight in Time',
                'identityIssuer' => 'System Generated',
                'createdDateTime' => now(),
            ];

            return AzureUser::create($userData);
        } catch (\Exception $e) {
            Log::error('Failed to create new user: ' . $e->getMessage(), [
                'username' => $username,
                'displayName' => $displayName,
            ]);
            return null;
        }
    }

    /**
     * Get or create unknown user ID - with caching to avoid repeated DB calls
     */
    protected static function getOrCreateUnknownUserId($username = null)
    {
        static $unknownUserId = null;

        if ($unknownUserId !== null) {
            return $unknownUserId;
        }

        try {
            // Look for existing unknown user
            $unknownUser = AzureUser::where('userPrincipalName', 'LIKE', 'unknown_%@freight-in-time.com')
                ->where('displayName', 'Unknown User')
                ->first();

            if ($unknownUser) {
                $unknownUserId = $unknownUser->id;
                return $unknownUserId;
            }

            // Create new unknown user
            $userId = (string) Str::uuid();
            $identifier = $username ? static::sanitizeUsername($username) : 'user_' . time();

            $unknownUser = AzureUser::create([
                'id' => $userId,
                'userPrincipalName' => 'unknown_' . $identifier . '@freight-in-time.com',
                'displayName' => 'Unknown User',
                'surname' => 'User',
                'givenName' => 'Unknown',
                'mail' => null,
                'userType' => 'Member',
                'accountEnabled' => false,
                'companyName' => 'Freight in Time',
                'identityIssuer' => 'System Generated',
                'createdDateTime' => now(),
            ]);

            $unknownUserId = $unknownUser->id;
            return $unknownUserId;
        } catch (\Exception $e) {
            Log::error('Failed to create/get unknown user: ' . $e->getMessage());
            return static::getSystemUnknownUserId();
        }
    }

    /**
     * Get system unknown user ID (hardcoded fallback)
     */
    protected static function getSystemUnknownUserId()
    {
        // You should create this user manually in your database
        // or return a known existing user ID as absolute fallback
        return 'SYSTEM_UNKNOWN_USER';
    }

    /**
     * Extract name components from username
     */
    protected static function extractNameFromUsername($username)
    {
        if (static::isValidEmail($username)) {
            $localPart = explode('@', $username)[0];
        } else {
            $localPart = $username;
        }

        // Remove common prefixes/suffixes and clean up
        $localPart = preg_replace('/^(mr|mrs|ms|dr|prof)\.?/i', '', $localPart);
        $localPart = preg_replace('/\.(admin|test|temp)$/i', '', $localPart);

        // Split by dots, underscores, or hyphens
        $nameParts = preg_split('/[._-]/', $localPart);
        $nameParts = array_map('ucfirst', array_filter($nameParts));

        if (count($nameParts) >= 2) {
            return [
                'givenName' => $nameParts[0],
                'surname' => end($nameParts),
                'displayName' => implode(' ', $nameParts),
            ];
        } else {
            $name = $nameParts[0] ?? 'Unknown';
            return [
                'givenName' => $name,
                'surname' => 'User',
                'displayName' => $name . ' User',
            ];
        }
    }

    /**
     * Sanitize username for use in email addresses
     */
    protected static function sanitizeUsername($username)
    {
        // Remove domain part if it's an email
        if (static::isValidEmail($username)) {
            $username = explode('@', $username)[0];
        }

        // Remove special characters and limit length
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $username);
        return substr($sanitized, 0, 20);
    }

    /**
     * Check if string is a valid email
     */
    protected static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(AzureUser::class, 'username', 'userPrincipalName');
    }

    /**
     * Query scopes
     */
    public function scopeLast30Days($query)
    {
        return $query->where('date_utc', '>=', now()->subDays(30));
    }

    public function scopeBetweenDateUtc($query, $start, $end)
    {
        return $query->whereBetween('date_utc', [Carbon::parse($start), Carbon::parse($end)]);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('date_utc', 'desc');
    }

    public function scopeDefaultSort($query)
    {
        return $query->orderBy('date_utc', 'desc');
    }

    public function scopeByApplication($query, $application)
    {
        return $query->where('application', $application);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'Success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'Failed');
    }

    public function scopeByUsername($query, $username)
    {
        return $query->where('username', $username);
    }

    public function scopeWithMfa($query)
    {
        return $query
            ->whereNotNull('multifactor_authentication_result')
            ->where('multifactor_authentication_result', '!=', 'None');
    }

    /**
     * Accessors
     */
    public function getDateUtcFormattedAttribute()
    {
        try {
            return $this->date_utc ? $this->date_utc->format('Y-m-d H:i:s') : 'N/A';
        } catch (\Exception $e) {
            return 'Invalid date';
        }
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    }

    public function getIpAddressFormattedAttribute()
    {
        return $this->ip_address ?: 'N/A';
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'Success' => 'Successful',
            'Failed' => 'Failed',
            'Interrupted' => 'Interrupted',
            default => ucfirst(strtolower($this->status ?? 'Unknown')),
        };
    }

    public function getLocationFormattedAttribute()
    {
        if (empty($this->location)) {
            return 'Unknown';
        }

        // Parse location string (e.g., "Mombasa, Coast, KE")
        $parts = array_map('trim', explode(',', $this->location));
        $filtered = array_filter($parts, fn($part) => !empty($part));

        return implode(', ', $filtered) ?: 'Unknown';
    }

    public function getBrowserFormattedAttribute()
    {
        if (empty($this->browser)) {
            return 'Unknown';
        }

        // Clean up browser string (e.g., "Firefox 140.0" -> "Firefox 140")
        return preg_replace('/\.0+$/', '', $this->browser);
    }

    /**
     * Helpers
     */
    public function isSuccessful()
    {
        return $this->status === 'Success';
    }

    public function isFailed()
    {
        return in_array($this->status, ['Failed', 'Failure']);
    }

    public function isInterrupted()
    {
        return $this->status === 'Interrupted';
    }

    public function usedMfa()
    {
        return !empty($this->multifactor_authentication_result) &&
            !in_array($this->multifactor_authentication_result, ['None', 'Not Required', '']);
    }

    public function hasValidUser()
    {
        return !empty($this->user_id) && $this->user()->exists();
    }

    public function isFromKnownLocation()
    {
        return !empty($this->location) && $this->location !== 'Unknown';
    }

    public function getSignInAge()
    {
        if (!$this->date_utc) {
            return null;
        }

        return $this->date_utc->diffForHumans();
    }

    /**
     * Static helpers
     */
    public static function getSystemList()
    {
        return static::select('system')
            ->distinct()
            ->whereNotNull('system')
            ->where('system', '!=', '')
            ->pluck('system')
            ->sort()
            ->values()
            ->toArray();
    }

    public static function getApplicationList()
    {
        return static::select('application')
            ->distinct()
            ->whereNotNull('application')
            ->where('application', '!=', '')
            ->pluck('application')
            ->sort()
            ->values()
            ->toArray();
    }

    public static function getStatusList()
    {
        return static::select('status')
            ->distinct()
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->pluck('status')
            ->sort()
            ->values()
            ->toArray();
    }

    public static function getBrowserList()
    {
        return static::select('browser')
            ->distinct()
            ->whereNotNull('browser')
            ->where('browser', '!=', '')
            ->pluck('browser')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Statistics helpers
     */
    public static function getSuccessRate($days = 30)
    {
        $total = static::last30Days()->count();
        if ($total === 0) {
            return 0;
        }

        $successful = static::last30Days()->successful()->count();
        return round(($successful / $total) * 100, 2);
    }

    public static function getMfaUsageRate($days = 30)
    {
        $total = static::last30Days()->successful()->count();
        if ($total === 0) {
            return 0;
        }

        $withMfa = static::last30Days()->successful()->withMfa()->count();
        return round(($withMfa / $total) * 100, 2);
    }

    /**
     * Batch import with comprehensive error handling
     */
    public static function batchImport(array $records, $batchSize = 100)
    {
        $successful = 0;
        $failed = 0;
        $errors = [];
        $batches = array_chunk($records, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            Log::info('Processing batch ' . ($batchIndex + 1) . ' of ' . count($batches));

            foreach ($batch as $index => $record) {
                try {
                    // Validate required fields
                    if (empty($record['username']) && empty($record['user_id'])) {
                        throw new \InvalidArgumentException('Either username or user_id is required');
                    }

                    static::create($record);
                    $successful++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'batch' => $batchIndex + 1,
                        'index' => $index,
                        'username' => $record['username'] ?? 'N/A',
                        'error' => $e->getMessage(),
                    ];

                    Log::error('SigninLog batch import failed for record', [
                        'batch' => $batchIndex + 1,
                        'index' => $index,
                        'record' => array_intersect_key(
                            $record,
                            array_flip(['username', 'user', 'status', 'application']),
                        ),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('SigninLog batch import completed', [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($records),
            'success_rate' => $successful > 0 ? round(($successful / count($records)) * 100, 2) : 0,
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($records),
            'errors' => $errors,
            'success_rate' => $successful > 0 ? round(($successful / count($records)) * 100, 2) : 0,
        ];
    }
}

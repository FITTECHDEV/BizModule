<?php

namespace App\Models\Logintracker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AzureUser extends Authenticatable
{
    use HasFactory;

    protected $table = 'Azure_Users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'userPrincipalName',
        'displayName',
        'surname',
        'mail',
        'givenName',
        'userType',
        'jobTitle',
        'department',
        'accountEnabled',
        'usageLocation',
        'streetAddress',
        'state',
        'country',
        'officeLocation',
        'city',
        'postalCode',
        'telephoneNumber',
        'mobilePhone',
        'alternateEmailAddress',
        'ageGroup',
        'consentProvidedForMinor',
        'legalAgeGroupClassification',
        'companyName',
        'creationType',
        'directorySynced',
        'invitationState',
        'identityIssuer',
        'createdDateTime',
    ];

    protected $casts = [
        'createdDateTime' => 'string', // column is varchar(16), not true datetime
        'accountEnabled' => 'boolean',
        'directorySynced' => 'boolean',
    ];

    // Mutators
    public function setDisplayNameAttribute($value): void
    {
        $maxLength = 23; // matches DB column length

        if (!empty($value)) {
            if (strlen($value) > $maxLength) {
                Log::warning('DisplayName truncated for user', [
                    'original' => $value,
                    'truncated' => substr($value, 0, $maxLength),
                ]);
                $this->attributes['displayName'] = substr($value, 0, $maxLength);
            } else {
                $this->attributes['displayName'] = $value;
            }
        } else {
            $this->attributes['displayName'] = null;
        }
    }

    // Helpers
    private static function sanitizeDisplayName(?string $displayName, ?string $userPrincipalName = null): ?string
    {
        if (empty($displayName)) {
            return null;
        }

        if (preg_match('/^[0-9a-f-]{36}$/i', $displayName)) {
            $sanitized = 'User-' . substr($displayName, 0, 8);

            if ($userPrincipalName && str_contains($userPrincipalName, '@')) {
                $username = explode('@', $userPrincipalName)[0];
                $sanitized = !empty($username) ? $username : $sanitized;
            }

            return $sanitized;
        }

        return substr($displayName, 0, 23); // match DB length
    }

    private static function validateUserData(array $userData): array
    {
        $errors = [];

        if (empty($userData['userPrincipalName'])) {
            $errors[] = 'userPrincipalName is required';
        } elseif (strlen($userData['userPrincipalName']) > 72) { // schema varchar(72)
            $errors[] = 'userPrincipalName too long (max 72 chars)';
        }

        if (!empty($userData['displayName']) && strlen($userData['displayName']) > 23) {
            $errors[] = 'displayName too long (max 23 chars)';
        }

        if (!empty($userData['id']) && strlen($userData['id']) > 36) {
            $errors[] = 'id too long (max 36 chars)';
        }

        return $errors;
    }

    // Relationships
    public function signIns()
    {
        return $this->hasMany(SigninLog::class, 'username', 'userPrincipalName');
    }

    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'application_user', 'user_id', 'application_id')
            ->withPivot('id');
    }

    // Domain logic
    public function isLoggedIn(int $days = 30): bool
    {
        return $this->signIns()
            ->where('date_utc', '>=', now()->subDays($days))
            ->exists();
    }

    public static function findByIdentifier(string $identifier): ?self
    {
        if (empty($identifier) || strlen($identifier) > 255) {
            Log::error('Invalid identifier length for Azure_Users', ['identifier' => $identifier]);
            return null;
        }

        $identifier = strtolower(trim($identifier));

        // Primary match
        $user = self::whereRaw('LOWER([userPrincipalName]) = ?', [$identifier])->first();
        if ($user) return $user;

        // Only use columns that actually exist in your table based on fillable array
        $fallbackFields = [
            'mail',
            'alternateEmailAddress',
        ];

        foreach ($fallbackFields as $field) {
            $user = self::whereRaw("LOWER($field) = ?", [$identifier])->first();
            if ($user) {
                Log::info('User resolved via fallback field', ['field' => $field, 'identifier' => $identifier]);
                return $user;
            }
        }

        // UUID match
        if (preg_match('/^[0-9a-f-]{36}$/i', $identifier)) {
            $user = self::find($identifier);
            if ($user) {
                Log::info('User resolved via UUID', ['identifier' => $identifier]);
                return $user;
            }
        }

        Log::warning('Failed to resolve user by any identifier', ['identifier' => $identifier]);
        return null;
    }


    /**
     * Fix duplicate display names by using email username
     * 
     * @param string|null $duplicateName The display name to fix (default: 'Marilign Anawte')
     * @return int Number of users updated
     */
    public static function fixDuplicateDisplayNames(?string $duplicateName = 'Marilign Anawte'): int
    {
        // Find all users with the duplicate display name
        $affectedUsers = \DB::table('Azure_Users')
            ->where('displayName', $duplicateName)
            ->whereNotNull('userPrincipalName')
            ->get();

        if ($affectedUsers->isEmpty()) {
            Log::info('No users found with duplicate display name', ['name' => $duplicateName]);
            return 0;
        }

        $updated = 0;

        foreach ($affectedUsers as $user) {
            // Extract username from email (part before @)
            if (strpos($user->userPrincipalName, '@') !== false) {
                $emailParts = explode('@', $user->userPrincipalName);
                $username = $emailParts[0];

                // Capitalize first letter: a.ali -> A.ali
                $newDisplayName = ucfirst($username);

                // Truncate to 23 chars (DB column limit)
                $newDisplayName = substr($newDisplayName, 0, 23);

                \DB::table('Azure_Users')
                    ->where('id', $user->id)
                    ->update(['displayName' => $newDisplayName]);

                $updated++;

                Log::info('Updated display name', [
                    'id' => $user->id,
                    'old' => $duplicateName,
                    'new' => $newDisplayName,
                    'email' => $user->userPrincipalName
                ]);
            }
        }

        Log::info('Fixed duplicate display names', [
            'duplicate_name' => $duplicateName,
            'updated' => $updated
        ]);

        return $updated;
    }

    public static function findOrCreateUser(string $userPrincipalName, ?string $displayName = null, ?string $userIdFromCsv = null): ?self
    {
        try {
            $sanitizedDisplayName = self::sanitizeDisplayName($displayName, $userPrincipalName);

            $user = self::where('userPrincipalName', $userPrincipalName)->first();

            if ($user) {
                $updated = false;

                if (!empty($sanitizedDisplayName) && $user->displayName !== $sanitizedDisplayName) {
                    $user->displayName = $sanitizedDisplayName;
                    $updated = true;
                }

                if (empty($user->id) && !empty($userIdFromCsv)) {
                    $user->id = $userIdFromCsv;
                    $updated = true;
                }

                if ($updated) {
                    $user->save();
                }

                return $user;
            }

            $userData = [
                'userPrincipalName' => $userPrincipalName,
                'displayName' => $sanitizedDisplayName,
                'id' => $userIdFromCsv ?? (string) Str::uuid(),
            ];

            $validationErrors = self::validateUserData($userData);
            if (!empty($validationErrors)) {
                Log::error('User data validation failed', [
                    'userPrincipalName' => $userPrincipalName,
                    'errors' => $validationErrors,
                ]);
                return null;
            }

            return self::updateOrCreate(
                ['userPrincipalName' => $userPrincipalName],
                $userData
            );
        } catch (\Exception $e) {
            Log::error('Failed to find or create user', [
                'userPrincipalName' => $userPrincipalName,
                'displayName' => $displayName,
                'userIdFromCsv' => $userIdFromCsv,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }


    public function testFindByIdentifierResolvesViaFallback()
    {
        $user = AzureUser::factory()->create([
            'mail' => 'fallback@example.com',
        ]);

        $resolved = AzureUser::findByIdentifier('fallback@example.com');
        $this->assertEquals($user->id, $resolved->id);
    }


    public function scopeValid($query)
    {
        return $query->whereNotNull('displayName')
            ->where('displayName', '!=', '')
            ->whereRaw("displayName NOT LIKE 'User-%'")
            ->whereNotNull('userPrincipalName')
            ->where('userPrincipalName', '!=', '')
            ->whereRaw("userPrincipalName NOT LIKE 'User-%'");
    }

    /**
     * Get comprehensive user activity summary with calendar month calculations
     * 
     * @return array
     */
    public static function getUserSummary(): array
    {
        try {
            // Get actual date range from imported data
            $statsService = app(\App\Services\DashboardStatsService::class);
            $actualRange = $statsService->getActualDateRange();

            if (!$actualRange['has_data']) {
                Log::warning('No signin data found in database');
                $totalUsers = self::valid()->count();
                return [
                    'total_users' => $totalUsers,
                    'active_this_month' => 0,
                    'active_last_month' => 0,
                    'inactive_users' => $totalUsers,
                    'never_logged_in' => $totalUsers,
                ];
            }

            $dataMin = $actualRange['min'];
            $dataMax = $actualRange['max'];

            // Current calendar month
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();

            // Previous calendar month
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();

            Log::info('📅 Calendar month ranges calculated', [
                'current_calendar_month' => [
                    $currentMonthStart->format('Y-m-d'),
                    $currentMonthEnd->format('Y-m-d')
                ],
                'previous_calendar_month' => [
                    $lastMonthStart->format('Y-m-d'),
                    $lastMonthEnd->format('Y-m-d')
                ],
                'data_available' => [
                    'from' => $dataMin->format('Y-m-d'),
                    'until' => $dataMax->format('Y-m-d')
                ],
            ]);

            // Total users
            $totalUsers = self::valid()->count();

            // Active this month (current calendar month)
            $activeThisMonth = \DB::select("
            SELECT COUNT(DISTINCT au.id) as count
            FROM Azure_Users au
            WHERE au.displayName IS NOT NULL 
                AND au.displayName != ''
                AND au.displayName NOT LIKE 'User-%'
                AND au.userPrincipalName IS NOT NULL
                AND au.userPrincipalName != ''
                AND au.userPrincipalName NOT LIKE 'User-%'
                AND EXISTS (
                    SELECT 1 
                    FROM Azure_LoginDetails ald 
                    WHERE LOWER(ald.username) = LOWER(au.userPrincipalName)
                        AND ald.date_utc BETWEEN ? AND ?
                )
        ", [$currentMonthStart, $currentMonthEnd])[0]->count ?? 0;

            // Active last month (previous calendar month)
            $activeLastMonth = \DB::select("
            SELECT COUNT(DISTINCT au.id) as count
            FROM Azure_Users au
            WHERE au.displayName IS NOT NULL 
                AND au.displayName != ''
                AND au.displayName NOT LIKE 'User-%'
                AND au.userPrincipalName IS NOT NULL
                AND au.userPrincipalName != ''
                AND au.userPrincipalName NOT LIKE 'User-%'
                AND EXISTS (
                    SELECT 1 
                    FROM Azure_LoginDetails ald 
                    WHERE LOWER(ald.username) = LOWER(au.userPrincipalName)
                        AND ald.date_utc BETWEEN ? AND ?
                )
        ", [$lastMonthStart, $lastMonthEnd])[0]->count ?? 0;

            // Inactive for 3+ months
            $threeMonthsAgo = now()->subMonths(3)->startOfDay();
            $inactiveThreeMonths = \DB::select("
            SELECT COUNT(*) as count
            FROM Azure_Users au
            WHERE au.displayName IS NOT NULL 
                AND au.displayName != ''
                AND au.displayName NOT LIKE 'User-%'
                AND au.userPrincipalName IS NOT NULL
                AND au.userPrincipalName != ''
                AND au.userPrincipalName NOT LIKE 'User-%'
                AND NOT EXISTS (
                    SELECT 1 
                    FROM Azure_LoginDetails ald 
                    WHERE LOWER(ald.username) = LOWER(au.userPrincipalName)
                        AND ald.date_utc >= ?
                )
        ", [$threeMonthsAgo])[0]->count ?? 0;

            // Never logged in
            $neverLoggedIn = \DB::select("
            SELECT COUNT(*) as count
            FROM Azure_Users au
            WHERE au.displayName IS NOT NULL 
                AND au.displayName != ''
                AND au.displayName NOT LIKE 'User-%'
                AND au.userPrincipalName IS NOT NULL
                AND au.userPrincipalName != ''
                AND au.userPrincipalName NOT LIKE 'User-%'
                AND NOT EXISTS (
                    SELECT 1 
                    FROM Azure_LoginDetails ald 
                    WHERE LOWER(ald.username) = LOWER(au.userPrincipalName)
                )
        ")[0]->count ?? 0;

            Log::info('✅ User summary calculated', [
                'total_users' => $totalUsers,
                'active_this_month' => $activeThisMonth,
                'active_last_month' => $activeLastMonth,
                'inactive_3_months' => $inactiveThreeMonths,
                'never_logged_in' => $neverLoggedIn,
                'current_month' => now()->format('F Y'),
            ]);

            return [
                'total_users' => $totalUsers,
                'active_this_month' => $activeThisMonth,
                'active_last_month' => $activeLastMonth,
                'inactive_users' => $inactiveThreeMonths,
                'never_logged_in' => $neverLoggedIn,
            ];
        } catch (\Exception $e) {
            Log::error('❌ Failed to get user summary', [
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);

            return [
                'total_users' => 0,
                'active_this_month' => 0,
                'active_last_month' => 0,
                'inactive_users' => 0,
                'never_logged_in' => 0,
            ];
        }
    }
}

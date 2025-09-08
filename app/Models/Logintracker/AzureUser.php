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
        return $this->hasMany(SigninLog::class, 'user_id', 'id');
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

}

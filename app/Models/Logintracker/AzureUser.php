<?php

namespace App\Models\Logintracker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\Logintracker\User
 *
 * Represents a user entity within the system, based on Azure AD or manual entry.
 * Handles user authentication and relationship to sign-in activity.
 */
class AzureUser extends Authenticatable
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Azure_Users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Disable timestamps (no created_at/updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable via create() or fill().
     *
     * @var array
     */
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'createdDateTime' => 'datetime',
        'accountEnabled' => 'boolean',
        'directorySynced' => 'boolean',
    ];

    /**
     * Attributes hidden from serialization (e.g., API responses).
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the sign-in records associated with this user.
     */
    public function signIns()
    {
        return $this->hasMany(SigninLog::class, 'user_id', 'id');
    }

    /**
     * Get interactive sign-in records associated with this user.
     */
    public function interactiveSignIns()
    {
        return $this->hasMany(SigninLog::class, 'user_id', 'id');
    }

    /**
     * Get sign-in logs associated with this user.
     */
    public function signinLogs()
    {
        return $this->hasMany(SigninLog::class, 'user_id', 'id');
    }

    /**
     * Get the systems associated with this user.
     */
    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'application_user', 'user_id', 'application_id')
                    ->withPivot('id');
    }

    /**
     * Use UUID instead of numeric id for route model binding.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Check if the user is considered logged in based on recent sign-in activity.
     *
     * @param int $days Number of days to consider for recent activity (default: 30)
     * @return bool
     */
    public function isLoggedIn($days = 30): bool
    {
        return $this->signIns()
            ->where('date_utc', '>=', now()->subDays($days))
            ->exists();
    }

    /**
     * Find a user by userPrincipalName or id.
     *
     * @param string $identifier
     * @return self|null
     */
    public static function findByIdentifier($identifier)
    {
        // Validate identifier
        if (empty($identifier) || strlen($identifier) > 255) {
            Log::error('Invalid identifier length for Azure_Users', ['identifier' => $identifier]);
            return null;
        }

        // Try userPrincipalName (validate as email)
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = self::where('userPrincipalName', $identifier)->first();
            if ($user) {
                return $user;
            }
        }

        // Try id if it's a GUID or string
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $identifier) || 
            preg_match('/^user-\d+$/', $identifier)) {
            $user = self::find($identifier);
            if ($user) {
                return $user;
            }
        }

        Log::warning('Failed to find user by identifier in Azure_Users', ['identifier' => $identifier]);
        return null;
    }
}
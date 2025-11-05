<?php

namespace App\Models\Logintracker;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class System extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Azure_SoftwareMaster';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // CHANGED: Disabled timestamps since columns were removed from DB

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
    public $incrementing = true;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name']; // CHANGED: Removed created_at and updated_at

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    // REMOVED: Timestamp casts since columns don't exist

    /**
     * REMOVED: Default systems array - no longer needed as systems are populated from database
     */

    /**
     * Get the users associated with this system through the pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            AzureUser::class,
            'application_user',
            'application_id',
            'user_id'
        )->withPivot('id')
         ->withTimestamps();
    }

    /**
     * Get all signin logs for this system.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function signinLogs(): HasMany
    {
        return $this->hasMany(SigninLog::class, 'system', 'name');
    }

    /**
     * Get all systems, auto-populating if empty (with caching).
     *
     * @param bool $forceRefresh Force cache refresh
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllOrPopulate(bool $forceRefresh = false): Collection
    {
        $cacheKey = 'systems_list';
        $cacheDuration = 3600; // 1 hour

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $cacheDuration, function () {
            $systems = static::all();

            if ($systems->isEmpty()) {
                Log::info('No systems found in Azure_SoftwareMaster, attempting auto-populate');

                try {
                    static::populateDefaultSystems();
                    $systems = static::all();

                    Log::info('Systems auto-populated successfully', [
                        'count' => $systems->count(),
                        'systems' => $systems->pluck('name')->toArray()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to auto-populate systems', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // CHANGED: Return empty collection instead of fallback with hardcoded systems
                    Log::warning('Returning empty systems collection - database population failed');
                    return collect();
                }
            }

            return $systems;
        });
    }

    /**
     * Normalize system name using systemmap config
     * 
     * @param string $systemName
     * @param array|null $systemMap
     * @return string
     */
    private static function normalizeSystemName($systemName, $systemMap = null)
    {
        if (empty($systemName)) {
            return 'Unknown';
        }
        
        $normalized = trim($systemName);
        
        if (empty($systemMap)) {
            $systemMap = config('systemmap', []);
        }
        
        if (empty($systemMap)) {
            return $normalized;
        }
        
        $lowerNormalized = strtolower($normalized);
        
        // Case-insensitive lookup
        foreach ($systemMap as $rawName => $cleanName) {
            if (strtolower($rawName) === $lowerNormalized) {
                return $cleanName;
            }
        }
        
        return $normalized;
    }

    /**
     * Populate the database with systems from Azure_LoginDetails.
     *
     * @return int Number of systems created
     * @throws \Exception
     */

public static function populateDefaultSystems(): int
{
    $systemMap = config('systemmap', []);
    
    // Get unique systems from Azure_LoginDetails
    $rawSystems = \DB::table('Azure_LoginDetails')
        ->selectRaw('DISTINCT TRIM(system) as clean_system')
        ->whereNotNull('system')
        ->where('system', '!=', '')
        ->get();
    
    $created = 0;
    $normalizedSystems = collect();
    
    foreach ($rawSystems as $item) {
        $normalized = self::normalizeSystemName($item->clean_system, $systemMap);
        $normalizedSystems->push($normalized);
    }
    
    // Insert unique normalized systems
    foreach ($normalizedSystems->unique() as $systemName) {
        // Check if exists (case-insensitive)
        $existing = \DB::table('Azure_SoftwareMaster')
            ->whereRaw('LOWER(name) = ?', [strtolower($systemName)])
            ->first();
        
        if (!$existing) {
            // ✅ CRITICAL FIX: Generate UUID instead of integer
            $newId = (string) \Illuminate\Support\Str::uuid();
            
            \DB::table('Azure_SoftwareMaster')->insert([
                'Id' => $newId,  // ✅ Now using UUID
                'name' => $systemName
            ]);
            $created++;
            
            \Log::info('Created new system', [
                'system_name' => $systemName,
                'system_id' => $newId
            ]);
        }
    }
    
    // Clear cache after populating
    Cache::forget('systems_list');
    
    Log::info('Populated Azure_SoftwareMaster from Azure_LoginDetails', [
        'created' => $created,
        'systems' => $normalizedSystems->unique()->toArray()
    ]);
    
    return $created;
}

    /**
     * REMOVED: createFallbackCollection() method
     * No longer needed as we return empty collection on failure
     */

    /**
     * Get system by name.
     *
     * @param string $name
     * @return static|null
     */
    public static function findByName(string $name): ?static
    {
        return static::getAllOrPopulate()->firstWhere('name', $name);
    }

    /**
     * Get system names as array.
     *
     * @return array
     */
    public static function getNames(): array
    {
        return static::getAllOrPopulate()->pluck('name')->toArray();
    }

    /**
     * Check if system exists by name.
     *
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return static::getAllOrPopulate()->contains('name', $name);
    }

    /**
     * Refresh systems cache.
     *
     * @return void
     */
    public static function refreshCache(): void
    {
        Cache::forget('systems_list');
        static::getAllOrPopulate(true);

        Log::info('Systems cache refreshed');
    }

    /**
     * Get active systems (excluding UAT/test systems).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveOnly(): Collection
    {
        return static::getAllOrPopulate()->filter(function ($system) {
            return !str_contains(strtolower($system->name), 'uat');
        });
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when a system is created, updated, or deleted
        static::created(function () {
            Cache::forget('systems_list');
        });

        static::updated(function () {
            Cache::forget('systems_list');
        });

        static::deleted(function () {
            Cache::forget('systems_list');
        });
    }
}
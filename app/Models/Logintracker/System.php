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
    public $timestamps = true;

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
    protected $fillable = ['name', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Default systems to populate if table is empty.
     *
     * @var array
     */
    protected static $defaultSystems = [
        'SCM',
        'Odoo',
        'OPS',
        'D365 Live',
        'Fit Express',
        'FIT ERP',
        'Fit Express UAT',
        'FITerp UAT',
        'OPS UAT'
    ];

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

                    // Fallback: Create in-memory collection
                    $systems = static::createFallbackCollection();

                    Log::warning('Using fallback in-memory systems collection', [
                        'count' => $systems->count()
                    ]);
                }
            }

            return $systems;
        });
    }

    /**
     * Populate the database with default systems.
     *
     * @return int Number of systems created
     * @throws \Exception
     */
    public static function populateDefaultSystems(): int
    {
        $created = 0;

        foreach (static::$defaultSystems as $systemName) {
            $system = static::firstOrCreate(
                ['name' => $systemName],
                [
                    'name' => $systemName,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($system->wasRecentlyCreated) {
                $created++;
            }
        }

        // Clear cache after populating
        Cache::forget('systems_list');

        return $created;
    }

    /**
     * Create fallback in-memory collection when database is unavailable.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected static function createFallbackCollection(): Collection
    {
        return collect(static::$defaultSystems)->map(function ($name) {
            $system = new static();
            $system->name = $name;
            $system->exists = false; // Mark as not persisted
            return $system;
        });
    }

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
<?php
namespace App\Models\Logintracker;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * Get the users associated with this system through the pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            AzureUser::class,
            'application_user',     // Pivot table name (matches SigninLogsImport)
            'application_id',       // Foreign key for this model in pivot table
            'user_id'              // Foreign key for related model in pivot table
        )->withPivot('id')
         ->withTimestamps();
    }

    /**
     * Get all signin logs for this system.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function signinLogs()
    {
        return $this->hasMany(SigninLog::class, 'system', 'name');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AzureProcedure extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
    protected $table = 'procedures';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'step',
        'description',
        'data',
        'started_at',
        'completed_at',
        'error_message'
    ];

    protected $casts = [
        'data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user this procedure belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AzureUser::class, 'user_id');
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
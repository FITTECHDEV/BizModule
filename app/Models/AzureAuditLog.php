<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AzureAuditLog extends Model
{
    use HasFactory;

    /**
     * The database connection to use
     */
    // protected $connection = 'azure_management';

    /**
     * The table associated with the model.
     */
    protected $table = 'audit_logs';

    // Disable updated_at since audit logs never update
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'performed_by',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user this action was performed on
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AzureUser::class, 'user_id');
    }

    /**
     * Get the user who performed this action
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(AzureUser::class, 'performed_by');
    }

    /**
     * Get the auditable entity (polymorphic)
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Only successful actions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Only failed actions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Filter by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by performer
     */
    public function scopeByPerformer($query, int $userId)
    {
        return $query->where('performed_by', $userId);
    }

    /**
     * Check if this is a critical audit log
     */
    public function isCritical(): bool
    {
        $criticalActions = [
            'azure.rollback.failed',
            'user.provisioning.failed',
            'azure.user.creation.failed',
        ];

        return in_array($this->action, $criticalActions) || 
                ($this->metadata['severity'] ?? null) === 'CRITICAL';
    }

    /**
     * Get human-readable action name
     */
    public function getActionNameAttribute(): string
    {
        return ucwords(str_replace(['.', '_'], ' ', $this->action));
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'partial' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get icon for action type
     */
    public function getActionIconAttribute(): string
    {
        if (str_contains($this->action, 'created')) return '➕';
        if (str_contains($this->action, 'updated')) return '✏️';
        if (str_contains($this->action, 'deleted')) return '🗑️';
        if (str_contains($this->action, 'disabled')) return '🚫';
        if (str_contains($this->action, 'enabled')) return '✅';
        if (str_contains($this->action, 'failed')) return '❌';
        if (str_contains($this->action, 'azure')) return '☁️';
        if (str_contains($this->action, 'module')) return '📦';
        
        return '📝';
    }
}
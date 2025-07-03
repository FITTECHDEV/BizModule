<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ClientPartyAttahment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'ID';
    protected $table = 'Cust_EntityAttachment';

    // Custom timestamp and soft delete columns
    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

    // Since you're using GUID for primary key
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     * THIS IS THE KEY FIX - Add all fields you want to mass assign
     */
    protected $fillable = [
        'ID',
        'CustEntityID',
        'RequestNo',
        'Title',
        'FileName',
        'FilePath',
        'CreatedBy',
        'CreatedOn',
        'LastModifiedBy',
        'LastModifiedOn',
        'DeletedOn',
        'DeletedBy',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'CreatedOn' => 'datetime',
        'LastModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    /**
     * Relationship with the main client party entity
     */
    public function clientParty()
    {
        return $this->belongsTo(ClientParty::class, 'CustEntityID', 'Id');
    }

    /**
     * Relationship with the user who created the attachment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'ID');
    }

    /**
     * Relationship with the user who last modified the attachment
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'LastModifiedBy', 'ID');
    }

    /**
     * Scope to get attachments for a specific entity
     */
    public function scopeForEntity($query, $entityId)
    {
        return $query->where('CustEntityID', $entityId);
    }

    /**
     * Scope to get attachments by request number
     */
    public function scopeByRequestNo($query, $requestNo)
    {
        return $query->where('RequestNo', $requestNo);
    }

    /**
     * Get the full file path URL from Azure storage
     */
    public function getFileUrlAttribute()
    {
        try {
            return Storage::disk('azure')->url($this->FilePath);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if file exists in Azure storage
     */
    public function fileExists()
    {
        try {
            return Storage::disk('azure')->exists($this->FilePath);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeAttribute()
    {
        try {
            $bytes = Storage::disk('azure')->size($this->FilePath);
            $units = ['B', 'KB', 'MB', 'GB'];

            for ($i = 0; $bytes > 1024; $i++) {
                $bytes /= 1024;
            }

            return round($bytes, 2) . ' ' . $units[$i];
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->FileName, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is PDF
     */
    public function isPdf()
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set the ID when creating
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });

        // Clean up file when attachment is deleted
        static::deleting(function ($model) {
            try {
                if ($model->FilePath && Storage::disk('azure')->exists($model->FilePath)) {
                    Storage::disk('azure')->delete($model->FilePath);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete file during attachment cleanup: ' . $e->getMessage(), [
                    'attachment_id' => $model->ID,
                    'file_path' => $model->FilePath
                ]);
            }
        });
    }
}

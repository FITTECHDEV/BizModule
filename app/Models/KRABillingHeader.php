<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KRABillingHeader extends Model
{
    use HasFactory;

    protected $table = 'KRA_BillingHeader';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $guarded = [];

    /**
     * Get the details for the billing header.
     */
    public function details()
    {
        return $this->hasMany(KRABillingDetail::class, 'DocumentHeaderId', 'ID');
    }

    /**
     * Count invoices based on a column and value with optional constraints.
     *
     * @param  string  $column
     * @param  mixed   $value
     * @param  array|null  $constraints
     * @return string
     */
    public function invCredCount(string $column = null, $value = null, array $constraints = null)
    {
        if ($column === null || $value === null) {
            return '0';
        }

        $query = $value === '*' ? KRABillingHeader::query() : KRABillingHeader::where($column, $value);

        if ($constraints) {
            foreach ($constraints as $constraint) {
                $query->where($constraint[0], $constraint[1]);
            }
        }

        $auth_user = Auth::user();
        if ($auth_user->Role !== 1) {
            $query->where('CreatedBy', $auth_user->full_name);
        }

        return number_format($query->count(), 0, '', ',');
    }

    /**
     * Get TIMS stats.
     *
     * @return array
     */
    public function timsStats()
    {
        return DB::select(DB::raw("
            SELECT DocStatus as StatusName, COUNT(DocStatus) as StatusCount
            FROM KRA_BillingHeader
            GROUP BY DocStatus
            ORDER BY StatusCount DESC
        "));
    }

    /**
     * Scope a query to only include pending invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('DocStatus', 'Pending');
    }
}

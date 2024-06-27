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

    // protected $guarded = [];
    protected $fillable = [
        'Docstatus',
        'KRAInvoiceNo',
        "KRASerialNo",
        "KRAQRCode",
        "SubmissionStatus"
    ];

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public function details()
    {
        return $this->hasMany(KRABillingDetail::class, 'DocumentHeaderId', 'ID');
    }

    public function invCredCount(string $column = null, $value = null, array $constraints = null)
    {
        $count = 0;
        $auth_user = Auth::user();

        if ($column == null || $value == null) {
            return $count;
        }

        if ($value == '*') {
            $count = KRABillingHeader::count();
        } else {
            $count = KRABillingHeader::where($column, $value)->count();
        }

        if (isset($constraints) && !empty($constraints)) {
            if ($value == '*') {
                $kraBillingHeader = new KRABillingHeader();
            } else {
                $kraBillingHeader = KRABillingHeader::where($column, $value);
            }
            foreach ($constraints as $key => $constraint) {
                $kraBillingHeader = $kraBillingHeader->where($constraint[0], $constraint[1]);
            }
            if ($auth_user->Role != 1) {
                $kraBillingHeader = $kraBillingHeader->where('CreatedBy', $auth_user->full_name);
            }
            $count = $kraBillingHeader->count();
        }

        return number_format($count, 0, '', ',');
    }

    public function timsStats()
    {

        return DB::select(DB::raw("select DocStatus as StatusName,
            COUNT(DocStatus) as StatusCount
            from
            KRA_BillingHeader
            group by DocStatus
            order by StatusCount desc"));
    }

    public function scopeNoLock($query)
    {
        return $query->from(DB::raw(self::getTable() . ' with (nolock)'));
    }
}

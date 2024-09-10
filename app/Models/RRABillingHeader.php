<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class RRABillingHeader extends Model
{
    use HasFactory;

    protected $table = 'RRA_BillingHeader';

    // protected $guarded = [];
    protected $fillable = [
        'Docstatus',
        'RRAInvoiceNo',
        'RRASerialNo',
        'RRAQRCode',
        'RRAInternalData',
        'RRASignature',
        'RRAScuReceiptDate',
        'RRAScuReceiptNo',
        'SubmissionStatus'
    ];

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public function details()
    {
        return $this->hasMany(RRABillingDetail::class, 'DocumentHeaderId', 'ID');
    }

    public function invCredCount(string $column = null, $value = null, array $constraints = null)
    {
        $count = 0;
        $auth_user = Auth::user();

        if ($column == null || $value == null) {
            return $count;
        }

        if ($value == '*') {
            $count = RRABillingHeader::count();
        } else {
            $count = RRABillingHeader::where($column, $value)->count();
        }

        if (isset($constraints) && !empty($constraints)) {
            if ($value == '*') {
                $rraBillingHeader = new RRABillingHeader();
            } else {
                $rraBillingHeader = RRABillingHeader::where($column, $value);
            }
            foreach ($constraints as $key => $constraint) {
                $rraBillingHeader = $rraBillingHeader->where($constraint[0], $constraint[1]);
            }
            if ($auth_user->Role != 1) {
                $rraBillingHeader = $rraBillingHeader->where('CreatedBy', $auth_user->full_name);
            }
            $count = $rraBillingHeader->count();
        }

        return number_format($count, 0, '', ',');
    }

    public function rraStats()
    {

        return DB::select(DB::raw("select DocStatus as StatusName,
            COUNT(DocStatus) as StatusCount
            from
            RRA_BillingHeader
            group by DocStatus
            order by StatusCount desc"));
    }

    public function scopeNoLock($query)
    {
        return $query->from(DB::raw(self::getTable() . ' with (nolock)'));
    }
}

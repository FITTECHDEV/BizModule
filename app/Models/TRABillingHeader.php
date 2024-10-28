<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TRABillingHeader extends Model
{
    use HasFactory;

    protected $table = 'TRA_BillingHeader';

    // protected $guarded = [];
    protected $fillable = [
        'Docstatus',
        'TRAInvoiceNo',
        'TRASerialNo',
        'TRAQRCode',
        'TRAInternalData',
        'TRASignature',
        'TRAScuReceiptDate',
        'TRAScuReceiptNo',
        'SubmissionStatus'
    ];

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public function details()
    {
        return $this->hasMany(TRABillingDetail::class, 'DocumentHeaderId', 'ID');
    }

    public function invCredCount(string $column = null, $value = null, array $constraints = null, $officeCode = null)
    {
        $count = 0;
        $auth_user = Auth::user();

        if ($column == null || $value == null) {
            return $count;
        }

        $query = TRABillingHeader::query();

        if ($value != '*') {
            $query->where($column, $value);
        }

        if ($officeCode) {
            $query->where('OfficeCode', $officeCode);
        }

        if (isset($constraints) && !empty($constraints)) {
            foreach ($constraints as $key => $constraint) {
                $query->where($constraint[0], $constraint[1]);
            }
        }

        if ($auth_user->Role != 1) {
            $query->where('CreatedBy', $auth_user->full_name);
        }

        $count = $query->count();

        return number_format($count, 0, '', ',');
    }


    public function traStats()
    {

        return DB::select(DB::raw("select DocStatus as StatusName,
            COUNT(DocStatus) as StatusCount
            from
            TRA_BillingHeader
            group by DocStatus
            order by StatusCount desc"));
    }

    public function scopeNoLock($query)
    {
        return $query->from(DB::raw(self::getTable() . ' with (nolock)'));
    }

    public function scopeFilterByOfficeCode($query, array $officeCodes)
    {
        return $query->whereIn('OfficeCode', $officeCodes);
    }

    public static function getStatsByOfficeCode(array $officeCodes)
    {
        return DB::select(DB::raw("select DocStatus as StatusName,
            COUNT(DocStatus) as StatusCount
            from
            TRA_BillingHeader
            where OfficeCode in ('" . implode("','", $officeCodes) . "')
            group by DocStatus
            order by StatusCount desc"));
    }
}

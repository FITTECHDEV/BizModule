<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Guid\Guid;
use RealRashid\SweetAlert\Facades\Alert;


class ClientParty extends Model
{
    use HasFactory;

    protected $table = 'Cust_Entity';

    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

    public function generateUniqueCode($id)
    {
        // dd($id);
        // if id is empty use kenya get: KE
        if ($id == null) {
            $office = Office::select('Code')->where('Name', 'Kenya')->first();
        } else {
            $office = Office::select('Code')->where('ID', $id)->first();
        }

        if (empty($office)) {
            alert()->error('Missing Office Details');
            abort(404, "Missing Office Details");
            return redirect()->route('home');
        }
        // Get the maximum number
        // $max_num = DB::table('Cust_Entity')
        //     ->select(DB::raw('Isnull(Max(cast(right(Code,5) as numeric(18,0))),0) +1 AS max_num'))
        //     ->whereRaw('Len(Code) = 9')
        //     ->pluck('max_num');
        $max_num = DB::table('Cust_Entity')
            ->select(DB::raw('ISNULL(MAX(CAST(RIGHT(Code, 5) AS NUMERIC(18,0))), 0) + 1 AS max_num'))
            ->whereRaw('LEN(Code) = 9')
            ->value('max_num'); // Returns direct value instead of collection


        // get year : 22
        $year = date('y');

        //$ticket_number = str_pad($max_num[0], 5, '0', STR_PAD_LEFT);
        $ticket_number = str_pad($max_num, 5, '0', STR_PAD_LEFT);

        //CODE
        $code = str_replace(' ', '', trim($office->Code . '' . $year . '' . $ticket_number, ""));
        return $code;
    }

    //     public function generateUniqueCode($id)
// {
//     // If ID is null, use Kenya's office code
//     if ($id == null) {
//         $office = Office::select('Code')->where('Name', 'Kenya')->first();
//     } else {
//         $office = Office::select('Code')->where('ID', $id)->first();
//     }

    //     // Check if office details are missing
//     if (empty($office)) {
//         alert()->error('Missing Office Details');
//         abort(404, "Missing Office Details");
//     }

    //     // Get the maximum number (ensuring no records case)
//     $max_num = DB::table('Cust_Entity')
//         ->select(DB::raw('ISNULL(MAX(CAST(RIGHT(Code, 5) AS NUMERIC(18,0))), 0) + 1 AS max_num'))
//         ->whereRaw('LEN(Code) = 9')
//         ->pluck('max_num');

    //     // Check if a valid max number is returned
//     $max_num = $max_num[0] ?? 0; // Ensure fallback to 0 if no records are found

    //     // Get the current year (last two digits)
//     $year = date('y');

    //     // Pad the max number to ensure it's 5 digits
//     $ticket_number = str_pad($max_num, 5, '0', STR_PAD_LEFT);

    //     // Generate the final code by concatenating office code, year, and ticket number
//     $code = $office->code . $year . $ticket_number;

    //     return $code;
// }


    public function getSCMOfficeID($id)
    {
        return DB::select(DB::raw("select SCMOfficeID
            from Cust_Office where ID = '$id'"));
    }
    public function increment_code($string)
    {
        return preg_replace_callback('/^([^0-9]*)([0-9]+)([^0-9]*)$/', function ($m) {
            return $m[1] . str_pad($m[2] + 1, strlen($m[2]), '0', STR_PAD_LEFT) . $m[3];
        }, $string);
    }

    public function paintClientPartyStatus(string $var = null)
    {
        switch ($var) {
            case 'Created':
                return 'success';
                break;
            case 'Submitted':
                return 'warning';
                break;
            case 'Not Submitted':
                return 'info';
                break;
            case 'Rejected':
                return 'danger';
                break;
            case 'Pending':
                return 'primary';
                break;
            default:
                return 'default';
                break;
        }
    }

    protected $fillable = [
        'Name',
        'Address_Line1',
        'Address_Line2',
        'Address_City',
        'Address_State',
        'Address_StateId',
        'StateCode',
        'Address_CountryId',
        'CountryCode',
        'Address_Pin',
        'Cont_Bch_Dept',
        'Cont_Bch_PersonName_FirstName',
        'Cont_Bch_PersonName_LastName',
        'Cont_Bch_PhoneNumbers',
        'Cont_Bch_Email',
        'CreatedBy',
        'CreatedOn',
        'LastModifiedBy',
        'LastModifiedOn',
        'IsActive',
        'IsDeleted',
        'Office',
        'SalesMgrOfficeFunMapName',
        'SalesMgrOfficeFunMapId',
        'AccountMgrOfficeFunMapName',
        'AccountMgrOfficeFunMapId',
        'PaymentTermsID',
        'PaymentTermsName',
        'IncoTermID',
        'IncoTermName',
        'DefaultNotifyPartyId',
        'DefaultNotifyPartyName',
        'DefaultNotifyParty1Id',
        'DefaultNotifyParty1Name',
        'DefaultFowarderName',
        'DefaultFowarderId',
        'AirUsualPortOfLoading',
        'AirUsualPortOfLoadingName',
        'AirUsualPortofDischarge',
        'AirUsualPortofDischargeName',
        'SeaUsualPortOfLoading',
        'SeaUsualPortOfLoadingName',
        'SeaUsualPortofDischarge',
        'SeaUsualPortofDischargeName',
        'DefaultPartyLocationId',
        'CreditDays',
        'CreditLimit',
        'CreditLimitCurrencyId',
        'CreditLimitCurrencyCode',
        'BillingCurrencyId',
        'BillingCurrencyCode',
        'LicenceTypeID',
        'LicenceTypeCode',
        'LicenceTypeValue',
        'DebtorLicenceTypeID',
        'DebtorLicenceTypeCode',
        'DebtorLicenceTypeValue',
        'VendorLicenceTypeID',
        'VendorLicenceTypeCode',
        'VendorLicenceTypeValue',
        'TariffValue',
        'BillingType',
        'FSCType',
        'FSCRate',
        'Discount',
        'RequestforClient',
        'RequestforParty',
        'RequestforDebtor',
        'RequestforVendor',
        'ClientID',
        'PartyID',
        'DebtorID',
        'DebtorCode',
        'DebtorType',
        'VendorID',
        'VendorCode',
        'CreationRequestBy',
        'CreationRequestOn',
        'RejectedBy',
        'RejectedOn',
        'RejectedReason',
        'OperatioEntityType',
        'LegalEntityType',
        'SpecialInstructions',
        'Status',
        'RejectedBy',
        'RejectedOn',
        'RejectedReason',
    ];

    public function entityStats()
    {
        $auth_user = Auth::user();
        if ($auth_user->Role == 1) {
            return DB::select(DB::raw("select status as StatusName,
            COUNT(status) as StatusCount
            from
            Cust_Entity
            group by Status
            order by StatusCount desc"));

        } else {
            return DB::select(DB::raw("select CE.Status as StatusName,
            COUNT(status) as StatusCount
            from
            Cust_Entity CE
            where CE.CreatedBy ='" . $auth_user->full_name . "'
            group by Status
            order by StatusCount desc"));
        }
    }

    public function clientPartyCount(string $column = null, $value = null, array $constraints = null)
    {
        $count = 0;
        $auth_user = Auth::user();

        if ($column == null || $value == null) {
            return $count;
        }

        if ($value == '*') {
            if ($auth_user->Role != 1) {
                $count = $this->where('CreatedBy', $auth_user->full_name)->count();
            } else {
                $count = $this->count();
            }
        } else {
            if ($auth_user->Role != 1) {
                $count = $this->where('CreatedBy', $auth_user->full_name)
                    ->where($column, (string) $value)->count();
            } else {
                $count = $this->where($column, (string) $value)->count();
            }
        }

        if (isset($constraints) && !empty($constraints)) {
            if ($value == '*') {
                $entity = new ClientParty();
            } else {
                $entity = $this->where($column, (string) $value);
            }
            foreach ($constraints as $key => $constraint) {

                $entity = $entity->where($constraint[0], $constraint[1]);
            }

            if ($auth_user->Role != 1) {
                $entity->where('CreatedBy', $auth_user->full_name);
            }
            $count = $entity->count();
        }

        return number_format($count, 0, '', ',');
    }

    public function attachments()
    {
        return $this->hasMany(ClientPartyAttahment::class, 'CustEntityID', 'Id');
    }
}

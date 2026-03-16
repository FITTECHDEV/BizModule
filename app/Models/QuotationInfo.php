<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationInfo extends Model
{
    protected $table = 'Quote_QuotationInfo';

    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = [
        'createdon' => 'datetime',
        'updatedon' => 'datetime',
    ];

    protected $fillable = [
        'bookingno',
        'QuoteNo',
        'companyname',
        'clientid',
        'source',
        'status',
        'userid',
        'username',
        'useremail',
        'SalesPersonEmail',
        'SalesPersonCode',
        'teamid',
        'teamname',
        'origincountry',
        'destinationcountry',
        'process',
        'incoterm',
        'shipmentmode',
        'TransportMode',
        'ServiceClass',
        'commodity',
        'scopevolume',
        'cargovolume',
        'containerlist',
        'CustomerType',
        'AgentType',
        'AgentTypeId',
        'OfficeCode',
        'QuotationTCTemplate',
        'QuotationEmailTemplate',
        'contact_name',
        'pri_partnermaster_name',
        'pri_partner_industrytype',
        'pri_partner_steet',
        'pri_partner_steet2',
        'pri_partner_city',
        'pri_partner_statename',
        'pri_partner_zip',
        'pri_partner_countryname',
        'pri_partner_phone',
        'pri_partner_email',
        'industryname',
        'Industryname',
        'ClientIntegration',
        'SCMBookingNo',
        'SCMINTStatus',
        'SCMBookingXML',
    ];

    // ── Accessors ────────────────────────────────────────────────────────────
    // Trim trailing/leading spaces from OfficeCode on every read.
    // Root cause: Cust_Office.Code stores 'KE ' (with trailing space) and this
    // gets written directly to Quote_QuotationInfo.OfficeCode without RTRIM.
    // Fixing here ensures all consumers (blade, controller, SP calls) get clean value.
    public function getOfficeCodeAttribute(?string $value): string
    {
        return trim($value ?? '');
    }
}
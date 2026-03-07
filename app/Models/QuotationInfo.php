<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationInfo extends Model
{
    protected $table = 'Quote_QuotationInfo';

    protected $primaryKey = 'ID';
    public $incrementing = false;        // ← ADD THIS
    protected $keyType = 'string';       // ← ADD THIS

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
        'useremail',         // keep if column exists for backwards compat
        'SalesPersonEmail',  // ← ADD
        'SalesPersonCode',   // ← ADD
        'teamid',
        'teamname',
        'origincountry',
        'destinationcountry',
        'process',
        'incoterm',          // ← was 'incoterms' (wrong)
        'shipmentmode',
        'TransportMode',
        'ServiceClass',
        'commodity',
        'scopevolume',
        'cargovolume',
        'containerlist',
        'CustomerType',      // ← ADD
        'AgentType',         // ← ADD
        'AgentTypeId',       // ← ADD
        'OfficeCode',        // ← ADD
        'QuotationTCTemplate',
        'QuotationEmailTemplate',
        'pri_partner_steet',   // ← was pri_partner_street (DB has typo)
        'pri_partner_steet2',  // ← was pri_partner_street2 (DB has typo)
        'pri_partner_city',
        'pri_partner_state',   // note: DB has partner_statename not pri_partner_state
        'pri_partner_zip',
        'pri_partner_countryname',
        'pri_partnername',     // note: DB has pri_partnermaster_name — verify this
        'pri_partner_phone',
        'pri_partner_email',
        'industryname',
        'Industryname',
        'ClientIntegration',
        'SCMBookingNo',
        'SCMINTStatus',
        'SCMBookingXML',
    ];
}

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
        'useremail',            // column exists (outside schema doc)
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
        'contact_name',             // ← {Contact_name} template variable
        'pri_partnermaster_name',   // ← FIXED: was pri_partnername
        'pri_partner_industrytype', // ← {pri_partner_industrytype} template variable
        'pri_partner_steet',        // DB typo preserved intentionally
        'pri_partner_steet2',       // DB typo preserved intentionally
        'pri_partner_city',
        'pri_partner_statename',    // ← FIXED: was pri_partner_state + {pri_partner_statename} template variable
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
}
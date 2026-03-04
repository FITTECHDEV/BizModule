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
        'companyname',
        'clientid',
        'username',
        'teamname',
        'origincountry',
        'destinationcountry',
        'status',
        'SCMINTStatus',
        'ServiceVerticalname',
        'process',
        'incoterms',
        'shipmentmode',
        'TransportMode',
        'ServiceClass',
        'commodity',
        'scopevolume',
        'cargovolume',
        'containerlist',
        'pri_partner_street',
        'pri_partner_street2',
        'pri_partner_city',
        'pri_partner_state',
        'pri_partner_zip',
        'pri_partner_countryname',
        'pri_partnername',
        'pri_partner_phone',
        'pri_partner_email',
        'Industryname',
        'ClientIntegration',
        'SCMBookingNo',
        'SCMBookingXML',
    ];
}
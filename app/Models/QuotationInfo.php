<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationInfo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Quote_QuotationInfo';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'createdon' => 'datetime',
        'updatedon' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
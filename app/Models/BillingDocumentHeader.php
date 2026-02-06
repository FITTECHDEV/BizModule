<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingDocumentHeader extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Bill_BillingDocumentHeader';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'Id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'Id' => 'string',
        'CustomerId' => 'string',
        'OfficeId' => 'string',
        'PartyId' => 'string',
        'RefBillingDocumentHeaderId' => 'string',
        'BaseBillingDocumentHeaderId' => 'string',
        'BillingCurrencyId' => 'string',
        'Bill_Address_CountryId' => 'string',
        'Bill_Address_StateId' => 'string',
        'TaxInvoiceBillId' => 'string',
        'IsRevenue' => 'boolean',
        'IsActive' => 'boolean',
        'IsAmended' => 'boolean',
        'IsDeleted' => 'boolean',
        'DocDate' => 'datetime',
        'DocDueDate' => 'datetime',
        'RefDate' => 'datetime',
        'CreatedOn' => 'datetime',
        'LastModifiedOn' => 'datetime',
        'SendToAuthorizeDate' => 'datetime',
        'AuthorizationDate' => 'datetime',
        'FinalizationDate' => 'datetime',
        'ProformaInvoiceDate' => 'datetime',
        'BillToBaseExchangeRate' => 'decimal:8',
        'TotalCharges' => 'decimal:5',
        'TotalTaxes' => 'decimal:5',
        'TotalDiscount' => 'decimal:5',
        'BillableTotalAmount' => 'decimal:5',
        'DocTerm' => 'integer',
        'Version' => 'integer',
        'RowVersion' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id',
        'CustomerId',
        'OfficeId',
        'DocNo',
        'PartyId',
        'DocumentType',
        'IsRevenue',
        'RefDocumentType',
        'RefBillingDocumentHeaderId',
        'BaseBillingDocumentHeaderId',
        'VendorRefNo',
        'RefDate',
        'DocDate',
        'DocDueDate',
        'DocTerm',
        'DocPeriod',
        'DocDescription',
        'BillingCurrencyId',
        'BillToBaseExchangeRate',
        'TotalCharges',
        'TotalTaxes',
        'TotalDiscount',
        'BillableTotalAmount',
        'DocStatus',
        'Bill_Address_Line1',
        'Bill_Address_Line2',
        'Bill_Address_Line3',
        'Bill_Address_City',
        'Bill_Address_State',
        'Bill_Address_CountryId',
        'Bill_Address_Pin',
        'Version',
        'DocAuthorizeStatus',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'LastModifiedBy',
        'LastModifiedOn',
        'SendToAuthorizeDate',
        'SendToAuthorizeBy',
        'AuthorizationDate',
        'AuthorizedBy',
        'FinalizationDate',
        'FinalizedBy',
        'IsActive',
        'IsAmended',
        'TaxInvoiceBillId',
        'IsDeleted',
        'RowVersion',
        'PartyName',
        'ProformaInvoiceNo',
        'ProformaInvoiceDate',
        'Bill_Address_StateId',
    ];

    /**
     * Disable Laravel's default timestamp handling since we use custom fields
     *
     * @var bool
     */
    public $timestamps = false;

    // ============================================
    // QUERY SCOPES
    // ============================================

    /**
     * Scope a query to only include active documents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true)->where('IsDeleted', false);
    }

    /**
     * Scope a query to only include revenue documents (Accounts Receivable).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountsReceivable($query)
    {
        return $query->where('IsRevenue', true);
    }

    /**
     * Scope a query to only include non-revenue documents (Accounts Payable).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccountsPayable($query)
    {
        return $query->where('IsRevenue', false);
    }

    /**
     * Scope a query to filter by office.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $officeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOffice($query, $officeId)
    {
        if ($officeId && $officeId !== 'all') {
            return $query->where('OfficeId', $officeId);
        }
        return $query;
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('DocDate', [$startDate, $endDate]);
        }
        return $query;
    }

    /**
     * Scope a query to filter by document status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('DocStatus', $status);
        }
        return $query;
    }

    /**
     * Scope a query to filter by authorization status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAuthorizationStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('DocAuthorizeStatus', $status);
        }
        return $query;
    }

    /**
     * Scope a query to search by invoice number, party name, or document number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('DocNo', 'like', "%{$search}%")
                  ->orWhere('PartyName', 'like', "%{$search}%")
                  ->orWhere('VendorRefNo', 'like', "%{$search}%")
                  ->orWhere('ProformaInvoiceNo', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    // ============================================
    // ACCESSORS & MUTATORS
    // ============================================

    /**
     * Get the formatted document date.
     *
     * @return string
     */
    public function getFormattedDocDateAttribute()
    {
        return $this->DocDate ? $this->DocDate->format('Y-m-d') : '-';
    }

    /**
     * Get the formatted due date.
     *
     * @return string
     */
    public function getFormattedDueDateAttribute()
    {
        return $this->DocDueDate ? $this->DocDueDate->format('Y-m-d') : '-';
    }

    /**
     * Get the formatted total amount.
     *
     * @return string
     */
    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->BillableTotalAmount, 2);
    }

    /**
     * Get the complete billing address.
     *
     * @return string
     */
    public function getFullBillingAddressAttribute()
    {
        $addressParts = array_filter([
            $this->Bill_Address_Line1,
            $this->Bill_Address_Line2,
            $this->Bill_Address_Line3,
            $this->Bill_Address_City,
            $this->Bill_Address_State,
            $this->Bill_Address_Pin,
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Determine if the document is overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->DocDueDate) {
            return false;
        }

        return $this->DocDueDate->isPast() && 
               !in_array(strtolower($this->DocStatus), ['paid', 'cancelled', 'void']);
    }

    /**
     * Get the document type category (AR/AP).
     *
     * @return string
     */
    public function getDocumentCategoryAttribute()
    {
        return $this->IsRevenue ? 'AR' : 'AP';
    }

    /**
     * Get days until due or days overdue.
     *
     * @return int|null
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->DocDueDate) {
            return null;
        }

        return now()->diffInDays($this->DocDueDate, false);
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the customer associated with this billing document.
     * (Assuming you have a Customer model)
     */
    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class, 'CustomerId', 'Id');
    // }

    /**
     * Get the office associated with this billing document.
     * (Assuming you have an Office model)
     */
    // public function office()
    // {
    //     return $this->belongsTo(Office::class, 'OfficeId', 'Id');
    // }

    /**
     * Get the party associated with this billing document.
     * (Assuming you have a Party model)
     */
    // public function party()
    // {
    //     return $this->belongsTo(Party::class, 'PartyId', 'Id');
    // }

    /**
     * Get the billing currency.
     * (Assuming you have a Currency model)
     */
    // public function currency()
    // {
    //     return $this->belongsTo(Currency::class, 'BillingCurrencyId', 'Id');
    // }

    /**
     * Get the reference billing document.
     */
    public function referenceBillingDocument()
    {
        return $this->belongsTo(BillingDocumentHeader::class, 'RefBillingDocumentHeaderId', 'Id');
    }

    /**
     * Get the base billing document.
     */
    public function baseBillingDocument()
    {
        return $this->belongsTo(BillingDocumentHeader::class, 'BaseBillingDocumentHeaderId', 'Id');
    }

    /**
     * Get all documents that reference this document.
     */
    public function referencingDocuments()
    {
        return $this->hasMany(BillingDocumentHeader::class, 'RefBillingDocumentHeaderId', 'Id');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if document is authorized.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return !empty($this->AuthorizationDate) && !empty($this->AuthorizedBy);
    }

    /**
     * Check if document is finalized.
     *
     * @return bool
     */
    public function isFinalized()
    {
        return !empty($this->FinalizationDate) && !empty($this->FinalizedBy);
    }

    /**
     * Get status badge class for UI.
     *
     * @return string
     */
    public function getStatusBadgeClass()
    {
        $status = strtolower($this->DocStatus ?? '');
        
        $statusMap = [
            'paid' => 'badge-success',
            'success' => 'badge-success',
            'completed' => 'badge-success',
            'pending' => 'badge-warning',
            'draft' => 'badge-secondary',
            'sent' => 'badge-info',
            'overdue' => 'badge-danger',
            'failed' => 'badge-danger',
            'cancelled' => 'badge-dark',
            'void' => 'badge-dark',
        ];

        return $statusMap[$status] ?? 'badge-secondary';
    }

    /**
     * Get authorization status badge class for UI.
     *
     * @return string
     */
    public function getAuthorizationBadgeClass()
    {
        $status = strtolower($this->DocAuthorizeStatus ?? '');
        
        $statusMap = [
            'approved' => 'badge-success',
            'pending' => 'badge-warning',
            'rejected' => 'badge-danger',
            'not submitted' => 'badge-secondary',
        ];

        return $statusMap[$status] ?? 'badge-secondary';
    }
}
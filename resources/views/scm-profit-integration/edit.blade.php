@extends('layouts.app')

@section('title', 'Edit Booking - ' . $booking->bookingno)

@section('styles')
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.page-header {
    background-color: #2c3e50;
    color: white;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    gap: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.page-header h1 {
    font-size: 24px;
    margin: 0;
}

.container-edit {
    display: grid;
    grid-template-columns: 500px 1fr;
    gap: 20px;
    padding: 20px;
    max-width: 1800px;
    margin: 0 auto;
}

.left-panel,
.right-panel {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.bill-number {
    padding: 20px;
    background-color: white;
    border-bottom: 1px solid #e0e0e0;
}

.bill-number h2 {
    font-size: 28px;
    color: #2c3e50;
    text-decoration: underline;
}

.accordion-section {
    border-bottom: 1px solid #e0e0e0;
}

.accordion-header {
    background-color: #5a7fa6;
    color: white;
    padding: 15px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    transition: background-color 0.3s;
}

.accordion-header:hover {
    background-color: #4a6f96;
}

.accordion-header.active {
    background-color: #4a6f96;
}

.accordion-icon {
    font-size: 12px;
    transition: transform 0.3s;
}

.accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    background-color: #f8f9fa;
}

.accordion-content.show {
    max-height: 2000px;
    padding: 20px;
}

.form-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
}

.form-row:last-child {
    border-bottom: none;
}

.form-row label {
    font-weight: 600;
    color: #2c3e50;
    flex: 1;
}

.form-row input,
.form-row select,
.form-row textarea {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-row input:focus,
.form-row select:focus,
.form-row textarea:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.form-row .readonly {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.section-header {
    background-color: #4a90e2;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;
}

.details-container {
    background-color: #f8f9fa;
    padding: 20px;
}

.action-buttons-edit {
    display: flex;
    gap: 10px;
    padding: 20px;
    justify-content: flex-end;
    background-color: #f8f9fa;
    border-top: 2px solid #e0e0e0;
}

.btn-save,
.btn-cancel {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-save {
    background-color: #28a745;
    color: white;
}

.btn-save:hover {
    background-color: #218838;
}

.btn-cancel {
    background-color: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background-color: #5a6268;
    text-decoration: none;
    color: white;
}

.alert {
    padding: 15px 20px;
    margin: 20px;
    border-radius: 4px;
    display: none;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .container-edit {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .container-edit {
        padding: 10px;
        gap: 15px;
    }
    
    .form-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .form-row input,
    .form-row select,
    .form-row textarea {
        width: 100%;
    }
    
    .action-buttons-edit {
        flex-direction: column;
    }
    
    .btn-save,
    .btn-cancel {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endsection

@section('content')
<div class="page-header">
    <h1>Edit Booking: {{ $booking->bookingno ?? 'N/A' }}</h1>
</div>

<div id="alertContainer"></div>

<form id="editBookingForm">
    @csrf
    @method('PUT')
    
    <div class="container-edit">
        <div class="left-panel">
            <div class="bill-number">
                <h2>{{ $booking->SCMBookingNo ?? $booking->bookingno ?? 'N/A' }}</h2>
            </div>

            <!-- Basic Information -->
            <div class="accordion-section">
                <div class="accordion-header active" onclick="toggleAccordion(this)">
                    <span>BASIC INFORMATION</span>
                    <span class="accordion-icon">▲</span>
                </div>
                <div class="accordion-content show">
                    <div class="form-row">
                        <label>Booking Number</label>
                        <input type="text" name="bookingno" value="{{ $booking->bookingno ?? '' }}" class="readonly" readonly>
                    </div>
                    <div class="form-row">
                        <label>Request Date</label>
                        <input type="text" value="{{ $booking->createdon ? \Carbon\Carbon::parse($booking->createdon)->format('d M Y') : 'N/A' }}" class="readonly" readonly>
                    </div>
                    <div class="form-row">
                        <label>Office</label>
                        <input type="text" name="teamname" value="{{ $booking->teamname ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Sales Person</label>
                        <input type="text" name="username" value="{{ $booking->username ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Service Vertical</label>
                        <input type="text" name="ServiceVertical" value="{{ $booking->ServiceVertical ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Transport Mode</label>
                        <select name="TransportMode">
                            <option value="">Select Mode</option>
                            <option value="Air" {{ ($booking->TransportMode ?? '') == 'Air' ? 'selected' : '' }}>Air</option>
                            <option value="Sea" {{ ($booking->TransportMode ?? '') == 'Sea' ? 'selected' : '' }}>Sea</option>
                            <option value="Road" {{ ($booking->TransportMode ?? '') == 'Road' ? 'selected' : '' }}>Road</option>
                            <option value="Rail" {{ ($booking->TransportMode ?? '') == 'Rail' ? 'selected' : '' }}>Rail</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Client Information -->
            <div class="accordion-section">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>CLIENT INFORMATION</span>
                    <span class="accordion-icon">▼</span>
                </div>
                <div class="accordion-content">
                    <div class="form-row">
                        <label>Client Name</label>
                        <input type="text" name="companyname" value="{{ $booking->companyname ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Client ID</label>
                        <input type="text" name="clientid" value="{{ $booking->clientid ?? '' }}" class="readonly" readonly>
                    </div>
                    <div class="form-row">
                        <label>Contact Name</label>
                        <input type="text" name="contact_name" value="{{ $booking->contact_name ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Email</label>
                        <input type="email" name="email_normalized" value="{{ $booking->email_normalized ?? $booking->email_from ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Phone</label>
                        <input type="text" name="phone_sanitized" value="{{ $booking->phone_sanitized ?? $booking->phone ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Shipment Movement -->
            <div class="accordion-section">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>SHIPMENT MOVEMENT</span>
                    <span class="accordion-icon">▼</span>
                </div>
                <div class="accordion-content">
                    <div class="form-row">
                        <label>Origin Country</label>
                        <input type="text" name="origincountry" value="{{ $booking->origincountry ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Origin Country Code</label>
                        <input type="text" name="origin_country_code" value="{{ $booking->origin_country_code ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Destination Country</label>
                        <input type="text" name="destinationcountry" value="{{ $booking->destinationcountry ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Destination Country Code</label>
                        <input type="text" name="destination_country_code" value="{{ $booking->destination_country_code ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Shipment Mode</label>
                        <input type="text" name="shipmentmode" value="{{ $booking->shipmentmode ?? '' }}">
                    </div>
                    <div class="form-row">
                        <label>Incoterm</label>
                        <input type="text" name="incoterm" value="{{ $booking->incoterm ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Integration Status -->
            <div class="accordion-section">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>INTEGRATION STATUS</span>
                    <span class="accordion-icon">▼</span>
                </div>
                <div class="accordion-content">
                    <div class="form-row">
                        <label>Client Integration</label>
                        <input type="text" value="{{ $booking->ClientIntegration ?? 'N/A' }}" class="readonly" readonly>
                    </div>
                    <div class="form-row">
                        <label>SCM Booking No</label>
                        <input type="text" value="{{ $booking->SCMBookingNo ?? 'N/A' }}" class="readonly" readonly>
                    </div>
                    <div class="form-row">
                        <label>SCM Job No</label>
                        <input type="text" value="{{ $booking->SCMJobNo ?? 'N/A' }}" class="readonly" readonly>
                    </div>
                    <div class="form-row">
                        <label>Integration Status</label>
                        <input type="text" value="@php
                            $status = 'Pending';
                            if (!empty($booking->SCMINTStatus)) {
                                if (stripos($booking->SCMINTStatus, 'success') !== false) {
                                    $status = 'Success';
                                } else {
                                    $status = 'Failed';
                                }
                            }
                            echo $status;
                        @endphp" class="readonly" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <!-- Cargo Details -->
            <div class="accordion-section">
                <div class="accordion-header active" onclick="toggleAccordion(this)">
                    <span>CARGO DETAILS</span>
                    <span class="accordion-icon">▲</span>
                </div>
                <div class="accordion-content show">
                    <div class="details-container">
                        <div class="form-row">
                            <label>Commodity</label>
                            <input type="text" name="commodity" value="{{ $booking->commodity ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Cargo Volume</label>
                            <input type="number" step="0.01" name="cargovolume" value="{{ $booking->cargovolume ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Scope of Volume</label>
                            <input type="text" name="scopevolume" value="{{ $booking->scopevolume ?? $booking->scope_of_volume ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Container List</label>
                            <textarea name="containerlist" rows="3">{{ $booking->containerlist ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote Information -->
            <div class="accordion-section">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>QUOTE INFORMATION</span>
                    <span class="accordion-icon">▼</span>
                </div>
                <div class="accordion-content">
                    <div class="details-container">
                        <div class="form-row">
                            <label>Quotation No</label>
                            <input type="text" name="quotationo" value="{{ $booking->quotationo ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Quoted Revenue</label>
                            <input type="number" step="0.01" name="quoted_revenue" value="{{ $booking->quoted_revenue ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Expected Revenue</label>
                            <input type="number" step="0.01" name="expected_revenue" value="{{ $booking->expected_revenue ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Prorated Revenue</label>
                            <input type="number" step="0.01" name="prorated_revenue" value="{{ $booking->prorated_revenue ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Won Status</label>
                            <select name="won_status">
                                <option value="">Select Status</option>
                                <option value="Pending" {{ ($booking->won_status ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Won" {{ ($booking->won_status ?? '') == 'Won' ? 'selected' : '' }}>Won</option>
                                <option value="Lost" {{ ($booking->won_status ?? '') == 'Lost' ? 'selected' : '' }}>Lost</option>
                                <option value="Cancel" {{ ($booking->won_status ?? '') == 'Cancel' ? 'selected' : '' }}>Cancel</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="accordion-section">
                <div class="accordion-header" onclick="toggleAccordion(this)">
                    <span>ADDITIONAL INFORMATION</span>
                    <span class="accordion-icon">▼</span>
                </div>
                <div class="accordion-content">
                    <div class="details-container">
                        <div class="form-row">
                            <label>Source</label>
                            <input type="text" name="source" value="{{ $booking->source ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Medium</label>
                            <input type="text" name="medium" value="{{ $booking->medium ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Process</label>
                            <input type="text" name="process" value="{{ $booking->process ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Service Class</label>
                            <input type="text" name="ServiceClass" value="{{ $booking->ServiceClass ?? '' }}">
                        </div>
                        <div class="form-row">
                            <label>Industry</label>
                            <input type="text" name="industryname" value="{{ $booking->industryname ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons-edit">
                <button type="submit" class="btn-save">
                    <i class="fa fa-save"></i> Save Changes
                </button>
                <a href="{{ route('scm-profit-integration.show', $booking->bookingno) }}" class="btn-cancel">
                    <i class="fa fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
function toggleAccordion(element) {
    const content = element.nextElementSibling;
    const icon = element.querySelector('.accordion-icon');
    
    element.classList.toggle('active');
    content.classList.toggle('show');
    
    if (content.classList.contains('show')) {
        icon.textContent = '▲';
    } else {
        icon.textContent = '▼';
    }
}

// Handle form submission via AJAX
document.getElementById('editBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const bookingNo = '{{ $booking->bookingno }}';
    
    // Show loading state
    const saveBtn = document.querySelector('.btn-save');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch(`/scm-profit-integration/${bookingNo}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Success! Changes have been saved.', 'success');
            setTimeout(() => {
                window.location.href = `/scm-profit-integration/${bookingNo}`;
            }, 1500);
        } else {
            showAlert('Error: ' + (data.message || 'Failed to save changes'), 'error');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        showAlert('Error: An unexpected error occurred', 'error');
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
});

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    
    alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
    alertContainer.firstChild.style.display = 'block';
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
</script>
@endsection
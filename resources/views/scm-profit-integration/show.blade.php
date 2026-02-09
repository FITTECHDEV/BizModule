@extends('layouts.app')

@section('title', 'View Booking - ' . $booking->bookingno)

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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 24px;
            margin: 0;
        }

        .back-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .back-btn:hover {
            background-color: #2980b9;
            color: white;
            text-decoration: none;
        }

        .container-view {
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row label {
            font-weight: 600;
            color: #2c3e50;
            flex: 1;
        }

        .info-row .value {
            flex: 1;
            text-align: right;
            color: #555;
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

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-pending {
            background-color: #ffc107;
            color: #856404;
        }

        .status-success {
            background-color: #28a745;
            color: white;
        }

        .status-failed {
            background-color: #dc3545;
            color: white;
        }

        .action-buttons-view {
            display: flex;
            gap: 10px;
            padding: 20px;
            justify-content: flex-end;
        }

        .btn-edit,
        .btn-back {
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

        .btn-edit {
            background-color: #ffc107;
            color: #333;
        }

        .btn-edit:hover {
            background-color: #ffb300;
            text-decoration: none;
            color: #333;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
            text-decoration: none;
            color: white;
        }

        /* Revenue and Cost Section Styles */
        .revenue-section,
        .cost-section {
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 0;
        }

        .section-bar {
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .revenue-bar {
            background-color: #5a7fa6;
            color: white;
        }

        .cost-bar {
            background-color: #d4e3f3;
            color: #2c3e50;
        }

        .section-bar:hover {
            opacity: 0.9;
        }

        .section-title {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .section-amount {
            font-weight: 700;
            font-size: 15px;
        }

        .charge-details {
            background-color: #f8f9fa;
        }

        .show-more-container {
            padding: 15px 20px;
            text-align: right;
        }

        .show-more-btn {
            background-color: white;
            border: 1px solid #d1d5db;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #4b5563;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .show-more-btn:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }

        .show-more-btn .arrow {
            font-size: 10px;
            transition: transform 0.3s;
        }

        .show-more-btn.expanded .arrow {
            transform: rotate(180deg);
        }

        .charges-table-wrapper {
            padding: 0 20px 20px 20px;
        }

        /* Charges Table Styles */
        .charges-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .charges-table thead tr {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
        }

        .charges-table th {
            padding: 12px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
            text-align: left;
        }

        .charges-table td {
            padding: 12px;
            color: #555;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
        }

        .charges-table tbody tr:last-child td {
            border-bottom: none;
        }

        .charges-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .charges-table .text-right {
            text-align: right;
        }

        .charges-table .text-center {
            text-align: center;
        }

        .action-icon {
            display: inline-block;
            margin: 0 5px;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .action-icon:hover {
            opacity: 1;
        }

        .total-row-inline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 4px;
            font-weight: 600;
        }

        .total-label {
            color: #2c3e50;
            font-size: 14px;
        }

        .total-value {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 700;
        }

        /* Profit Summary Section */
        .profit-summary-section {
            background-color: white;
            padding: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .warning-message {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .profit-display {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .profit-label {
            color: #059669;
            font-weight: 700;
            font-size: 16px;
            min-width: 60px;
        }

        .profit-bars {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .bar-container {
            width: 100%;
            height: 8px;
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            background-color: #e5e7eb;
        }

        .bar {
            height: 100%;
        }

        .bar-cyan {
            background-color: #06b6d4;
        }

        .bar-yellow {
            background-color: #fbbf24;
        }

        .profit-percentage {
            color: #2c3e50;
            font-weight: 700;
            font-size: 16px;
            min-width: 70px;
            text-align: right;
        }

        .profit-amount {
            color: #2c3e50;
            font-weight: 700;
            font-size: 18px;
            min-width: 100px;
            text-align: right;
        }

        .action-buttons-profit {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .action-buttons-profit button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-show-all {
            background-color: #06b6d4;
            color: white;
        }

        .btn-show-all:hover {
            background-color: #0891b2;
        }

        .btn-save-charges {
            background-color: #fbbf24;
            color: #78350f;
        }

        .btn-save-charges:hover {
            background-color: #f59e0b;
        }

        .btn-retrigger {
            background-color: #10b981;
            color: white;
        }

        .btn-retrigger:hover {
            background-color: #059669;
        }

        .btn-icon {
            font-size: 16px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container-view {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .back-btn {
                margin-left: 0;
                width: 100%;
                justify-content: center;
            }

            .container-view {
                padding: 10px;
                gap: 15px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .info-row .value {
                text-align: left;
            }

            .action-buttons-view {
                flex-direction: column;
            }

            .btn-edit,
            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .charges-table {
                font-size: 12px;
            }

            .charges-table th,
            .charges-table td {
                padding: 8px;
            }

            .profit-display {
                flex-direction: column;
                align-items: flex-start;
            }

            .profit-bars {
                width: 100%;
            }

            .action-buttons-profit {
                flex-direction: column;
            }

            .action-buttons-profit button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h1>View Booking: {{ $booking->bookingno ?? 'N/A' }}</h1>
        <a href="{{ route('scm-profit-integration.index') }}" class="back-btn">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="container-view">
        <div class="left-panel">
            <div class="bill-number">
                <h2>{{ $booking->SCMBookingNo ?? ($booking->bookingno ?? 'N/A') }}</h2>
            </div>

            <!-- Basic Information -->
            <div class="accordion-section">
                <div class="accordion-header active" onclick="toggleAccordion(this)">
                    <span>BASIC INFORMATION</span>
                    <span class="accordion-icon">▲</span>
                </div>
                <div class="accordion-content show">
                    <div class="info-row">
                        <label>Booking Number</label>
                        <span class="value">{{ $booking->bookingno ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Request Date</label>
                        <span
                            class="value">{{ $booking->createdon ? \Carbon\Carbon::parse($booking->createdon)->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Office</label>
                        <span class="value">{{ $booking->teamname ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Sales Person</label>
                        <span class="value">{{ $booking->username ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Service Vertical</label>
                        <span class="value">{{ $booking->SVName ?? ($booking->ServiceVertical ?? 'N/A') }}</span>
                    </div>
                    <div class="info-row">
                        <label>Transport Mode</label>
                        <span class="value">{{ $booking->TransportMode ?? 'N/A' }}</span>
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
                    <div class="info-row">
                        <label>Client Name</label>
                        <span class="value">{{ $booking->companyname ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Client ID</label>
                        <span class="value">{{ $booking->clientid ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Client Code (SCM)</label>
                        <span class="value">{{ $booking->ClientCodeINSCM ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Contact Name</label>
                        <span class="value">{{ $booking->contact_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Email</label>
                        <span class="value">{{ $booking->email_normalized ?? ($booking->email_from ?? 'N/A') }}</span>
                    </div>
                    <div class="info-row">
                        <label>Phone</label>
                        <span
                            class="value">{{ $booking->phone_sanitized ?? ($booking->phone ?? ($booking->mobile ?? 'N/A')) }}</span>
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
                    <div class="info-row">
                        <label>Origin Country</label>
                        <span class="value">{{ $booking->origincountry ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Origin Country Code</label>
                        <span class="value">{{ $booking->origin_country_code ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Destination Country</label>
                        <span class="value">{{ $booking->destinationcountry ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Destination Country Code</label>
                        <span class="value">{{ $booking->destination_country_code ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Shipment Mode</label>
                        <span class="value">{{ $booking->shipmentmode ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Incoterm</label>
                        <span class="value">{{ $booking->incoterm ?? 'N/A' }}</span>
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
                    <div class="info-row">
                        <label>Client Integration</label>
                        <span class="value">{{ $booking->ClientIntegration ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>SCM Booking No</label>
                        <span class="value">{{ $booking->SCMBookingNo ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>SCM Job No</label>
                        <span class="value">{{ $booking->SCMJobNo ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Integration Status</label>
                        <span class="value">
                            @php
                                $status = 'Pending';
                                $statusClass = 'status-pending';
                                if (!empty($booking->SCMINTStatus)) {
                                    if (stripos($booking->SCMINTStatus, 'success') !== false) {
                                        $status = 'Success';
                                        $statusClass = 'status-success';
                                    } else {
                                        $status = 'Failed';
                                        $statusClass = 'status-failed';
                                    }
                                }
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                        </span>
                    </div>
                    @if ($status === 'Failed' && !empty($booking->SCMINTStatus))
                        <div class="info-row">
                            <label>Status Remark</label>
                            <span class="value">{{ $booking->SCMINTStatus }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <label>Created in SCM</label>
                        <span
                            class="value">{{ $booking->CreatedOnInSCM ? \Carbon\Carbon::parse($booking->CreatedOnInSCM)->format('d M Y H:i') : 'N/A' }}</span>
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
                        <div class="info-row">
                            <label>Commodity</label>
                            <span class="value">{{ $booking->commodity ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <label>Cargo Volume</label>
                            <span class="value">{{ $booking->cargovolume ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <label>Scope of Volume</label>
                            <span class="value">{{ $booking->scopevolume ?? ($booking->scope_of_volume ?? 'N/A') }}</span>
                        </div>
                        <div class="info-row">
                            <label>Container List</label>
                            <span class="value">{{ $booking->containerlist ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Section -->
            <div class="accordion-section revenue-section">
                <div class="section-bar revenue-bar" onclick="toggleChargeSection(this)">
                    <span class="section-title">REVENUE</span>
                    <span class="section-amount">70,980.15</span>
                </div>
                <div class="charge-details">
                    <div class="show-more-container">
                        <button class="show-more-btn" onclick="toggleChargesTable(this)">
                            Show More <span class="arrow">▼</span>
                        </button>
                    </div>
                    <div class="charges-table-wrapper" style="display: none;">
                        <table class="charges-table">
                            <thead>
                                <tr>
                                    <th>Charge Name</th>
                                    <th>Bill to Party</th>
                                    <th>Description</th>
                                    <th>Currency</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Base Amount</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Freight Charges (Auto)</td>
                                    <td>BAMBURI BEACH</td>
                                    <td></td>
                                    <td>KES</td>
                                    <td class="text-right">34,755.15</td>
                                    <td class="text-right">34,755.15</td>
                                    <td class="text-center">
                                        <span class="action-icon">✎</span>
                                        <span class="action-icon">🗑</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Airfreight Charges</td>
                                    <td>C Dorman Limited</td>
                                    <td></td>
                                    <td>KES</td>
                                    <td class="text-right">36,225.00</td>
                                    <td class="text-right">36,225.00</td>
                                    <td class="text-center">
                                        <span class="action-icon">✎</span>
                                        <span class="action-icon">🗑</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="total-row-inline">
                            <span class="total-label">TOTAL REVENUE</span>
                            <span class="total-value">70,980.15</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost Section -->
            <div class="accordion-section cost-section">
                <div class="section-bar cost-bar" onclick="toggleChargeSection(this)">
                    <span class="section-title">COST</span>
                    <span class="section-amount">30,450.00</span>
                </div>
                <div class="charge-details">
                    <div class="show-more-container">
                        <button class="show-more-btn" onclick="toggleChargesTable(this)">
                            Show More <span class="arrow">▼</span>
                        </button>
                    </div>
                    <div class="charges-table-wrapper" style="display: none;">
                        <table class="charges-table">
                            <thead>
                                <tr>
                                    <th>Charge Name</th>
                                    <th>Bill to Party</th>
                                    <th>Description</th>
                                    <th>Currency</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Base Amount</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Airfreight Charges</td>
                                    <td>UPS SETTLEMENT ACCOUNT</td>
                                    <td></td>
                                    <td>KES</td>
                                    <td class="text-right">30,450.00</td>
                                    <td class="text-right">30,450.00</td>
                                    <td class="text-center">
                                        <span class="action-icon">✎</span>
                                        <span class="action-icon">🗑</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="total-row-inline">
                            <span class="total-label">TOTAL COST</span>
                            <span class="total-value">30,450.00</span>
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

                        <div class="info-row">
                            <label>Quotation No</label>
                            <span class="value">{{ $booking->quotationo ?? 'N/A' }}</span>
                        </div>

                        <div class="info-row">
                            <label>Quoted Revenue</label>
                            <span class="value">{{ number_format($quotedRevenue, 2) }}</span>
                        </div>

                        <div class="info-row">
                            <label>Expected Revenue</label>
                            <span class="value">
                                {{ $booking->expected_revenue ? number_format($booking->expected_revenue, 2) : 'N/A' }}
                            </span>
                        </div>

                        <div class="info-row">
                            <label>Prorated Revenue</label>
                            <span class="value">
                                {{ $booking->prorated_revenue ? number_format($booking->prorated_revenue, 2) : 'N/A' }}
                            </span>
                        </div>

                        <div class="info-row">
                            <label>Won Status</label>
                            <span class="value">{{ $booking->won_status ?? 'N/A' }}</span>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>

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

        function toggleChargeSection(element) {
            // Optional: Add collapse functionality for the entire section if needed
            const chargeDetails = element.nextElementSibling;
            chargeDetails.style.display = chargeDetails.style.display === 'none' ? 'block' : 'block';
        }

        function toggleChargesTable(button) {
            const wrapper = button.parentElement.nextElementSibling;
            const arrow = button.querySelector('.arrow');
            
            if (wrapper.style.display === 'none') {
                wrapper.style.display = 'block';
                button.textContent = 'Show Less ';
                arrow.textContent = '▲';
                button.appendChild(arrow);
                button.classList.add('expanded');
            } else {
                wrapper.style.display = 'none';
                button.textContent = 'Show More ';
                arrow.textContent = '▼';
                button.appendChild(arrow);
                button.classList.remove('expanded');
            }
        }
    </script>
@endsection
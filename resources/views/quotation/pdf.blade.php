<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation {{ $quotation->bookingno }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000;
            line-height: 1.3;
        }

        /* ═══════════════════════════════════════
           HEADER
        ═══════════════════════════════════════ */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .logo-cell {
            width: 55px;
        }
        .logo-box {
            width: 50px;
            height: 38px;
            border: 2px solid #1a3d6b;
            text-align: center;
            vertical-align: middle;
            padding: 2px;
        }
        .logo-fit {
            font-size: 20pt;
            font-weight: bold;
            color: #1a3d6b;
            line-height: 1;
        }
        .company-cell {
            padding-left: 6px;
        }
        .company-name {
            font-size: 9pt;
            font-weight: bold;
        }
        .company-address {
            font-size: 7.5pt;
            color: #333;
        }
        .quotation-ref-cell {
            text-align: right;
            white-space: nowrap;
            vertical-align: top;
            padding-top: 2px;
        }
        .quotation-ref-label {
            font-size: 13pt;
            font-weight: bold;
        }
        .quotation-ref-number {
            font-size: 11pt;
            font-weight: bold;
        }

        /* ═══════════════════════════════════════
           INFO ROWS (To / Date / Client etc.)
        ═══════════════════════════════════════ */
        .info-outer {
            width: 100%;
            margin-bottom: 3px;
        }
        .info-outer td {
            vertical-align: top;
            padding: 1px 4px 1px 0;
            font-size: 7.5pt;
        }
        .lbl {
            color: #444;
            white-space: nowrap;
            font-size: 7.5pt;
        }
        .val {
            font-size: 7.5pt;
        }
        .val-bold {
            font-size: 7.5pt;
            font-weight: bold;
        }

        /* ═══════════════════════════════════════
           DIVIDERS
        ═══════════════════════════════════════ */
        .divider-thin {
            border: none;
            border-top: 1px solid #ccc;
            margin: 3px 0;
        }
        .divider-thick {
            border: none;
            border-top: 2px solid #000;
            margin: 5px 0;
        }

        /* ═══════════════════════════════════════
           SECTION HEADING (SEA - IMPORT)
        ═══════════════════════════════════════ */
        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* ═══════════════════════════════════════
           SHIPMENT DETAILS GRID
        ═══════════════════════════════════════ */
        .shipment-grid {
            width: 100%;
            margin-bottom: 7px;
        }
        .shipment-grid td {
            vertical-align: top;
            padding: 1.5px 4px 1.5px 0;
            font-size: 7.5pt;
            width: 25%;
        }
        .shipment-label {
            color: #555;
            font-size: 7pt;
            display: block;
        }
        .shipment-value {
            font-size: 7.5pt;
            display: block;
        }

        /* ═══════════════════════════════════════
           CHARGES TABLE
        ═══════════════════════════════════════ */
        .charges-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 7.5pt;
        }
        .charges-table thead tr {
            background-color: #3a3a3a;
            color: #ffffff;
        }
        .charges-table thead th {
            padding: 4px 5px;
            text-align: left;
            border: 0.5px solid #666;
            font-size: 7.5pt;
            font-weight: bold;
            line-height: 1.3;
        }
        .charges-table tbody td {
            padding: 3px 5px;
            border: 0.5px solid #bbb;
            vertical-align: top;
            font-size: 7.5pt;
        }
        .charges-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .charges-table .total-row td {
            border-top: 1.5px solid #000;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* ═══════════════════════════════════════
           TERMS & CONDITIONS
        ═══════════════════════════════════════ */
        .terms-heading {
            font-size: 8.5pt;
            font-weight: bold;
            margin-bottom: 4px;
            margin-top: 2px;
        }
        .terms-body {
            font-size: 6.8pt;
            line-height: 10pt;
        }
        .terms-body p {
            margin-bottom: 1px;
        }

        /* ═══════════════════════════════════════
           ADDITIONAL TERMS FOOTER
        ═══════════════════════════════════════ */
        .additional-terms {
            font-size: 8.5pt;
            font-weight: bold;
            margin-top: 18px;
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════
     HEADER — Logo / Company / Quotation Ref
══════════════════════════════════════════ --}}
<table class="header-table">
    <tr>
        <td class="logo-cell">
            <div class="logo-box">
                <span class="logo-fit">FiT</span>
            </div>
        </td>
        <td class="company-cell">
            <div class="company-name">FREIGHT IN TIME (U) LIMITED</div>
            <div class="company-address">P.O. BOX 70942, OLD PORT BELL ROAD, PLOT 20, KAMPALA, UGANDA</div>
        </td>
        <td class="quotation-ref-cell">
            <div class="quotation-ref-label">Quotation For</div>
            <div class="quotation-ref-number">{{ $quotation->bookingno ?? 'N/A' }}</div>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════
     TO / DATE / CURR / CLIENT NAME
══════════════════════════════════════════ --}}
<table class="info-outer">
    <tr>
        <td class="lbl" style="width:22mm;">To</td>
        <td style="width:60mm;">
            <div class="val-bold">{{ $quotation->companyname ?? 'N/A' }}</div>
            @if(!empty($quotation->pri_partner_city))
                <div class="val">{{ strtoupper($quotation->pri_partner_city) }}</div>
            @endif
            @if(!empty($quotation->pri_partner_street))
                <div class="val">{{ strtoupper($quotation->pri_partner_street) }}</div>
            @endif
            @if(!empty($quotation->pri_partner_state))
                <div class="val">{{ strtoupper($quotation->pri_partner_state) }}</div>
            @endif
        </td>
        <td style="width:20mm;"><span class="val-bold">Date</span></td>
        <td style="width:28mm;"><span class="val">{{ $generatedDate }}</span></td>
        <td style="width:14mm;"><span class="val-bold">Curr.</span></td>
        <td><span class="val-bold">{{ $currencyCode ?? 'N/A' }}</span></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td><span class="lbl">Client Name</span></td>
        <td colspan="3"><span class="val">{{ $quotation->companyname ?? 'N/A' }}</span></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td><span class="lbl">Payment Terms</span></td>
        <td colspan="3"><span class="val">{{ $quotation->teamname ?? '' }}</span></td>
    </tr>
</table>

<hr class="divider-thin">

{{-- ══════════════════════════════════════════
     PLACE OF RECEIPT / DELIVERY / MOVEMENT / INCO / SHIPMENT
══════════════════════════════════════════ --}}
<table class="info-outer">
    <tr>
        <td class="lbl" style="width:22mm;">Place of Receipt</td>
        <td style="width:48mm;"><span class="val">{{ $quotation->origincountry ?? 'N/A' }}</span></td>
        <td style="width:26mm;"><span class="lbl">Movement Type</span></td>
        <td style="width:30mm;"><span class="val">{{ $quotation->TransportMode ?? '' }}</span></td>
        <td style="width:24mm;"><span class="lbl">Shipment Type</span></td>
        <td><span class="val-bold">{{ $quotation->shipmentmode ?? 'DIRECT' }}</span></td>
    </tr>
    <tr>
        <td class="lbl">Place of Delivery</td>
        <td><span class="val">{{ $quotation->destinationcountry ?? 'N/A' }}</span></td>
        <td><span class="lbl">Inco Term</span></td>
        <td><span class="val">{{ $quotation->incoterms ?? '' }}</span></td>
        <td><span class="lbl">Payment Terms</span></td>
        <td><span class="val"></span></td>
    </tr>
</table>

{{-- GROSS / CHARGEABLE / VOLUME ──────── --}}
<table class="info-outer" style="margin-top:2px;">
    <tr>
        <td class="lbl" style="width:22mm;">Gross Wt.</td>
        <td style="width:16mm;"><span class="val">0.00</span></td>
        <td style="width:30mm;"><span class="lbl">Chargeable Wt.</span></td>
        <td style="width:16mm;"><span class="val">0.00</span></td>
        <td style="width:20mm;"><span class="lbl">Volume</span></td>
        <td><span class="val">0.000</span></td>
    </tr>
</table>

<hr class="divider-thick">

{{-- ══════════════════════════════════════════
     SERVICE SECTION HEADING (SEA - IMPORT etc.)
══════════════════════════════════════════ --}}
<div class="section-heading">
    {{ strtoupper($quotation->ServiceVerticalname ?? 'SEA - IMPORT') }}
</div>

{{-- ══════════════════════════════════════════
     SHIPMENT DETAILS GRID (4 columns x 3 rows)
══════════════════════════════════════════ --}}
<table class="shipment-grid">
    <tr>
        <td>
            <span class="shipment-label">Owner Office</span>
            <span class="shipment-value">{{ $quotation->teamname ?? 'N/A' }}</span>
        </td>
        <td>
            <span class="shipment-label">Executing Office</span>
            <span class="shipment-value">{{ $quotation->teamname ?? 'N/A' }}</span>
        </td>
        <td>
            <span class="shipment-label">Equipment</span>
            <span class="shipment-value">{{ $quotation->containerlist ?? '' }}</span>
        </td>
        <td>
            <span class="shipment-label">Vehicle</span>
            <span class="shipment-value"></span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="shipment-label">Place of Loading</span>
            <span class="shipment-value"></span>
        </td>
        <td>
            <span class="shipment-label">Place of Discharge</span>
            <span class="shipment-value">{{ $quotation->origincountry ?? '' }}</span>
        </td>
        <td>
            <span class="shipment-label">Carrier Name</span>
            <span class="shipment-value"></span>
        </td>
        <td>
            <span class="shipment-label">Master No.</span>
            <span class="shipment-value"></span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="shipment-label">Service Class</span>
            <span class="shipment-value">{{ $quotation->ServiceVerticalname ?? '' }}</span>
        </td>
        <td>
            <span class="shipment-label">ETD</span>
            <span class="shipment-value"></span>
        </td>
        <td>
            <span class="shipment-label">House No.</span>
            <span class="shipment-value"></span>
        </td>
        <td>
            <span class="shipment-label">ETA</span>
            <span class="shipment-value"></span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════
     CHARGES TABLE — Revenue charges shown to client
══════════════════════════════════════════ --}}
<table class="charges-table">
    <thead>
        <tr>
            <th style="width:30%;">Charge Description<br>Activity</th>
            <th style="width:14%;">Based on Qty<br>&amp; UOM</th>
            <th style="width:18%;">Rate &amp; Curr.<br>Min. Amt.</th>
            <th style="width:12%; text-align:right;">Charge Amt.</th>
            <th style="width:10%; text-align:right;">Ex. Rate</th>
            <th style="width:12%; text-align:right;">Quote Amt.</th>
            <th style="width:4%; text-align:center;">Tax</th>
        </tr>
    </thead>
    <tbody>
        @forelse($revenueCharges as $charge)
            <tr>
                <td>{{ $charge->ChargeName ?? $charge->ChargeCode ?? 'N/A' }}</td>
                <td>
                    {{ $charge->BasedOnUOM ?? $charge->BasedOn ?? '' }}<br>
                    <span style="color:#555;">{{ number_format((float)$charge->Quantity, 0) }}</span>
                </td>
                <td>
                    {{ number_format((float)$charge->Rate, 2) }} {{ $charge->ChgCurr ?? '' }}<br>
                    <span style="color:#555;">0.00</span>
                </td>
                <td class="text-right">
                    {{ number_format((float)$charge->AmtInChgCurr, 2) }}
                </td>
                <td class="text-right">
                    @if((float)$charge->ExChangeRate && (float)$charge->ExChangeRate != 1)
                        {{ number_format((float)$charge->ExChangeRate, 2) }}
                    @endif
                </td>
                <td class="text-right">
                    {{ number_format((float)$charge->AmtInBaseCurr, 2) }}
                </td>
                <td class="text-center"></td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center" style="color:#999; font-style:italic;">
                    No charges
                </td>
            </tr>
        @endforelse

        {{-- TOTAL ROW --}}
        <tr class="total-row">
            <td colspan="5" class="text-right" style="padding-right:8px;">Total</td>
            <td class="text-right">{{ number_format($totalRevenue, 2) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

{{-- ══════════════════════════════════════════
     TERMS & CONDITIONS
══════════════════════════════════════════ --}}
@if(!empty($quotation->terms_condition))
    <div class="terms-heading">Terms &amp; Condition</div>
    <div class="terms-body">
        {!! nl2br(e($quotation->terms_condition)) !!}
    </div>
@endif

{{-- ADDITIONAL TERMS FOOTER --}}
<div class="additional-terms">Additional Terms &amp; Condition</div>

</body>
</html>
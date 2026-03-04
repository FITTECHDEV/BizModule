<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Quotation {{ $quotation->bookingno ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #000;
            padding: 18px 24px;
        }

        table {
            border-spacing: 0;
            width: 100%;
        }

        td {
            line-height: 1.35;
            vertical-align: top;
        }

        /* HEADER */
        .header-table {
            border-bottom: 1.5px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-company {
            font-size: 11px;
            line-height: 1.4;
            padding-left: 8px;
        }

        .header-company strong {
            font-size: 12px;
        }

        .quotation-title {
            text-align: right;
        }

        .title-label {
            font-size: 17px;
            font-weight: 700;
        }

        .booking-no {
            font-size: 17px;
            font-weight: 700;
        }

        /* INFO BLOCK */
        .info-table {
            margin-bottom: 6px;
        }

        .info-table {
            font-size: 10.5px;
            line-height: 1.4;
        }

        .info-table strong {
            font-weight: 700;
        }

        .info-table td {
            padding: 3px 6px 3px 0;
            font-size: 11.5px;
        }

        .to-label {
            font-weight: 700;
            padding-right: 6px;
        }

        .field-label {
            font-weight: 700;
            white-space: nowrap;
        }

        /* SHIPMENT STRIP */
        .shipment-row {
            border-top: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
            margin: 6px 0;
        }

        .shipment-row td {
            padding: 4px 6px;
        }

        /* SECTION HEADER */
        .section-header {
            background: #000;
            color: #fff;
            font-weight: 700;
            font-size: 11.5px;
            padding: 3px 7px;
            margin: 10px 0 4px 0;
        }

        /* DETAILS TABLE */
        .section-details {
            margin-bottom: 8px;
            font-size: 10px;
        }

        .section-details td {
            font-size: 10px;
            padding: 2px 5px;
        }

        .section-details .lbl {
            color: #444;
            width: 23%;
            font-weight: 700;
            white-space: nowrap;
        }

        .section-details .val {
            width: 28%;
        }

        /* CHARGES TABLE */
        .charges-table {
            border-collapse: collapse;
            margin-top: 8px;
        }

        .charges-table th {
            background: #ececec;
            border: 1px solid #666;
            padding: 5px 6px;
            font-size: 11px;
            font-weight: 700;
            text-align: left;
        }

        .charges-table td {
            border: 1px solid #777;
            padding: 4px 6px;
            font-size: 11px;
        }

        .col-charge {
            width: 22%
        }

        .col-based {
            width: 14%
        }

        .col-rate {
            width: 14%
        }

        .col-chgamt {
            width: 10%;
            text-align: right
        }

        .col-exrate {
            width: 10%;
            text-align: right
        }

        .col-quoteamt {
            width: 12%;
            text-align: right
        }

        .col-tax {
            width: 8%;
            text-align: right
        }

        /* TOTAL */
        .total-row td {
            border-top: 2px solid #000;
            font-weight: 700;
            font-size: 12px;
            padding: 5px 6px;
        }

        /* TERMS */
        .terms-section {
            margin-top: 12px;
            font-size: 10.5px;
            line-height: 1.4;
        }

        .terms-title {
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 4px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .terms-content {
            font-size: 10.5px;
            line-height: 1.6;
        }

        .terms-content p {
            margin: 0 0 6px 0;
        }

        .terms-content ol,
        .terms-content ul {
            margin: 4px 0 6px 0;
            padding-left: 20px;
        }

        .terms-content li {
            margin-bottom: 4px;
        }

        .terms-content strong,
        .terms-content b {
            font-weight: 700;
        }

        .terms-content h1,
        .terms-content h2,
        .terms-content h3 {
            font-size: 11px;
            font-weight: 700;
            margin: 6px 0 4px 0;
        }

        .additional-terms-title {
            font-weight: 700;
            font-size: 12px;
            margin-top: 12px;
            margin-bottom: 4px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        /* GENERAL INFO */
        .general-info {
            margin: 6px 0 8px 0;
            font-size: 10px;
        }

        .general-info td {
            padding: 3px 8px 3px 0;
            font-size: 10px;
        }

        .general-info .field-label {
            font-weight: 700;
            width: 110px;
            white-space: nowrap;
        }
    </style>
</head>

<body>

    {{-- ══════════════════════════════════════
        HEADER — Logo + Company + Quotation Title
    ══════════════════════════════════════ --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:55%; vertical-align:middle;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align:middle; padding-right:8px;">
                            <img src="{{ 'file://' . str_replace('\\', '/', public_path('img/logo.png')) }}"
                                style="height:42px;">
                        </td>
                        <td class="header-company">
                            <strong>FREIGHT IN TIME (U) LIMITED</strong><br>
                            P.O. BOX 70942, OLD PORT BELL ROAD,<br>
                            PLOT 20, KAMPALA, UGANDA
                        </td>
                    </tr>
                </table>
            </td>
            <td class="quotation-title">
                <div class="title-label">Quotation For</div>
                <div class="booking-no">{{ $quotation->bookingno ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
        CLIENT + DATE + CURRENCY BLOCK
    ══════════════════════════════════════ --}}
    <table class="info-table" cellpadding="0" cellspacing="0"
        style="width:100%; margin:6px 0 10px 0; font-size:10.5px;">
        <tr>
            <td style="vertical-align:top; width:45%;">
                <strong>To</strong> {{ $quotation->companyname ?? '' }}
            </td>
            <td style="vertical-align:top; width:25%; text-align:center;">
                <strong>Date</strong> {{ $generatedDate ?? '' }}
            </td>
            <td style="vertical-align:top; width:30%; text-align:right;">
                <strong>Curr.</strong> {{ $currencyCode ?? '' }}
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
     PLACE OF RECEIPT / DELIVERY / WEIGHTS
     ══════════════════════════════════════ --}}
    <div class="section-header">GENERAL INFORMATION</div>

    <table class="general-info" cellpadding="0" cellspacing="0">
        <tr>
            <td class="field-label">Place of Receipt</td>
            <td>{{ $quotation->placeofreceipt ?? $quotation->origincountry ?? '' }}</td>
            <td class="field-label">Place of Delivery</td>
            <td>{{ $quotation->placeofdelivery ?? $quotation->destinationcountry ?? '' }}</td>
            <td class="field-label">Movement Type</td>
            <td>{{ $quotation->TransportMode ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">Shipment Type</td>
            <td>{{ $quotation->shipmentmode ?? '' }}</td>
            <td class="field-label">Inco Term</td>
            <td>{{ $quotation->incoterms ?? '' }}</td>
            <td class="field-label">Payment Terms</td>
            <td>{{ $quotation->paymentterms ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">Gross Wt.</td>
            <td>{{ number_format($quotation->grossweight ?? 0, 2) }}</td>
            <td class="field-label">Chargeable Wt.</td>
            <td>{{ number_format($quotation->chargeableweight ?? 0, 2) }}</td>
            <td class="field-label">Volume</td>
            <td>{{ number_format($quotation->volume ?? 0, 3) }} m³</td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
     SEA-IMPORT / SERVICE SECTION HEADER
     ══════════════════════════════════════ --}}
    <div class="section-header">
        {{ strtoupper($quotation->ServiceVerticalname ?? '') }}
    </div>

    {{-- Section detail rows --}}
    <table class="section-details" cellpadding="0" cellspacing="0">
        <tr>
            <td class="lbl">Owner Office</td>
            <td class="val">{{ $quotation->teamname ?? '' }}</td>
            <td class="lbl">Executing Office</td>
            <td class="val">{{ $quotation->executingoffice ?? $quotation->teamname ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Place of Loading</td>
            <td class="val">{{ $quotation->portofloading ?? '' }}</td>
            <td class="lbl">Equipment</td>
            <td class="val">{{ $quotation->containerlist ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Place of Discharge</td>
            <td class="val">{{ $quotation->portofdischarge ?? $quotation->origincountry ?? '' }}</td>
            <td class="lbl">Vehicle</td>
            <td class="val">{{ $quotation->vehicle ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Carrier Name</td>
            <td class="val">{{ $quotation->carriername ?? '' }}</td>
            <td class="lbl">Master No.</td>
            <td class="val">{{ $quotation->masterno ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Service Class</td>
            <td class="val">{{ $quotation->ServiceClass ?? '' }}</td>
            <td class="lbl">House No.</td>
            <td class="val">{{ $quotation->houseno ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">ETD</td>
            <td class="val">
                {{ $quotation->etd ? \Carbon\Carbon::parse($quotation->etd)->format('d-m-Y') : '' }}
            </td>
            <td class="lbl">ETA</td>
            <td class="val">
                {{ $quotation->eta ? \Carbon\Carbon::parse($quotation->eta)->format('d-m-Y') : '' }}
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
     CHARGES TABLE
     ══════════════════════════════════════ --}}
    <table class="charges-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="col-charge">Charge Description<br>Activity</th>
                <th class="col-based">Based on Qty<br>&amp; UOM</th>
                <th class="col-rate">Rate &amp; Curr.<br>Min. Amt.</th>
                <th class="col-chgamt">Charge Amt.</th>
                <th class="col-exrate">Ex. Rate</th>
                <th class="col-quoteamt">Quote Amt.</th>
                <th class="col-tax">Tax</th>
            </tr>
        </thead>
        <tbody>
            @forelse($revenueCharges as $charge)
                <tr>
                    <td class="col-charge">{{ $charge->ChargeName ?? $charge->ChargeCode ?? '' }}</td>
                    <td class="col-based">
                        {{ $charge->BasedOn ?? $charge->BasedOnCode ?? '' }}<br>
                        {{ $charge->BasedOnUOM ?? '' }}
                    </td>
                    <td class="col-rate">
                        {{ number_format((float) ($charge->Rate ?? 0), 2) }}
                        {{ $charge->ChgCurr ?? '' }}<br>
                        {{ number_format((float) ($charge->Quantity ?? 0), 2) }}
                    </td>
                    <td class="col-chgamt">
                        {{ number_format((float) ($charge->AmtInChgCurr ?? 0), 2) }}
                    </td>
                    <td class="col-exrate">
                        {{ $charge->ExChangeRate ? number_format((float) $charge->ExChangeRate, 2) : '' }}
                    </td>
                    <td class="col-quoteamt">
                        {{ number_format((float) ($charge->AmtInBaseCurr ?? 0), 2) }}
                    </td>
                    <td class="col-tax">
                        {{ $charge->Tax ?? '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#888; padding:8px;">
                        No charges available
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align:right; border:1px solid #555;">
                    <strong>Total</strong>
                </td>
                <td class="col-quoteamt" style="border:1px solid #555;">
                    <strong>{{ number_format($totalRevenue ?? 0, 2) }}</strong>
                </td>
                <td class="col-tax" style="border:1px solid #555;"></td>
            </tr>
        </tfoot>
    </table>


    {{-- ══════════════════════════════════════
     TERMS & CONDITIONS — from chosen template only
     Static boilerplate removed. A T&C template must
     be selected before generating the PDF, so
     $termsCondition is guaranteed to be present here.
    ══════════════════════════════════════ --}}
    @if (!empty($termsCondition))
        <div class="terms-section" style="margin-top:14px;">
            <div class="terms-content">{!! $termsCondition !!}</div>
        </div>
    @endif

</body>

</html>
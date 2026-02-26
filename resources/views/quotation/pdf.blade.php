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
            font-size: 11.5px;
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
            font-size: 12.5px;
            padding: 4px 7px;
            margin: 8px 0 5px 0;
        }

        /* DETAILS TABLE */
        .section-details {
            margin-bottom: 8px;
        }

        .section-details td {
            font-size: 11.5px;
            padding: 3px 5px;
        }

        .section-details .lbl {
            color: #555;
            width: 22%;
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
            white-space: pre-wrap;
        }

        .additional-terms-title {
            font-weight: 700;
            font-size: 12px;
            margin-top: 12px;
            margin-bottom: 4px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
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
                            <img src="{{ public_path('logo.png') }}" style="height:42px;">
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
                <div class="booking-no">{{ $quotation->bookingno ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
     CLIENT + DATE + CURRENCY BLOCK
     ══════════════════════════════════════ --}}
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Left: To + Address --}}
            <td class="to-block">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="to-label">To</td>
                        <td class="to-address">
                            {{ $quotation->companyname ?? '' }}<br>
                            @if ($quotation->pri_partner_street ?? null)
                                {{ $quotation->pri_partner_street }}<br>
                            @endif
                            @if ($quotation->pri_partner_street2 ?? null)
                                {{ $quotation->pri_partner_street2 }}<br>
                            @endif
                            @if ($quotation->pri_partner_city ?? null)
                                {{ $quotation->pri_partner_city }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Right: Date / Client Name / Curr --}}
            <td class="date-block">
                <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                        <td style="width:30%; padding:2px 6px;">
                            <span class="field-label">Date</span>
                        </td>
                        <td style="padding:2px 6px;">
                            {{ $generatedDate ?? now()->format('d-m-Y') }}
                        </td>
                        <td style="padding:2px 6px;">
                            <span class="field-label">Curr.</span>
                        </td>
                        <td style="padding:2px 6px; font-weight:700;">
                            {{ $currencyCode ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:2px 6px;">
                            <span class="field-label">Client Name</span>
                        </td>
                        <td colspan="3" style="padding:2px 6px;">
                            {{ $quotation->companyname ?? 'N/A' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
     PLACE OF RECEIPT / DELIVERY / WEIGHTS
     ══════════════════════════════════════ --}}
    <table style="width:100%; margin:4px 0;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:50%; font-size:9px; padding:2px 4px;">
                <span class="field-label">Place of Receipt</span>&nbsp;&nbsp;
                {{ $quotation->placeofreceipt ?? ($quotation->origincountry ?? 'N/A') }}
            </td>
            <td style="width:50%; font-size:9px; padding:2px 4px;">
                <span class="field-label">Movement Type</span>&nbsp;&nbsp;
                {{ $quotation->TransportMode ?? '' }}
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="field-label">Shipment Type</span>&nbsp;&nbsp;
                {{ $quotation->shipmentmode ?? 'DIRECT' }}
            </td>
        </tr>
        <tr>
            <td style="font-size:9px; padding:2px 4px;">
                <span class="field-label">Place of Delivery</span>&nbsp;&nbsp;
                {{ $quotation->placeofdelivery ?? ($quotation->destinationcountry ?? 'N/A') }}
            </td>
            <td style="font-size:9px; padding:2px 4px;">
                <span class="field-label">Inco Term</span>&nbsp;&nbsp;
                {{ $quotation->incoterms ?? '' }}
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="field-label">Payment Terms</span>&nbsp;&nbsp;
                {{ $quotation->paymentterms ?? '' }}
            </td>
        </tr>
        <tr>
            <td colspan="2"
                style="font-size:9px; padding:2px 4px; border-top:0.5px solid #999; border-bottom:0.5px solid #999;">
                <span class="field-label">Gross Wt.</span>&nbsp;
                {{ number_format($quotation->grossweight ?? 0, 2) }}
                &nbsp;&nbsp;&nbsp;
                <span class="field-label">Chargeable Wt.</span>&nbsp;
                {{ number_format($quotation->chargeableweight ?? 0, 2) }}
                &nbsp;&nbsp;&nbsp;
                <span class="field-label">Volume</span>&nbsp;
                {{ number_format($quotation->volume ?? 0, 3) }}
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════
     SEA-IMPORT / SERVICE SECTION HEADER
     ══════════════════════════════════════ --}}
    <div class="section-header">
        {{ strtoupper($quotation->ServiceVerticalname ?? 'SEA - IMPORT') }}
    </div>

    {{-- Section detail rows --}}
    <table class="section-details" cellpadding="0" cellspacing="0">
        <tr>
            <td class="lbl">Owner Office</td>
            <td class="val">{{ $quotation->teamname ?? 'N/A' }}</td>
            <td class="lbl">Executing Office</td>
            <td class="val">{{ $quotation->executingoffice ?? ($quotation->teamname ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="lbl">Place of Loading</td>
            <td class="val">{{ $quotation->portofloading ?? '' }}</td>
            <td class="lbl">Equipment</td>
            <td class="val">{{ $quotation->containerlist ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Place of Discharge</td>
            <td class="val">{{ $quotation->portofdischarge ?? ($quotation->origincountry ?? '') }}</td>
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
                    <td class="col-charge">{{ $charge->ChargeName ?? ($charge->ChargeCode ?? 'N/A') }}</td>
                    <td class="col-based">
                        {{ $charge->BasedOn ?? ($charge->BasedOnCode ?? '') }}<br>
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
     TERMS & CONDITIONS
     ══════════════════════════════════════ --}}
    @if (!empty($quotation->terms_condition))
        <div class="terms-section">
            <div class="terms-title">Terms &amp; Condition</div>
            <div class="terms-content">{{ $quotation->terms_condition }}</div>
        </div>
    @endif

    {{-- Additional Terms placeholder --}}
    <div class="additional-terms-title">Additional Terms &amp; Condition</div>

</body>

</html>

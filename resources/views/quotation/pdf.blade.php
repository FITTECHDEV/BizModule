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
     Static boilerplate (a–t) with the storage
     rate table (g) driven by $storageRates.

     To override storage rates from the controller:
         $storageRates = [
             ['days' => 'Day 1-9',   '20ft' => null,  '40ft' => null],
             ['days' => 'Day 10-21', '20ft' => 20.00, '40ft' => 40.00],
             ['days' => 'From 22',   '20ft' => 40.00, '40ft' => 80.00],
         ];
     ══════════════════════════════════════ --}}
    <div class="terms-section">
        <div class="terms-title">Terms &amp; Condition</div>

        @php
            // Default storage rates — override by passing $storageRates from the controller
            $storageRates = $storageRates ?? [
                ['days' => 'Day 1-9',   '20ft' => null,  '40ft' => null],
                ['days' => 'Day 10-21', '20ft' => 20.00, '40ft' => 40.00],
                ['days' => 'From 22',   '20ft' => 40.00, '40ft' => 80.00],
            ];
        @endphp

        <table cellpadding="0" cellspacing="0" style="width:100%; font-size:10.5px; line-height:1.6;">
            <tr>
                <td style="padding:1px 0; vertical-align:top; width:18px;">a)</td>
                <td style="padding:1px 0;">Service charges in Kenya marked with * are subject to 16% VAT</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">b)</td>
                <td style="padding:1px 0;">Kindly be informed that some shipping lines e.g. MAERSK / Safmarine are charging an additional equipment management fees of up to USD 50 for 20ft and USD 100 for 40ft ctrs. in transit.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">c)</td>
                <td style="padding:1px 0;">Kindly note that this quotation is only valid if a container does not contain more than 4 different items / HS codes and can be verified at the border (Express Clearance). If a container contains 5 or more items / HS codes it will be transferred to the ICD in Mombasa and additional charges will incur.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">d)</td>
                <td style="padding:1px 0;">This quotation does not include port/terminal storage, container demurrage and possible damage repair charges or truck detention fees if any.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">e)</td>
                <td style="padding:1px 0;">Kindly note that should any of these charges or payments of government duties &amp; taxes, custom scanning fee, storage at customs warehouse etc. be incurred, these costs will be at cost to consignee's account and are due in advance.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">f)</td>
                <td style="padding:1px 0;">Port Storage Charges at the Mombasa for containerized transit imports (no free time allowed for Hazardous and Flat Rack ctrs.)</td>
            </tr>
            <tr>
                <td style="padding:4px 0 2px 0; vertical-align:top;">g)</td>
                <td style="padding:4px 0 2px 0;">
                    Storage Amount in USD
                    <table cellpadding="0" cellspacing="0" style="margin-top:4px; border-collapse:collapse; font-size:10.5px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #666; padding:3px 14px 3px 6px; text-align:left; background:#f0f0f0;">Days</th>
                                <th style="border:1px solid #666; padding:3px 20px 3px 6px; text-align:right; background:#f0f0f0;">20ft</th>
                                <th style="border:1px solid #666; padding:3px 20px 3px 6px; text-align:right; background:#f0f0f0;">40ft</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($storageRates as $row)
                            <tr>
                                <td style="border:1px solid #777; padding:2px 14px 2px 6px;">{{ $row['days'] }}</td>
                                <td style="border:1px solid #777; padding:2px 20px 2px 6px; text-align:right;">
                                    {{ isset($row['20ft']) && $row['20ft'] !== null ? number_format($row['20ft'], 2) : '-' }}
                                </td>
                                <td style="border:1px solid #777; padding:2px 20px 2px 6px; text-align:right;">
                                    {{ isset($row['40ft']) && $row['40ft'] !== null ? number_format($row['40ft'], 2) : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">h)</td>
                <td style="padding:1px 0;">Kindly note that after the allowed free storage additional remarshalling charges of USD 110 and USD 165 will be incurred for 20' and 40' Ctrs. respectively</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">i)</td>
                <td style="padding:1px 0;">Demurrage free time from the Shipping Lines for transit import to Uganda are approx. 30 days. The demurrage charges thereafter also depend on the carrier and daily charges are getting higher over time.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">j)</td>
                <td style="padding:1px 0;">Truck detention free time for transit imports are 2 days at the border and 2 days for offloading at final destination. Thereafter detention charge of USD 200/ truck / day will be incurred.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">k)</td>
                <td style="padding:1px 0;">Port Handling charges are 10% higher for hazardous goods.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">l)</td>
                <td style="padding:1px 0;">Consignee shall arrange, at its cost, insurance coverage during transportation of the cargo from the Port of Mombasa to the final destination. Insurance can be arranged upon request</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">m)</td>
                <td style="padding:1px 0;">Kindly note: As per regulation of the Kenyan Road Authority truck payload is limited to 27tons Gr. Wt. (incl container tare weight). If a 20' ctr. not be combined with a second 20' ctr. (due to availability of single 20' or weight limitations - note that there is also weight limitations per axle) additional transportation charges will be incurred that may amount up to the charge for a full truck / 40' ctr.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">n)</td>
                <td style="padding:1px 0;">Client / consignee is responsible for arranging the mandatory Pre-Shipment Inspection (PSI) and has to provide all documents that are required for clearance at destination well in advance, best 5 days prior to shipment arrival - including in particular Certificate of Conformity (COC) and all other necessary permits (depending on the nature of the goods).</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">o)</td>
                <td style="padding:1px 0;">Shipment related documents shall be provided to FiT before production / confirmation of original set of documents for approval.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">p)</td>
                <td style="padding:1px 0;">Kindly note that the bond fee will only be applicable for goods for which single customs territory is not in full effect yet and these charges will be obsolete there after.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">q)</td>
                <td style="padding:1px 0;">This quotation does not include unloading charges. We assume that unloading will be arranged by the consignee.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">r)</td>
                <td style="padding:1px 0;">Payment of third-party costs are due in advance, the outstanding balance at arrival at final destination.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">s)</td>
                <td style="padding:1px 0;">For all additional third-party costs upfronted by FIT a disbursement charge of 5% is applicable.</td>
            </tr>
            <tr>
                <td style="padding:1px 0; vertical-align:top;">t)</td>
                <td style="padding:1px 0;">Please take note that for DG cargo storage starts immediately on arrival at port. DGR Fees per container per day $44-20ft &amp; USD66-40ft from 1-9days. Containers is expected to move within 9 days if taxes are paid on time. So it's important when quoting to allow for storage of 7-9 days at the port or advise your client.</td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════
     ADDITIONAL TERMS — from the T&C template
     applied via the "Apply Terms" button.
     Only rendered when a template was saved.
     ══════════════════════════════════════ --}}
    @if (!empty($quotation->terms_condition))
        <div class="terms-section" style="margin-top:14px;">
            <div class="additional-terms-title">Additional Terms &amp; Condition</div>
            <div class="terms-content">{{ $quotation->terms_condition }}</div>
        </div>
    @else
        <div class="additional-terms-title" style="margin-top:14px;">Additional Terms &amp; Condition</div>
    @endif

</body>

</html>
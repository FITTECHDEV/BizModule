{{-- resources/views/quotation/pdf_document.blade.php --}}
{{-- Renders sp_Quote_QuotationFormat JSON (+ sp_Quote_QuotationFormat_Data for equipment) in the previous PDF layout. --}}
{{-- Dompdf supports CSS 2.1 only: tables, no flex/grid. --}}
@php
    $office = $quote['Office'] ?? [];
    $customer = $quote['Customer'] ?? [];
    $info = $quote['QuoteInfo'] ?? [];
    $cargo = $quote['CargoDetails'] ?? [];
    $charges = collect($quote['ChargeDetails'] ?? []);

    // Equipment table: codes come from the "<Code> Rate" columns; values are pre-formatted by the SP.
    $equipmentCodes = $equipment['codes'] ?? [];
    $equipmentRows = collect($equipment['rows'] ?? []);
    $hasEquipment = $equipmentRows->isNotEmpty();
    $equipmentColumns = 1 + count($equipmentCodes) * ($showVat ? 3 : 2);

    $money = fn($value) => number_format((float) $value, 2);
    $chargeColumns = 6;

    // Rows with no VAT carry no VATAmount key (FOR JSON PATH omits NULLs).
    $totalAmount = $charges->sum(fn($charge) => (float) ($charge['Amount'] ?? 0));
    $totalVat = $charges->sum(fn($charge) => (float) ($charge['VATAmount'] ?? 0));

    $addressLines = fn(array $party): array => array_values(
        array_filter(
            [
                trim($party['Address1'] ?? ''),
                trim($party['Address2'] ?? ''),
                trim(($party['City'] ?? '') . ($party['State'] ?? '') . ($party['Pin'] ?? '')),
                trim($party['Country'] ?? ''),
            ],
            fn($line) => trim($line, ' -') !== '',
        ),
    );
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quote['QuoteNo'] ?? '' }}</title>
    <style>
        @page {
            margin: 30px 35px 42px 35px;

            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-family: Arial, Helvetica, sans-serif;
                font-size: 8px;
                color: #555;
            }
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .right {
            text-align: right;
        }

        .header td {
            vertical-align: top;
        }

        .title {
            font-size: 15px;
            font-weight: bold;
        }

        .rule {
            border-bottom: 1px solid #000;
            height: 6px;
        }

        .bar {
            background: #000;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 4px 8px;
            margin-top: 8px;
        }

        .info td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 8.5px;
        }

        .info .label {
            font-weight: bold;
            color: #444;
            width: 24%;
            white-space: nowrap;
        }

        .charges {
            margin-top: 6px;
            table-layout: fixed;
        }

        .charges th,
        .charges td {
            border: 1px solid #444;
            padding: 3px 4px;
            vertical-align: top;
            font-size: 8.5px;
            word-wrap: break-word;
        }

        .charges th {
            background: #eee;
            text-align: left;
        }

        .charges tr {
            page-break-inside: avoid;
        }

        /* Fixed grid so colspan rows (category headers, totals) line up with
           the header row instead of being auto-sized independently. */
        .charges.non-equipment th:nth-child(1) {
            width: 30%;
        }

        .charges.non-equipment th:nth-child(2) {
            width: 20%;
        }

        .charges.non-equipment th:nth-child(3) {
            width: 12%;
        }

        .charges.non-equipment th:nth-child(4) {
            width: 19%;
        }

        .charges.non-equipment th:nth-child(5) {
            width: 19%;
        }

        .charges.non-equipment th:nth-child(6) {
            width: 14%;
        }

        .charges.equipment th:first-child {
            width: 28%;
        }

        .charges .category td {
            background: #513D6C;
            color: #fff;
            font-weight: bold;
        }

        .charges .qty td {
            background: #f2f2f2;
            font-weight: bold;
        }

        .charges .total td {
            font-weight: bold;
            font-size: 10px;
        }

        .remarks {
            color: #666;
            font-size: 8px;
        }

        .terms {
            margin-top: 10px;
            font-size: 9px;
        }

        /* Force uniform sizing regardless of inline font-size in the
           admin-maintained Cust_CommunicationDetails HTML. */
        .terms,
        .terms * {
            font-size: 9px !important;
            line-height: 1.4;
        }

        .terms table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .terms table td,
        .terms table th {
            border: 1px solid #444;
            padding: 3px 5px;
        }
    </style>
</head>

<body>

    {{-- Header: logo | office | quote number --}}
    <table class="header">
        <tr>
            <td style="width: 22%;">
                @if (is_file($logoPath))
                    <img src="{{ $logoPath }}" style="height: 80px; width: auto;" alt="Logo">
                @endif
            </td>
            <td style="width: 40%;">
                <strong>{{ strtoupper($office['Name'] ?? '') }}</strong><br>
                @foreach ($addressLines($office) as $line)
                    {{ $line }}<br>
                @endforeach
            </td>
            <td class="right title">
                Quotation For<br>
                {{ $quote['QuoteNo'] ?? '' }}
            </td>
        </tr>
    </table>
    <div class="rule"></div>

    {{-- To / Date / Currency --}}
    <table class="header" style="margin-top: 6px;">
        <tr>
            <td style="width: 42%;">
                <strong>To</strong><br>
                <strong>{{ $customer['Name'] ?? '' }}</strong><br>
                @foreach ($addressLines($customer) as $line)
                    <strong>{{ $line }}</strong><br>
                @endforeach
            </td>
            <td>
                <strong>Date</strong> {{ $quote['QuoteDate'] ?? '' }}
            </td>
            <td class="right">
                <strong>Curr.</strong> {{ $quote['QuoteCurr'] ?? '' }}
            </td>
        </tr>
    </table>

    {{-- General information --}}
    <div class="bar">GENERAL INFORMATION</div>
    <table class="info">
        <tr>
            <td class="label">Country Of Origin</td>
            <td>{{ $info['OriginCountry'] ?? '' }}</td>
            <td class="label">Country Of Destination</td>
            <td>{{ $info['DestinationCountry'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Movement Type</td>
            <td>{{ $info['MovementType'] ?? '' }}</td>
            <td class="label">Shipment Type</td>
            <td>{{ $info['ShipmentMode'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">InCo Term</td>
            <td>{{ $info['IncoTerm'] ?? '' }}</td>
            <td class="label">Service Class</td>
            <td>{{ $info['ServiceClass'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Office</td>
            <td>{{ $info['Office'] ?? '' }}</td>
            <td class="label">Sales Person</td>
            <td>{{ $info['SalesPerson'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Commodity</td>
            <td>{{ $info['Commodity'] ?? '' }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    {{-- Cargo information --}}
    <div class="bar">CARGO INFORMATION</div>
    <table class="info">
        <tr>
            <td><strong>Weight in (KGS)</strong> &nbsp; {{ $money($cargo['CargoWeight'] ?? 0) }}</td>
            <td><strong>Volume in (CBM)</strong> &nbsp; {{ $money($cargo['CargoVolume'] ?? 0) }}</td>
            <td><strong>Container</strong> &nbsp; {{ $cargo['Container'] ?? '' }}</td>
        </tr>
    </table>

    {{-- Service bar --}}
    <div class="bar">{{ $quote['Service'] ?? 'Charges' }}</div>

    {{-- Equipment-based charges --}}
    @if ($hasEquipment)
        <table class="charges equipment">
            <thead>
                <tr>
                    <th>Charge Description<br>Activity</th>
                    @foreach ($equipmentCodes as $code)
                        <th class="right">Rate</th>
                        <th class="right">{{ $code }}</th>
                        @if ($showVat)
                            <th class="right">{{ $code }} VAT</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $lastCategory = null; @endphp
                @foreach ($equipmentRows as $row)
                    @php
                        $type = trim($row['ChargeCatagories'] ?? '');
                        $isQuantity = $type === 'No of Container';
                        $isSubTotal = $type === 'Sub Total';
                        $isTotal = $type === 'Total';
                    @endphp
                    {{-- Without VAT the old layout showed one Total (the sum of amounts), which is the SP's Sub Total row. --}}
                    @continue($isTotal && !$showVat)
                    @if (!$isQuantity && !$isSubTotal && !$isTotal && $type !== '' && $type !== $lastCategory)
                        @php $lastCategory = $type; @endphp
                        <tr class="category">
                            <td colspan="{{ $equipmentColumns }}">{{ $type }}</td>
                        </tr>
                    @endif
                    <tr class="{{ $isQuantity ? 'qty' : ($isSubTotal || $isTotal ? 'total' : '') }}">
                        <td>
                            @if ($isQuantity)
                                Quantity
                            @elseif ($isSubTotal)
                                {{ $showVat ? 'Sub Total' : 'Total' }}
                            @elseif ($isTotal)
                                Total
                            @else
                                {{ $row['Description'] ?? '' }}
                            @endif
                        </td>
                        @foreach ($equipmentCodes as $code)
                            <td class="right">{{ $row[$code . ' Rate'] ?? '' }}</td>
                            <td class="right">
                                {{ $isQuantity && ($row[$code] ?? '') !== '' ? (int) str_replace(',', '', $row[$code]) : $row[$code] ?? '' }}
                            </td>
                            @if ($showVat)
                                <td class="right">{{ $row[$code . ' VAT'] ?? '' }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Non-equipment charges, grouped by ChargeCatagory --}}
    @if ($hasEquipment && $charges->isNotEmpty())
        <p style="margin: 10px 0 2px;"><strong>Non Equipment Based Charges</strong></p>
    @endif
    @if ($charges->isNotEmpty() || !$hasEquipment)
        <table class="charges non-equipment">
            <thead>
                <tr>
                    <th>Charge Description<br>Activity</th>
                    <th>Based on<br>&amp; UOM</th>
                    <th class="right">Qty.</th>
                    <th class="right">Rate</th>
                    <th class="right">Quote Amt.</th>
                    <th>VAT</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chargeGroups as $category => $rows)
                    @if ((string) $category !== '')
                        <tr class="category">
                            <td colspan="{{ $chargeColumns }}">{{ $category }}</td>
                        </tr>
                    @endif
                    @foreach ($rows as $charge)
                        <tr>
                            <td>
                                {{ $charge['ChargeDescription'] ?? '' }}
                                @if (!empty($charge['Remarks']))
                                    <div class="remarks">{{ $charge['Remarks'] }}</div>
                                @endif
                            </td>
                            <td>{{ $charge['BasedOn'] ?? '' }}</td>
                            <td class="right">{{ $money($charge['Quantity'] ?? 0) }}</td>
                            <td class="right">{{ $money($charge['Rate'] ?? 0) }}</td>
                            <td class="right">{{ $money($charge['Amount'] ?? 0) }}</td>
                            <td>
                                @if ($showVat && isset($charge['VATRate']))
                                    {{ !empty($charge['VATDescription']) ? $charge['VATDescription'] . ' @ ' : '' }}{{ $money($charge['VATRate']) }}%
                                @endif
                                @if ($showVat && isset($charge['VATAmount']))
                                    <br>{{ $money($charge['VATAmount']) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ $chargeColumns }}">No charges available.</td>
                    </tr>
                @endforelse
                @if ($charges->isNotEmpty())
                    @if ($totalVat > 0)
                        <tr class="total">
                            <td colspan="4" class="right">Sub Total</td>
                            <td class="right">{{ $money($totalAmount) }}</td>
                            <td></td>
                        </tr>
                        <tr class="total">
                            <td colspan="4" class="right">Total</td>
                            <td class="right">{{ $money($totalAmount + $totalVat) }}</td>
                            <td class="right">{{ $money($totalVat) }}</td>
                        </tr>
                    @else
                        <tr class="total">
                            <td colspan="4" class="right">Total</td>
                            <td class="right">{{ $money($totalAmount) }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>
    @endif

    {{-- Terms & conditions --}}
    @if (!empty($quote['Terms']))
        <div class="bar">TERMS AND CONDITION</div>
        <div class="terms">
            {{-- Template HTML from Cust_CommunicationDetails (admin-maintained), so it is not escaped. --}}
            {!! $quote['Terms'] !!}
        </div>
    @endif

</body>

</html>

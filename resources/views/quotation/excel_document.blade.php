@php
    $office = $quote['Office'] ?? [];
    $customer = $quote['Customer'] ?? [];
    $info = $quote['QuoteInfo'] ?? [];
    $cargo = $quote['CargoDetails'] ?? [];
    $lines = fn(array $p) => array_values(array_filter([
        trim($p['Address1'] ?? ''), trim($p['Address2'] ?? ''),
        trim(($p['City'] ?? '') . ($p['State'] ?? '') . ($p['Pin'] ?? '')), trim($p['Country'] ?? ''),
    ], fn($l) => trim($l, ' -') !== ''));
    $bar = 'background:#000;color:#fff;font-weight:700;padding:3px 7px;';
@endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Quotation {{ $quote['QuoteNo'] ?? '' }}</title></head>
<body>
<table cellpadding="0" cellspacing="0" style="width:100%;">
    <tr>
        <td style="width:55%;">
            {{-- Logo is injected as a PhpSpreadsheet Drawing in generateExcel().
                 Keeping a blank cell here reserves the space in the header row. --}}
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width:60px;">&nbsp;</td>
                    <td>
                        <strong>{{ $office['Name'] ?? '' }}</strong><br>
                        @foreach ($lines($office) as $line){{ $line }}<br>@endforeach
                    </td>
                </tr>
            </table>
        </td>
        <td style="text-align:right;">
            <div style="font-weight:700;">Quotation For</div>
            <div style="font-weight:700;">{{ $quote['QuoteNo'] ?? '' }}</div>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" style="width:100%;">
    <tr>
        <td style="width:45%;"><strong>To</strong><br>
            <strong>{{ $customer['Name'] ?? '' }}</strong><br>
            @foreach ($lines($customer) as $line){{ $line }}<br>@endforeach
        </td>
        <td style="width:25%;text-align:center;"><strong>Date</strong> {{ $quote['QuoteDate'] ?? '' }}</td>
        <td style="width:30%;text-align:right;"><strong>Curr.</strong> {{ $quote['QuoteCurr'] ?? '' }}</td>
    </tr>
</table>
<div style="{{ $bar }}">GENERAL INFORMATION</div>
<table cellpadding="0" cellspacing="0" style="width:100%;">
    <tr><td><strong>Country Of Origin</strong></td><td>{{ $info['OriginCountry'] ?? '' }}</td><td><strong>Country Of Destination</strong></td><td>{{ $info['DestinationCountry'] ?? '' }}</td></tr>
    <tr><td><strong>Movement Type</strong></td><td>{{ $info['MovementType'] ?? '' }}</td><td><strong>Shipment Type</strong></td><td>{{ $info['ShipmentMode'] ?? '' }}</td></tr>
    <tr><td><strong>InCo Term</strong></td><td>{{ $info['IncoTerm'] ?? '' }}</td><td><strong>Service Class</strong></td><td>{{ $info['ServiceClass'] ?? '' }}</td></tr>
    <tr><td><strong>Office</strong></td><td>{{ $info['Office'] ?? '' }}</td><td><strong>Sales Person</strong></td><td>{{ $info['SalesPerson'] ?? '' }}</td></tr>
    <tr><td><strong>Commodity</strong></td><td>{{ $info['Commodity'] ?? '' }}</td></tr>
</table>
<div style="{{ $bar }}">CARGO INFORMATION</div>
<table cellpadding="0" cellspacing="0" style="width:100%;">
    <tr>
        <td><strong>Weight in (KGS)</strong></td><td>{{ number_format((float) ($cargo['CargoWeight'] ?? 0), 2) }}</td>
        <td><strong>Volume in (CBM)</strong></td><td>{{ number_format((float) ($cargo['CargoVolume'] ?? 0), 2) }}</td>
        <td><strong>Container</strong></td><td>{{ $cargo['Container'] ?? '' }}</td>
    </tr>
</table>
<div style="{{ $bar }}">{{ $quote['Service'] ?? '' }}</div>
{{-- Charge table HTML — built by QuotationController::getQuotationExcelTable() --}}
{!! $tableList !!}
@if (!empty($quote['TermParagraphs']))
    <div style="{{ $bar }}">TERMS AND CONDITION</div>
    <table cellpadding="4" cellspacing="0" style="width:100%;">
        @foreach ($quote['TermParagraphs'] as $paragraph)
            <tr>
                <td colspan="8" style="font-size:9px; line-height:1.4; vertical-align:top;">
                    {{ $paragraph }}
                </td>
            </tr>
        @endforeach
    </table>
@endif
</body>
</html>
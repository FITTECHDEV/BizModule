@extends('adminlte::page')

@section('title', 'Contract Details')

@section('content')
    <div class="container-fluid" id="contractDetailsContent">

        <div class="cc-box cc-box-compact mb-3">
            <div class="cc-box-title">
                {{ $contractInfo->ContractNo }}
                <a href="{{ route('contracts.create') }}" class="btn btn-secondary btn-sm float-right">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <div class="cc-info-grid">
                <div class="cc-info-col">
                    <div class="cc-info-row"><span class="cc-lbl">Contract Name</span><span
                            class="cc-val">{{ $contractInfo->ContractName ?? '—' }}</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Type</span><span
                            class="cc-val">{{ $contractInfo->ContractType ?? '—' }}</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Office</span><span
                            class="cc-val">{{ $contractInfo->OfficeCode ?? '—' }}</span></div>
                    <div class="cc-info-row"><span class="cc-lbl">Status</span><span
                            class="cc-val">{{ $contractInfo->Status ?? '—' }}</span></div>
                </div>
                <div class="cc-info-col">
                    <div class="cc-info-row">
                        <span class="cc-lbl">Effective From</span><span
                            class="cc-val">{{ $contractInfo->EffectiveFrom ?? '—' }}</span>
                        <span class="cc-lbl" style="margin-left:24px;">Effective To</span><span
                            class="cc-val">{{ $contractInfo->EffectiveTo ?? '—' }}</span>
                    </div>
                    <div class="cc-info-row"><span class="cc-lbl">Remark</span><span
                            class="cc-val">{{ $contractInfo->Remark ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        @if ($services->count())
            <div class="cc-section-block mb-3">
                <div class="cc-section-block-header cc-header-revenue">
                    <strong>Services</strong>
                    <span class="ml-auto cc-totals-inline">{{ $services->count() }} total</span>
                </div>

                @foreach ($services as $service)
                    <div class="cc-group">
                        <div class="cc-group-header collapsed" data-target="serviceBody{{ $loop->index }}">
                            <span class="cc-caret">&#9660;</span>
                            <span>{{ $service->Service }} — {{ $service->Process }}</span>
                            <span class="cc-group-total">{{ $service->ShipmentMode ?? '—' }}</span>
                        </div>
                        <div class="cc-group-body collapsed" id="serviceBody{{ $loop->index }}">

                            <div class="cc-info-grid" style="margin-bottom:10px;">
                                <div class="cc-info-col">
                                    <div class="cc-info-row"><span class="cc-lbl">COO</span><span
                                            class="cc-val">{{ $service->COO ?? '—' }}</span></div>
                                    <div class="cc-info-row"><span class="cc-lbl">COD</span><span
                                            class="cc-val">{{ $service->COD ?? '—' }}</span></div>
                                </div>
                                <div class="cc-info-col">
                                    <div class="cc-info-row"><span class="cc-lbl">IncoTerm</span><span
                                            class="cc-val">{{ $service->IncoTerm ?? '—' }}</span></div>
                                    <div class="cc-info-row"><span class="cc-lbl">POL</span><span
                                            class="cc-val">{{ $service->POL ?? '—' }}</span></div>
                                </div>
                                <div class="cc-info-col">
                                    <div class="cc-info-row"><span class="cc-lbl">POD</span><span
                                            class="cc-val">{{ $service->POD ?? '—' }}</span></div>
                                </div>
                            </div>

                            @if (isset($charges[$service->Id]) && $charges[$service->Id]->count())
                                <div class="cc-table-wrap">
                                    <table class="cc-table">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Based On</th>
                                                <th>UOM</th>
                                                <th>Rate Type</th>
                                                <th>Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($charges[$service->Id] as $charge)
                                                <tr>
                                                    <td>{{ $charge->ChargeCode }}</td>
                                                    <td>{{ $charge->ChargeDescription }}</td>
                                                    <td>{{ $charge->ChargeBasedon }}</td>
                                                    <td>{{ $charge->ChargeBasedOnUOM }}</td>
                                                    <td>{{ $charge->RateType }}</td>
                                                    <td>
                                                        @if ($charge->RateType === 'Single')
                                                            {{ $charge->Rate }}
                                                        @elseif($charge->RateType === 'SlabBased' || $charge->RateType === 'StepUp')
                                                            @php
                                                                $slabs = $charge->RateSlabJson
                                                                    ? json_decode($charge->RateSlabJson, true)
                                                                    : null;
                                                            @endphp
                                                            {{ is_array($slabs) ? count($slabs) . ' slabs' : '—' }}
                                                        @elseif($charge->RateType === 'Formula')
                                                            {{ $charge->Formula }}
                                                        @elseif($charge->RateType === 'At Actual')
                                                            At Actual
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="cc-empty-msg">No charges for this service.</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cc-box cc-box-compact">
                <div class="cc-empty-msg">No services added.</div>
            </div>
        @endif

    </div>
@stop

@section('css')
    <style>
        .cc-box {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 16px;
            background: #fff;
        }

        .cc-box-compact {
            padding: 10px 16px;
        }

        .cc-box-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #4B49AC;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .cc-info-grid {
            display: flex;
            gap: 24px;
        }

        .cc-info-col {
            flex: 1;
        }

        .cc-info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 3px;
        }

        .cc-lbl {
            font-size: 0.78rem;
            color: #495057;
            min-width: 120px;
        }

        .cc-val {
            font-size: 0.8rem;
            color: #212529;
            font-weight: 500;
        }

        .cc-section-block {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }

        .cc-section-block-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            color: #fff;
            font-size: 0.9rem;
        }

        .cc-header-revenue {
            background: #4B49AC;
        }

        .cc-totals-inline {
            font-size: 0.8rem;
            opacity: 0.95;
        }

        .cc-group {
            border-top: 1px solid #eee;
        }

        .cc-group-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: #f4f4fb;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
            color: #4B49AC;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .cc-group-header .cc-caret {
            transition: transform 0.15s ease;
        }

        .cc-group-header.collapsed .cc-caret {
            transform: rotate(-90deg);
        }

        .cc-group-header .cc-group-total {
            margin-left: auto;
            font-weight: 500;
            text-transform: none;
            color: #495057;
        }

        .cc-group-body {
            padding: 10px 12px;
        }

        .cc-group-body.collapsed {
            display: none;
        }

        .cc-table-wrap {
            overflow-x: auto;
        }

        .cc-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.8rem;
            border-collapse: collapse;
        }

        .cc-table th,
        .cc-table td {
            border: 1px solid #dee2e6;
            padding: 5px 7px;
            vertical-align: middle;
        }

        .cc-table thead th {
            background: #fff;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.72rem;
            text-transform: uppercase;
        }

        .cc-empty-msg {
            text-align: center;
            color: #888;
            padding: 10px;
            font-size: 0.8rem;
        }

        @media (max-width: 991px) {
            .cc-info-grid {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('click', function(e) {
            var header = e.target.closest('.cc-group-header');
            if (!header) return;
            var body = document.getElementById(header.getAttribute('data-target'));
            header.classList.toggle('collapsed');
            if (body) body.classList.toggle('collapsed');
        });
    </script>
@stop

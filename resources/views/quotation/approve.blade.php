@extends('adminlte::page')

@section('title', 'Quotation Approval')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-check-double"></i> Quotation Approval</h1>
        <div style="display:flex; gap:8px;">
            @if (($quotation['status'] ?? null) !== 'Approved')
                <button type="button" id="editBtn" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </button>
            @endif
            <a href="{{ route('quotation.approvals') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card" style="border-radius:0; border:1px solid #aaa; box-shadow:2px 4px 12px rgba(0,0,0,.25);">

        {{-- Header --}}
        <div class="card-header" style="background:#4B49AC; border-bottom:none; padding:10px 16px;">
            <span style="font-size:1rem; font-weight:600; color:#fff;">
                Quotation Approval — {{ $quotation['quoteNo'] }}
            </span>
        </div>

        {{-- Body --}}
        <div class="card-body" style="padding:20px;">

            {{-- Info Grid --}}
            <div
                style="display:grid; grid-template-columns:1fr 1fr; gap:0 16px; margin-bottom:20px;
                    background:#f4f4fb; border:1px solid #dddcf5; border-radius:6px; padding:14px;">

                {{-- Left Column --}}
                <div>
                    <div class="approve-info-row">
                        <span class="approve-label">Quotation No</span>
                        <span class="approve-value">{{ $quotation['quoteNo'] }}</span>
                    </div>
                    <div class="approve-info-row">
                        <span class="approve-label">Customer</span>
                        <span class="approve-value approve-view-field">{{ $quotation['customer'] }}</span>
                        <input type="text" class="approve-edit-field form-control form-control-sm" style="display:none;"
                            value="{{ $quotation['customer'] }}" data-field="customer">
                    </div>
                    <div class="approve-info-row">
                        <span class="approve-label">Revenue</span>
                        <span class="approve-value approve-view-field">
                            {{ $quotation['currency'] }} {{ number_format($quotation['revenue'], 2) }}
                        </span>
                        <input type="number" step="0.01" class="approve-edit-field form-control form-control-sm"
                            style="display:none;" value="{{ $quotation['revenue'] }}" data-field="revenue">
                    </div>
                    <div class="approve-info-row">
                        <span class="approve-label">Cost</span>
                        <span class="approve-value approve-view-field">
                            {{ $quotation['currency'] }} {{ number_format($quotation['cost'], 2) }}
                        </span>
                        <input type="number" step="0.01" class="approve-edit-field form-control form-control-sm"
                            style="display:none;" value="{{ $quotation['cost'] }}" data-field="cost">
                    </div>
                </div>

                {{-- Right Column --}}
                <div>
                    <div class="approve-info-row">
                        <span class="approve-label">Profit</span>
                        <span class="approve-value">
                            {{ $quotation['currency'] }} {{ number_format($quotation['profit'], 2) }}
                        </span>
                    </div>
                    <div class="approve-info-row">
                        <span class="approve-label">Margin</span>
                        <span class="approve-value">{{ $quotation['margin'] }}%</span>
                    </div>
                    <div class="approve-info-row">
                        <span class="approve-label">Submitted By</span>
                        <span class="approve-value">{{ $quotation['submittedBy'] ?? '—' }}</span>
                    </div>
                    <div class="approve-info-row">
                        <span class="approve-label">Submitted Date</span>
                        <span class="approve-value">{{ $quotation['submittedDate'] ?? '—' }}</span>
                    </div>
                </div>

            </div>

            {{-- Revenue Charges --}}
            <div class="approve-charge-section" id="revenueSection" style="margin-bottom:16px;">
                <div class="approve-section-header" data-target="revenueChargesBody" data-chevron="revenueChevron">
                    Revenue Charges
                    <span class="section-summary" id="revenueChargeCount">
                        [ Total Record(s): 0 ]
                    </span>
                    <span class="show-more-link" data-tbody="approveRevenueBody" data-table="approveRevenueTable"
                        data-expanded="0">
                        <i class="fas fa-columns mr-1"></i> Show more
                    </span>
                    <i class="fas fa-chevron-down section-chevron" id="revenueChevron"></i>
                </div>
                <div id="revenueChargesBody" style="display:none;">
                    <div class="approve-charges-wrap">
                        <table class="approve-charges-table" id="approveRevenueTable">
                            <thead>
                                <tr>
                                    <th>Charge Description</th>
                                    <th>Rate</th>
                                    <th class="col-extra">Quantity</th>
                                    <th class="col-extra">Based On &amp; UOM</th>
                                    <th class="col-extra">Currency</th>
                                    <th>Amount</th>
                                    <th>Base Amount</th>
                                </tr>
                            </thead>
                            <tbody id="approveRevenueBody">
                                <tr>
                                    <td colspan="7"
                                        style="text-align:center; color:#888;
                            font-style:italic; padding:10px;">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Cost Charges --}}
            <div class="approve-charge-section" id="costSection" style="margin-bottom:20px;">
                <div class="approve-section-header" data-target="costChargesBody" data-chevron="costChevron">
                    Cost Charges
                    <span class="section-summary" id="costChargeCount">
                        [ Total Record(s): 0 ]
                    </span>
                    <span class="show-more-link" data-tbody="approveCostBody" data-table="approveCostTable"
                        data-expanded="0">
                        <i class="fas fa-columns mr-1"></i> Show more
                    </span>
                    <i class="fas fa-chevron-down section-chevron" id="costChevron"></i>
                </div>
                <div id="costChargesBody" style="display:none;">
                    <div class="approve-charges-wrap">
                        <table class="approve-charges-table" id="approveCostTable">
                            <thead>
                                <tr>
                                    <th>Charge Description</th>
                                    <th>Rate</th>
                                    <th class="col-extra">Quantity</th>
                                    <th class="col-extra">Based On &amp; UOM</th>
                                    <th class="col-extra">Currency</th>
                                    <th>Amount</th>
                                    <th>Base Amount</th>
                                </tr>
                            </thead>
                            <tbody id="approveCostBody">
                                <tr>
                                    <td colspan="7"
                                        style="text-align:center; color:#888;
                            font-style:italic; padding:10px;">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Approvers Table --}}
            <div style="margin-bottom:20px;">
                <div class="approve-section-header">
                    Approval Summary
                </div>
                <div style="border:1px solid #b8d4ea; overflow-x:auto;">
                    <table class="approve-charges-table">
                        <thead>
                            <tr>
                                <th>Approver</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- TODO: replace with real rows from sp_ApprovalStatus --}}
                            <tr>
                                <td colspan="4"
                                    style="text-align:center; color:#888;
                                font-style:italic; padding:10px;">
                                    Approver list will be available once the approval SP is available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div id="actionSection"></div>
            <div id="editActionSection" style="display:none; margin-top:12px;">
                <div style="display:flex; gap:12px; align-items:center;">
                    <button type="button" id="approveBtnEdit" class="btn btn-success"
                        style="min-width:120px; font-weight:600;">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                    <button type="button" id="rejectBtnEdit" class="btn btn-danger"
                        style="min-width:120px; font-weight:600;">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                    <button type="button" id="editChargesBtn" class="btn btn-warning"
                        style="min-width:120px; font-weight:600;">
                        <i class="fas fa-edit mr-1"></i> Edit Charges
                    </button>
                    <button type="button" id="saveEditBtn" class="btn btn-success"
                        style="display:none; min-width:120px; font-weight:600;">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <button type="button" id="cancelEditBtn" class="btn btn-secondary"
                        style="display:none; min-width:120px; font-weight:600;">
                        Cancel
                    </button>
                </div>
            </div>

            {{-- Rejection Panel --}}
            <div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background:#fff9f9;">
                            <h5 class="modal-title" style="font-size:0.95rem; font-weight:700; color:#dc3545;">
                                Rejection Details
                            </h5>
                            <button type="button" class="close" id="closeRejectionModalBtn" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejectionReason" style="font-weight:600; font-size:0.875rem;">
                                    Reason <span class="text-danger">*</span>
                                </label>
                                <div id="rejectionReasonWrapper">
                                    <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:0.82rem;">
                                        <i class="fas fa-tools mr-1"></i>
                                        Rejection reasons are not available yet — pending.
                                    </div>
                                    <select id="rejectionReason" class="form-control form-control-sm" disabled>
                                        <option value="">— Not available yet —</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="rejectionRemark" style="font-weight:600; font-size:0.875rem;">
                                    Remark <span class="text-danger">*</span>
                                </label>
                                <textarea id="rejectionRemark" class="form-control form-control-sm" rows="3"
                                    placeholder="Enter rejection remark..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancelRejectBtn" class="btn btn-secondary">
                                Cancel
                            </button>
                            <button type="button" id="confirmRejectBtn" class="btn btn-danger"
                                style="font-weight:600;">
                                <i class="fas fa-times mr-1"></i> Confirm Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Result Message --}}
            <div id="decisionResult" style="display:none; margin-top:16px;"></div>

            {{-- Hidden State --}}
            <input type="hidden" id="quotationId" value="{{ $quotation['quotationId'] }}">
            <input type="hidden" id="approverId" value="{{ $approverId }}">

        </div>

        {{-- Footer --}}
        <div
            style="display:flex; align-items:center; gap:10px; padding:10px 20px;
                background:#f8f8fd; border-top:1px solid #e8e8f5;">
            <a href="{{ route('quotation.approvals') }}" class="btn btn-secondary btn-sm">
                Close
            </a>
        </div>

    </div>
@stop

@section('css')
    <style>
        .approve-info-row {
            display: flex;
            align-items: center;
            min-height: 28px;
            gap: 8px;
            font-size: 0.875rem;
            margin-bottom: 4px;
        }

        .approve-label {
            min-width: 120px;
            font-size: 0.85rem;
            color: #555;
            white-space: nowrap;
        }

        .approve-value {
            font-size: 0.875rem;
            color: #222;
            font-weight: 500;
        }

        .approve-section-header {
            display: flex;
            align-items: center;
            background: #4B49AC;
            border: 1px solid #4B49AC;
            border-bottom: none;
            padding: 4px 8px;
            font-size: 0.875rem;
            color: #ffffff;
            gap: 8px;
            font-weight: 700;
        }

        .approve-charges-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .approve-charges-table thead tr {
            background: #e9ecef;
        }

        .approve-charges-table thead th {
            padding: 6px 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #495057;
            border: 1px solid #c8d4de;
            white-space: nowrap;
            text-align: left;
        }

        .approve-charges-table tbody td {
            padding: 6px 8px;
            border: 1px solid #dde3ea;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #222;
            white-space: nowrap;
        }

        .approve-charges-table tbody tr:hover {
            background: #f0f5fb;
        }

        .approve-charges-wrap {
            overflow-x: auto;
            border: 1px solid #b8d4ea;
            max-height: 280px;
            overflow-y: auto;
        }

        .approve-section-header {
            display: flex;
            align-items: center;
            background: #4B49AC;
            border: 1px solid #4B49AC;
            border-bottom: none;
            padding: 4px 8px;
            font-size: 0.875rem;
            color: #ffffff;
            gap: 8px;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
        }

        .approve-section-header .section-chevron {
            margin-left: auto;
            font-size: 0.75rem;
        }

        .approve-section-header .section-summary {
            font-size: 0.75rem;
            color: #d4d3f0;
            font-weight: 400;
        }

        .approve-section-header .show-more-link {
            font-size: 0.72rem;
            color: #d4d3f0;
            text-decoration: underline;
            cursor: pointer;
            white-space: nowrap;
            margin-left: 8px;
        }

        .approve-charges-table .col-extra {
            display: none;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {

            const quotationId = $('#quotationId').val();
            const approverId = $('#approverId').val();

            // Load revenue and cost charges on page load
            loadCharges();

            function loadCharges() {
                $.ajax({
                    url: '{{ route('quotation.charges') }}',
                    type: 'GET',
                    data: {
                        quotation_id: quotationId,
                        booking_no: '{{ $quotation['bookingNo'] ?? '' }}',
                        office_code: '{{ $quotation['officeCode'] ?? '' }}',
                    },
                    success: function(response) {
                        if (!response.success) {
                            showChargesError('Failed to load charges.');
                            return;
                        }
                        renderCharges('approveRevenueBody', 'revenueChargeCount', response.revenue ||
                        []);
                        renderCharges('approveCostBody', 'costChargeCount', response.cost || []);
                    },
                    error: function() {
                        showChargesError('Failed to load charges. Please refresh.');
                    }
                });
            }

            function renderCharges(tbodyId, countId, rows) {
                const $tbody = $('#' + tbodyId);
                $tbody.empty();

                $('#' + countId).text('[ Total Record(s): ' + rows.length + ' ]');

                if (!rows.length) {
                    $tbody.html(
                        '<tr><td colspan="7" style="text-align:center; color:#888; ' +
                        'font-style:italic; padding:10px;">No charges found</td></tr>'
                    );
                    return;
                }

                rows.forEach(function(charge) {
                    $tbody.append(
                        '<tr>' +
                        '<td>' + (charge.chargeName || charge.chargeCode || '') + '</td>' +
                        '<td style="text-align:right;">' +
                        '<span class="charge-view-field">' + parseFloat(charge.rate || 0).toFixed(2) +
                        '</span>' +
                        '<input type="number" step="0.01" class="charge-edit-field form-control form-control-sm" ' +
                        'style="display:none; text-align:right;" value="' + parseFloat(charge.rate ||
                            0) + '" data-charge-field="rate">' +
                        '</td>' +
                        '<td class="col-extra" style="text-align:right;">' +
                        parseFloat(charge.quantity || 0).toFixed(2) +
                        '</td>' +
                        '<td class="col-extra">' +
                        (charge.basedOnAndUOM || charge.basedOnUOM || '') +
                        '</td>' +
                        '<td class="col-extra">' +
                        (charge.chgCurr || '') +
                        '</td>' +
                        '<td style="text-align:right;">' +
                        parseFloat(charge.amtInChgCurr || 0).toFixed(2) +
                        '</td>' +
                        '<td style="text-align:right;">' +
                        parseFloat(charge.amtInBaseCurr || 0).toFixed(2) +
                        '</td>' +
                        '</tr>'
                    );
                });
            }

            function showChargesError(message) {
                const errorHtml =
                    '<tr><td colspan="4" style="text-align:center; color:#dc3545; padding:10px;">' +
                    message + '</td></tr>';
                $('#approveRevenueBody, #approveCostBody').html(errorHtml);
            }

            // Cancel rejection
            $('#cancelRejectBtn').on('click', function() {
                $('#rejectionModal').modal('hide');
                $('#rejectionRemark').val('');
                $('#rejectionReason').val('');
                $('#rejectBtnEdit').prop('disabled', false);
            });

            // Confirm Reject
            $('#confirmRejectBtn').on('click', function() {
                const remark = $('#rejectionRemark').val().trim();

                if (!remark) {
                    showResult('warning', 'Remark is required before rejecting.');
                    return;
                }

                const reason = $('#rejectionReason').val() || '';
                submitDecision('Rejected', reason, remark);
            });

            function submitDecision(decision, reason, remark) {
                $.ajax({
                    url: '/quotation/' + quotationId + '/approval-decision',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        decision: decision,
                        approver_id: approverId,
                        reason: reason,
                        remark: remark,
                    },
                    beforeSend: function() {
                        $('#approveBtn, #rejectBtn, #approveBtnEdit, #rejectBtnEdit, #confirmRejectBtn')
                            .prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            showResult('success', response.message ||
                                'Decision submitted successfully.');
                            $('#actionSection, #editActionSection').hide();
                            $('#rejectionModal').modal('hide');
                        } else {
                            showResult('danger', response.message || 'Failed to submit decision.');
                            $('#approveBtnEdit, #rejectBtnEdit, #confirmRejectBtn').prop('disabled',
                                false);;
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message ||
                            'An error occurred. Please try again.';
                        showResult('danger', message);
                        $('#approveBtnEdit, #rejectBtnEdit, #confirmRejectBtn').prop('disabled',
                            false);;
                    },
                });
            }

            function showResult(type, message) {
                $('#decisionResult')
                    .removeClass()
                    .addClass('alert alert-' + type)
                    .text(message)
                    .show();
            }

            // ── Section collapse toggle ───────────────────────────────────────────────
            $(document).on('click', '.approve-section-header', function(event) {
                // Prevent show-more link click from also triggering collapse
                if ($(event.target).hasClass('show-more-link') ||
                    $(event.target).closest('.show-more-link').length) {
                    return;
                }

                const targetId = $(this).data('target');
                const chevronId = $(this).data('chevron');
                const $body = $('#' + targetId);
                const $chevron = $('#' + chevronId);
                const isOpen = $body.is(':visible');

                $body.slideToggle(200);
                $chevron
                    .toggleClass('fa-chevron-down', isOpen)
                    .toggleClass('fa-chevron-up', !isOpen);
            });

            $('#editBtn').on('click', function() {
                window.location.href = window.location.pathname + '?mode=edit';
            });

            $('#editChargesBtn').on('click', function() {
                $(this).hide();
                $('#saveEditBtn, #cancelEditBtn').show();
                $('.approve-view-field, .charge-view-field').hide();
                $('.approve-edit-field, .charge-edit-field').show();
            });

            $('#cancelEditBtn').on('click', function() {
                $('#saveEditBtn, #cancelEditBtn').hide();
                $('#editChargesBtn').show();
                $('.approve-edit-field, .charge-edit-field').hide();
                $('.approve-view-field, .charge-view-field').show();
            });

            $('#saveEditBtn').on('click', function() {
                // fix: persistence endpoint not yet defined
                showResult('warning', 'Save is not wired yet.');
            });

            if (new URLSearchParams(window.location.search).get('mode') === 'edit') {
                $('#actionSection').hide();
                $('#editActionSection').show();
                $('#editBtn').hide();
            }

            // ── Show more / Show less columns ─────────────────────────────────────────
            $(document).on('click', '.show-more-link', function(event) {
                event.stopPropagation();

                const tableId = $(this).data('table');
                const isExpanded = parseInt($(this).data('expanded'), 10) === 1;
                const nowExpanded = !isExpanded;

                $('#' + tableId).find('.col-extra').css('display', nowExpanded ? 'table-cell' : 'none');

                $(this)
                    .data('expanded', nowExpanded ? 1 : 0)
                    .html(
                        nowExpanded ?
                        '<i class="fas fa-columns mr-1"></i> Show less' :
                        '<i class="fas fa-columns mr-1"></i> Show more'
                    );
            });

            $('#approveBtnEdit').on('click', function() {
                submitDecision('Approved', '', '');
            });

            $('#rejectBtnEdit').on('click', function() {
                $('#rejectionModal').modal('show');
            });

            $('#closeRejectionModalBtn').on('click', function() {
                $('#rejectionModal').modal('hide');
            });

        });
    </script>
@stop

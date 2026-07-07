@extends('adminlte::page')

@section('title', 'Approvals Dashboard')

@section('content_header')
    <h1><i class="fas fa-check-double"></i> Approvals Dashboard</h1>
@stop

@section('content')

    {{-- Approval status summary cards --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3 mb-3">
            <div class="info-box" style="background-color:#f0ad4e; color:#fff; border-radius:6px;">
                <span class="info-box-icon" style="background:transparent;">
                    <i class="fas fa-hourglass-half" style="color:#fff;"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-number" style="font-size:1.8rem; font-weight:700;">{{ $counts['pending'] }}</span>
                    <span class="info-box-text">Pending Approval</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="info-box" style="background-color:#28a745; color:#fff; border-radius:6px;">
                <span class="info-box-icon" style="background:transparent;">
                    <i class="fas fa-check-circle" style="color:#fff;"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-number"
                        style="font-size:1.8rem; font-weight:700;">{{ $counts['approved'] }}</span>
                    <span class="info-box-text">Approved</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="info-box" style="background-color:#dc3545; color:#fff; border-radius:6px;">
                <span class="info-box-icon" style="background:transparent;">
                    <i class="fas fa-times-circle" style="color:#fff;"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-number"
                        style="font-size:1.8rem; font-weight:700;">{{ $counts['rejected'] }}</span>
                    <span class="info-box-text">Rejected</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="info-box" style="background-color:#5b9bd5; color:#fff; border-radius:6px;">
                <span class="info-box-icon" style="background:transparent;">
                    <i class="fas fa-database" style="color:#fff;"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-number" style="font-size:1.8rem; font-weight:700;">{{ $counts['total'] }}</span>
                    <span class="info-box-text">Total</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Approval Requests</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover mb-0" id="approvalsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Quote No</th>
                            <th>Customer</th>
                            <th>Submitted By</th>
                            <th>Submitted Date</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr data-status="{{ $row['status'] }}">
                                <td>{{ $row['quoteNo'] }}</td>
                                <td>{{ $row['customer'] }}</td>
                                <td>{{ $row['submittedBy'] }}</td>
                                <td>{{ $row['submittedDate'] }}</td>
                                <td>{{ $row['approvedCount'] }} of {{ $row['required'] }}</td>
                                <td>
                                    <span
                                        class="badge {{ $row['status'] === 'Approved' ? 'badge-success' : ($row['status'] === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('quotation.approve.page', $row['requestId']) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if ($row['status'] !== 'Approved')
                                        <a href="{{ route('quotation.approve.page', $row['requestId']) }}?mode=edit"
                                            class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No approval requests found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {

            function filterTable(status) {
                $('#approvalsTable tbody tr').each(function() {
                    const show = !status || $(this).data('status') === status;
                    $(this).toggle(show);
                });
            }

            $('.info-box').css('cursor', 'pointer').on('click', function() {
                const label = $(this).find('.info-box-text').text().trim();
                const map = {
                    'Pending Approval': 'Pending Approval',
                    'Approved': 'Approved',
                    'Rejected': 'Rejected',
                    'Total': ''
                };
                filterTable(map[label] ?? '');
            });

        });
    </script>
@stop

<div class="row justify-content-center pt-0">
    <div class="col-12">
        <div class="card table-responsive">
            <div class="card-header">
                {{-- <h3 class="card-title">
                    <i class="fas fa-file-invoice"></i>
                    {{ $view === 'ar' ? 'Revenue (AR)' : 'Cost (AP)' }} Invoices
                </h3> --}}
                <div class="card-tools">
                    <span class="badge badge-{{ $view === 'ar' ? 'info' : 'purple' }}" id="statusFilter">
                        All Invoices
                    </span>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover text-sm" id="invoice-table">
                    <thead>
                        <tr>
                            <th>InvoiceNo</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Detail Modal -->
<div class="modal fade" id="invoiceDetailModal" tabindex="-1" role="dialog" aria-labelledby="invoiceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="invoiceDetailModalLabel">
                    <i class="fas fa-file-invoice"></i> Invoice Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="invoiceDetailContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading invoice details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
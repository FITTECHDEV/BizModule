{{-- resources/views/admin/entity-tools.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container" style="max-width:700px; margin-top:40px;">

    {{-- ════════════════════════════════════════════════════════════
         DELETE ENTITY
    ════════════════════════════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">⚠️ Admin — Delete Entity</h4>
        </div>
        <div class="card-body">

            @if(session('delete_error'))
                <div class="alert alert-danger">{{ session('delete_error') }}</div>
            @endif

            @if(session('delete_success'))
                @php $result = session('delete_success'); @endphp
                <div class="alert alert-success">
                    <strong>✅ Entity {{ $result['code'] }} deleted successfully.</strong><br>
                    Cust_Entity remaining: {{ $result['entity_remaining'] }} (expected 0)<br>
                    Cust_EntityHistory remaining: {{ $result['history_remaining'] }} (expected 0)<br>
                    Cust_EntityAttachment remaining: {{ $result['attachment_remaining'] }} (expected 0)
                </div>
            @endif

            @if(session('delete_preview'))
                @php $p = session('delete_preview'); @endphp
                <div class="alert alert-warning">
                    <strong>📋 Preview — Entity found:</strong><br><br>
                    <table class="table table-sm table-bordered">
                        <tr><th>Code</th><td>{{ $p->Code }}</td></tr>
                        <tr><th>Name</th><td>{{ $p->Name }}</td></tr>
                        <tr><th>Operational Type</th><td>{{ $p->OperationalEntityType }}</td></tr>
                        <tr><th>Legal Type</th><td>{{ $p->LegalEntityType }}</td></tr>
                        <tr><th>Status</th><td>{{ $p->Status }}</td></tr>
                        <tr><th>Office Code</th><td>{{ $p->OfficeCode }}</td></tr>
                        <tr><th>Client Code</th><td>{{ $p->ClientCode }}</td></tr>
                        <tr><th>Created By</th><td>{{ $p->CreationRequestBy }}</td></tr>
                        <tr><th>Created On</th><td>{{ $p->CreationRequestOn }}</td></tr>
                        <tr><th>Is Active</th><td>{{ $p->IsActive }}</td></tr>
                        <tr><th>Is Deleted</th><td>{{ $p->IsDeleted }}</td></tr>
                    </table>

                    <form method="POST" action="/admin/entity-delete"
                          onsubmit="return confirm('Are you sure you want to permanently delete {{ session('delete_code') }}? This cannot be undone.')">
                        @csrf
                        <input type="hidden" name="code" value="{{ session('delete_code') }}">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">
                            🗑️ Confirm Delete {{ session('delete_code') }}
                        </button>
                        <a href="/admin/entity-tools" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            @endif

            <form method="POST" action="/admin/entity-delete">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Entity Code</label>
                    <input type="text" name="code" class="form-control"
                           placeholder="e.g. TZ2601131"
                           value="{{ session('delete_code') }}"
                           style="text-transform:uppercase;">
                </div>
                <input type="hidden" name="action" value="preview">
                <button type="submit" class="btn btn-warning">
                    🔍 Preview Before Delete
                </button>
            </form>

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         MARK AS CREATED
    ════════════════════════════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">🔄 Admin — Mark Entity as Created</h4>
        </div>
        <div class="card-body">

            @if(session('status_error'))
                <div class="alert alert-danger">{{ session('status_error') }}</div>
            @endif

            @if(session('status_success'))
                @php $result = session('status_success'); @endphp
                <div class="alert alert-success">
                    <strong>✅ Entity {{ $result['code'] }} status changed.</strong><br>
                    Old status: {{ $result['old_status'] }}<br>
                    New status: Created<br>
                    Rows updated: {{ $result['updated'] }}
                </div>
            @endif

            @if(session('status_preview'))
                @php $p = session('status_preview'); @endphp
                <div class="alert alert-warning">
                    <strong>📋 Preview — Entity found:</strong><br><br>
                    <table class="table table-sm table-bordered">
                        <tr><th>Code</th><td>{{ $p->Code }}</td></tr>
                        <tr><th>Name</th><td>{{ $p->Name }}</td></tr>
                        <tr><th>Operational Type</th><td>{{ $p->OperationalEntityType }}</td></tr>
                        <tr><th>Legal Type</th><td>{{ $p->LegalEntityType }}</td></tr>
                        <tr><th>Status</th><td>{{ $p->Status }}</td></tr>
                        <tr><th>Office Code</th><td>{{ $p->OfficeCode }}</td></tr>
                        <tr><th>Client Code</th><td>{{ $p->ClientCode }}</td></tr>
                        <tr><th>Created By</th><td>{{ $p->CreationRequestBy }}</td></tr>
                        <tr><th>Created On</th><td>{{ $p->CreationRequestOn }}</td></tr>
                        <tr><th>Is Active</th><td>{{ $p->IsActive }}</td></tr>
                        <tr><th>Is Deleted</th><td>{{ $p->IsDeleted }}</td></tr>
                    </table>

                    @if($p->Status === 'Created')
                        <div class="alert alert-info mb-0">This entity's status is already <strong>Created</strong>.</div>
                    @else
                        <form method="POST" action="/admin/entity-status"
                              onsubmit="return confirm('Change status of {{ session('status_code') }} from {{ $p->Status }} to Created?')">
                            @csrf
                            <input type="hidden" name="code" value="{{ session('status_code') }}">
                            <input type="hidden" name="action" value="update">
                            <button type="submit" class="btn btn-primary">
                                🔄 Confirm — Mark {{ session('status_code') }} as Created
                            </button>
                            <a href="/admin/entity-tools" class="btn btn-secondary ms-2">Cancel</a>
                        </form>
                    @endif
                </div>
            @endif

            <form method="POST" action="/admin/entity-status">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Entity Code</label>
                    <input type="text" name="code" class="form-control"
                           placeholder="e.g. TZ2601131"
                           value="{{ session('status_code') }}"
                           style="text-transform:uppercase;">
                </div>
                <input type="hidden" name="action" value="preview">
                <button type="submit" class="btn btn-warning">
                    🔍 Preview Before Status Change
                </button>
            </form>

        </div>
    </div>

</div>
@endsection

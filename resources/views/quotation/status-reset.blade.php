@extends('adminlte::page')

@section('title', 'Reset Quote Status')

@section('content_header')
    <div class="text-center mb-4">
        <h1 class="font-weight-bold">
            <i class="fas fa-sync-alt text-warning mr-2"></i>
            Reset Quote Status
        </h1>
        <p class="text-muted">
            Move a quotation from <strong>Won</strong> or <strong>Failed Int.</strong>
            back to <strong>In Progress</strong>.
        </p>
    </div>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}

                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <h6 class="mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Please correct the following errors:
                </h6>

                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-warning shadow-lg border-0">

            <div class="card-header bg-warning">
                <h3 class="card-title text-dark mb-0">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Quote Status Reset
                </h3>
            </div>

            <div class="card-body p-4">

                <form method="POST" action="{{ route('quotation.status-reset') }}">
                    @csrf

                    <div class="form-group">
                        <label for="quote_no">
                            <i class="fas fa-hashtag text-primary mr-1"></i>
                            Quote Number
                        </label>

                        <input
                            type="text"
                            class="form-control form-control-lg"
                            id="quote_no"
                            name="quote_no"
                            placeholder="KE-QT-26-00403"
                            value="{{ old('quote_no') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="current_status">
                            <i class="fas fa-tasks text-primary mr-1"></i>
                            Current Status
                        </label>

                        <select
                            class="form-control form-control-lg"
                            id="current_status"
                            name="current_status"
                            required>

                            <option value="">
                                -- Select Current Status --
                            </option>

                            <option value="Won"
                                {{ old('current_status') == 'Won' ? 'selected' : '' }}>
                                Won
                            </option>

                            <option value="Failed Int."
                                {{ old('current_status') == 'Failed Int.' ? 'selected' : '' }}>
                                Failed Int.
                            </option>

                        </select>

                        <small class="text-muted">
                            Select the quotation's current status.
                        </small>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                                class="btn btn-warning btn-block py-2">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Move to In Progress
                        </button>

                        <a href="{{ route('quotation.index') }}"
                           class="btn btn-outline-secondary btn-block mt-2">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Quotations
                        </a>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

@stop

@section('css')
<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.5rem;
    }

    .form-control-lg {
        border-radius: 10px;
        font-size: 1rem;
    }

    .btn {
        border-radius: 10px;
        font-weight: 600;
    }

    .shadow-lg {
        box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,.12) !important;
    }

    .border-left {
        border-left: 4px solid #ffc107 !important;
    }
</style>
@stop
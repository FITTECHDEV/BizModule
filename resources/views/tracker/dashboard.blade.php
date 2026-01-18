@extends('adminlte::page')

@section('title', 'GPS Trip Tracking')

@section('content_header')
    <div class="header-with-notification">
        <h1>Truck Tracking</h1>
        <button class="btn btn-warning" id="openNotificationsBtn">
            <i class="fa fa-bell"></i> Notifications
            <span class="badge badge-danger" id="unreadBadge" style="display: none;">0</span>
        </button>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/tracker/tracker.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap4.css" rel="stylesheet">

    <!-- ADD FLATPICKR CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* Your existing styles... */

        /* Flatpickr customization for better look */
        .flatpickr-calendar {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
        }

        .flatpickr-time input {
            font-size: 1.1rem !important;
            font-weight: 600;
        }

        /* Make sure inputs look good in the yellow form */
        #manualStartForm .form-control,
        #backdateForm .form-control {
            background-color: #fff !important;
            border: 2px solid #dee2e6;
        }

        #manualStartForm .form-control:focus,
        #backdateForm .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }

        .header-with-notification {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        #openNotificationsBtn {
            font-size: 1rem;
            padding: 8px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #openNotificationsBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        #unreadBadge {
            font-size: 0.75rem;
            padding: 3px 6px;
            border-radius: 10px;
            margin-left: 5px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Status Cards Row -->
        <div class="row justify-content-start">

            <div class="col-md-3 col-lg-3 col-xl-auto p-1 m-0 flex-fill">
                <div class="card bg-created order-card mb-1" id="createdCard" style="cursor: pointer;">
                    <div class="card-block">
                        <h6 class="m-b-20">Planned</h6>
                        <h2 class="text-right">
                            <i class="fa fa-plus-circle f-left"></i>
                            <span>{{ $stats->Planned ?? 0 }}</span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-xl-auto p-1 m-0 flex-fill">
                <div class="card bg-transit order-card mb-1" id="inProgressCard" style="cursor: pointer;">
                    <div class="card-block">
                        <h6 class="m-b-20">In Transit</h6>
                        <h2 class="text-right">
                            <i class="fa fa-truck f-left"></i>
                            <span>{{ $stats->in_progress ?? 0 }}</span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-xl-auto p-1 m-0 flex-fill">
                <div class="card bg-complete order-card mb-1" id="completedCard" style="cursor: pointer;">
                    <div class="card-block">
                        <h6 class="m-b-20">Completed</h6>
                        <h2 class="text-right">
                            <i class="fa fa-check-circle f-left"></i>
                            <span>{{ $stats->completed ?? 0 }}</span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-xl-auto p-1 m-0 flex-fill">
                <div class="card bg-total order-card mb-1" id="totalCard" style="cursor: pointer;">
                    <div class="card-block">
                        <h6 class="m-b-20">Total Trips</h6>
                        <h2 class="text-right">
                            <i class="fa fa-list f-left"></i>
                            <span>{{ $stats->total ?? 0 }}</span>
                        </h2>
                    </div>
                </div>
            </div>

        </div>

        <!-- Trips Table -->
        <div class="container-fluid mt-3">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm w-100" id="tripsTable">
                    <thead>
                        <tr>
                            <th>Trip No</th>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                            <th>Mileage (km)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- View Trip Modal -->
            <div class="modal fade" id="viewTripModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-map"></i> Trip Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Trip Summary -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <small class="text-muted">Trip No</small><br>
                                    <strong><span id="viewTripNo">-</span></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Vehicle</small><br>
                                    <strong><span id="viewVehicle">-</span></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Driver</small><br>
                                    <strong><span id="viewDriver">-</span></strong>
                                </div>
                                <div class="col-md-3 text-center">
                                    <span id="viewStatusBadge" class="badge badge-lg"
                                        style="font-size: 0.95rem; padding: 8px 16px;">
                                        <span id="viewStatus">-</span>
                                    </span>

                                    <!-- ADD HERE -->

                                    <button type="button" class="btn btn-danger btn-sm mt-2" id="endTripAgainBtn"
                                        style="display:none;">
                                        <i class="fa fa-redo"></i> End Trip Again
                                    </button>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-uppercase mb-2"
                                    style="color: #0d47a1; border-bottom: 2px solid #0d47a1; padding-bottom: 4px;">
                                    Trip Timeline
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Start:</small>
                                        <strong><span id="viewStartDate">-</span></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">End:</small>
                                        <strong><span id="viewEndDate">-</span></strong>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <small class="text-muted">Duration:</small>
                                        <strong><span id="viewDuration">-</span></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Mileage:</small>
                                        <strong><span id="viewMileage">-</span></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold text-uppercase mb-0"
                                        style="color: #0d47a1; border-bottom: 2px solid #0d47a1; padding-bottom: 4px; flex-grow: 1;">
                                        Allocated Jobs (<span id="jobsCount">0</span>)
                                    </h6>
                                    <button type="button" class="btn btn-info btn-sm ml-2" id="addManualJobBtn">
                                        <i class="fa fa-calendar-plus"></i> Add Historical Job
                                    </button>
                                </div>
                                <div id="allocatedJobsContainer">
                                    <div class="text-center text-muted py-3">
                                        <i class="fa fa-spinner fa-spin"></i> Loading...
                                    </div>
                                </div>
                            </div>

                            <!-- Speed & Movement Stats -->
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-uppercase mb-2"
                                    style="color: #0d47a1; border-bottom: 2px solid #0d47a1; padding-bottom: 4px;">
                                    Speed & Movement
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Avg Speed</small><br>
                                        <strong><span id="viewAvgSpeed">-</span> km/h</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Max Speed</small><br>
                                        <strong><span id="viewMaxSpeed">-</span> km/h</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">GPS Points</small><br>
                                        <strong><span id="viewGpsPoints">-</span></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- LIVE GPS TRACKING -->
                            <div id="liveTrackingSection"
                                style="display: none; margin-top: 25px; padding: 20px; border-radius: 12px; border-left: 6px solid #4caf50; background: #e8f5e9; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.4s ease;">
                                <h6 class="font-weight-bold text-uppercase mb-3" style="color: #2e7d32;">
                                    <i class="fa fa-satellite-dish"></i> Live GPS Tracking
                                    <span id="liveBadge"
                                        class="badge badge-success animate__animated animate__pulse animate__infinite ml-2"
                                        style="font-size: 1rem; padding: 6px 12px;">
                                        LIVE
                                    </span>
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Current Location (Lat, Lng)</small><br>
                                        <strong style="font-size: 1.2rem; color: #1b5e20;">
                                            <span id="liveLat">-</span>, <span id="liveLng">-</span>
                                        </strong>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <small class="text-muted">Current Speed</small><br>
                                        <strong id="liveSpeed"
                                            style="font-size: 2.4rem; color: #2e7d32; font-weight: bold;">
                                            0 <small style="font-size: 1rem;">km/h</small>
                                        </strong>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fa fa-clock"></i> Last Updated:
                                    </small>
                                    <span id="liveLastUpdate" style="color: #2e7d32; font-weight: bold;">-</span>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="#" id="openGoogleMaps" target="_blank"
                                        class="btn btn-success btn-lg shadow">
                                        <i class="fa fa-map-marker-alt"></i> Open in Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Trip Modal -->
            <div class="modal fade" id="createTripModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Create New Trip</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form id="createTripForm">
                            <div class="modal-body">

                                <!-- 1. Vehicle -->
                                <div class="form-group">
                                    <label>Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tripVehicle" name="imei" required>
                                        <option value="">Loading vehicles...</option>
                                    </select>
                                    <input type="hidden" id="tripVehicleNo" name="vehicle_no">
                                </div>

                                <!-- 2. Driver Name -->
                                <div class="form-group">
                                    <label>Driver Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="driverName" name="driver_name"
                                        placeholder="Enter driver's full name" required>
                                </div>

                                <!-- 3. Driver Phone -->
                                <div class="form-group">
                                    <label>Driver Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="driverPhone" name="driver_phone"
                                        placeholder="e.g. 0712345678" required>
                                </div>

                                <!-- Start Location -->
                                <div class="form-group">
                                    <label>Start Location <span class="text-danger">*</span></label>
                                    <select id="tripStartLocation" name="start_location_code" required>
                                        <option value="">Type to search locations...</option>
                                    </select>
                                    <small class="text-muted">Where the trip begins</small>
                                </div>

                                <!-- End Location -->
                                <div class="form-group">
                                    <label>End Location <span class="text-danger">*</span></label>
                                    <select id="tripEndLocation" name="end_location_code" required>
                                        <option value="">Type to search locations...</option>
                                    </select>
                                    <small class="text-muted">Final destination of the trip</small>
                                </div>

                                <!-- Info Alert -->
                                <div class="alert alert-info mt-3">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Note:</strong> Trip will be created with "Created" status.
                                    Add jobs and start any job to begin tracking.
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="submitCreateTrip">
                                    <i class="fa fa-check"></i> Create Trip
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Jobs Modal -->
            <div class="modal fade" id="addJobsModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title"><i class="fa fa-plus"></i> Add Job to Trip</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form id="addJobsForm">
                            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                                <input type="hidden" id="addJobsTripId" name="trip_id">

                                <div class="alert alert-info mb-3" style="padding: 10px;">
                                    <div class="row">
                                        <div class="col-6">
                                            <small><strong>Trip:</strong> <span id="addJobsTripNo">-</span></small><br>
                                            <small><strong>Vehicle:</strong> <span id="addJobsVehicle">-</span></small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Status:</strong> <span id="addJobsStatus">-</span></small><br>
                                            <small><strong>Driver:</strong> <span id="addJobsDriver">-</span></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Job Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="addJobType" name="job_type" required>
                                        <option value="">Select Job Type</option>
                                        <option value="internal">Internal Job (Pickup goods)</option>
                                        <option value="customer_delivery">Customer Delivery</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        <strong>Internal:</strong> Going to pick up goods |
                                        <strong>Customer:</strong> Delivering to customer
                                    </small>
                                </div>

                                <div id="internalJobFields" style="display: none;">
                                    <div class="alert alert-success mb-3" style="padding: 10px;">
                                        <i class="fa fa-truck"></i> <strong>Internal Job</strong> - Configure pickup
                                        details
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Start Location <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" id="jobStartLocation"
                                                    name="start_location">
                                                    <option value="">Select Start Location</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>End Location <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" id="jobEndLocation"
                                                    name="end_location">
                                                    <option value="">Select End Location</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="customerDeliveryFields" style="display: none;">
                                    <div class="alert alert-primary mb-3" style="padding: 10px;">
                                        <i class="fa fa-gift"></i> <strong>Customer Delivery</strong> - Select existing
                                        customer job
                                    </div>

                                    <div class="form-group">
                                        <label>Select Customer Job <span class="text-danger">*</span></label>
                                        <select class="form-control" id="customerJobSelect" name="customer_job_id">
                                            <option value="">Loading jobs...</option>
                                        </select>
                                    </div>

                                    <div id="customerJobDetails" style="display: none;">
                                        <div class="card">
                                            <div class="card-header bg-light" style="padding: 8px 12px;">
                                                <strong>Job Details</strong>
                                            </div>
                                            <div class="card-body" style="padding: 12px;">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small><strong>Customer:</strong> <span
                                                                id="previewCustomer">-</span></small><br>
                                                        <small><strong>Pickup:</strong> <span
                                                                id="previewPickup">-</span></small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small><strong>Phone:</strong> <span
                                                                id="previewPhone">-</span></small><br>
                                                        <small><strong>Delivery:</strong> <span
                                                                id="previewDelivery">-</span></small>
                                                    </div>
                                                </div>
                                                <small class="d-block mt-2"><strong>Description:</strong> <span
                                                        id="previewDescription">-</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer" style="padding: 10px 15px;">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning btn-sm" id="submitAddJob">
                                    <i class="fa fa-check"></i> Add Job to Trip
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Manual Job Modal -->
            <div class="modal fade" id="manualJobModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-calendar-plus"></i> Add Historical Job Entry
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form id="manualJobForm">
                            <div class="modal-body">
                                <input type="hidden" id="manualJobTripId" name="trip_id">

                                <div class="alert alert-info mb-3" style="padding: 10px;">
                                    <div class="row">
                                        <div class="col-6">
                                            <small><strong>Trip:</strong> <span id="manualJobTripNo">-</span></small><br>
                                            <small><strong>Vehicle:</strong> <span id="manualJobVehicle">-</span></small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Status:</strong> <span id="manualJobStatus">-</span></small><br>
                                            <small><strong>Driver:</strong> <span id="manualJobDriver">-</span></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i> <strong>Historical Entry:</strong>
                                    This job will be added as <strong>COMPLETED</strong> with the <span
                                        class="historical-badge">H</span> badge.
                                    GPS data will be fetched for the specified date range.
                                </div>

                                <div class="form-group">
                                    <label>Job Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="manualJobType" name="job_type" required>
                                        <option value="">Select Job Type</option>
                                        <option value="internal">Internal Job (Pickup goods)</option>
                                        <option value="customer_delivery">Customer Delivery</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" id="manualStartDate"
                                                name="start_date" required>
                                            <small class="text-muted">When the job started</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control" id="manualEndDate"
                                                name="end_date" required>
                                            <small class="text-muted">When the job was completed</small>
                                        </div>
                                    </div>
                                </div>

                                <div id="manualInternalFields" style="display: none;">
                                    <div class="alert alert-success mb-3" style="padding: 10px;">
                                        <i class="fa fa-truck"></i> <strong>Internal Job</strong> - Pickup details
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Start Location <span class="text-danger">*</span></label>
                                                <select class="form-control" id="manualStartLocation"
                                                    name="start_location">
                                                    <option value="">Select Start Location</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>End Location <span class="text-danger">*</span></label>
                                                <select class="form-control" id="manualEndLocation" name="end_location">
                                                    <option value="">Select End Location</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="manualCustomerFields" style="display: none;">
                                    <div class="alert alert-primary mb-3" style="padding: 10px;">
                                        <i class="fa fa-gift"></i> <strong>Customer Delivery</strong> - Select job
                                    </div>

                                    <div class="form-group">
                                        <label>Select Customer Job <span class="text-danger">*</span></label>
                                        <select class="form-control" id="manualCustomerJob" name="customer_job_id">
                                            <option value="">Loading jobs...</option>
                                        </select>
                                    </div>

                                    <div id="manualJobPreview" style="display: none;">
                                        <div class="card">
                                            <div class="card-header bg-light" style="padding: 8px 12px;">
                                                <strong>Job Details</strong>
                                            </div>
                                            <div class="card-body" style="padding: 12px;">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small><strong>Customer:</strong> <span
                                                                id="manualPreviewCustomer">-</span></small><br>
                                                        <small><strong>Pickup:</strong> <span
                                                                id="manualPreviewPickup">-</span></small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small><strong>Phone:</strong> <span
                                                                id="manualPreviewPhone">-</span></small><br>
                                                        <small><strong>Delivery:</strong> <span
                                                                id="manualPreviewDelivery">-</span></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-info" id="submitManualJob">
                                    <i class="fa fa-save"></i> Save Historical Job
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backdate Job Completion Modal -->
            <div class="modal fade" id="backdateJobModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-clock"></i> Backdate Job Completion
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form id="backdateJobForm">
                            <div class="modal-body">
                                <input type="hidden" id="backdateJobId">
                                <input type="hidden" id="backdateTripId">

                                <div class="alert alert-info">
                                    <strong>Job:</strong> <span id="backdateJobNo">-</span><br>
                                    <strong>Started:</strong> <span id="backdateJobStart">-</span>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Enter the actual time the job was completed.
                                </div>

                                <div class="form-group">
                                    <label>Completion Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="backdateJobDate" name="end_date"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label>Completion Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="backdateJobTime" name="end_time"
                                        required>
                                </div>

                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i>
                                    GPS data will be fetched for the specified completion time.
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning" id="submitBackdateJob">
                                    <i class="fa fa-check"></i> Complete Job
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- <!-- Backdate Trip End Modal -->
            <div class="modal fade" id="backdateTripModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-clock"></i> Backdate Trip End
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form id="backdateTripForm">
                            <div class="modal-body">
                                <input type="hidden" id="backdateTripEndId">

                                <div class="alert alert-info">
                                    <strong>Trip:</strong> <span id="backdateTripNo">-</span><br>
                                    <strong>Started:</strong> <span id="backdateTripStart">-</span>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Enter the actual time the trip ended. All jobs must be completed first.
                                </div>

                                <div class="form-group">
                                    <label>End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="backdateTripEndDate" name="end_date"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label>End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="backdateTripEndTime" name="end_time"
                                        required>
                                </div>

                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i>
                                    Complete GPS data will be calculated from trip start to this time.
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger" id="submitBackdateTrip">
                                    <i class="fa fa-check"></i> End Trip
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> --}}
            <!-- Manual Start Job Modal -->
            <div class="modal fade" id="manualStartJobModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-clock"></i> Backdate Job Start <!-- Changed -->
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form id="manualStartJobForm">
                            <div class="modal-body">
                                <input type="hidden" id="manualStartJobId">
                                <input type="hidden" id="manualStartTripId">

                                <div class="alert alert-info">
                                    <strong>Job:</strong> <span id="manualStartJobNo">-</span>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    <strong>Backdating:</strong> Enter the actual time this job was started in the past.
                                    <!-- Changed -->
                                    <br><small>Cannot be in the future.</small> <!-- Added -->
                                </div>

                                <div class="form-group">
                                    <label>Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="manualStartJobDate" name="start_date"
                                        max="" required> <!-- Added max attribute -->
                                    <small class="text-muted">Date when the job was actually started</small>
                                    <!-- Changed -->
                                </div>

                                <div class="form-group">
                                    <label>Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="manualStartJobTime" name="start_time"
                                        required>
                                    <small class="text-muted">Time when the job was actually started (Nairobi time)</small>
                                    <!-- Changed -->
                                </div>

                                <div class="alert alert-info"> <!-- Added -->
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Note:</strong> If this is the first job, the trip will also be started
                                    automatically with this time.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success" id="submitManualStartJob">
                                    <i class="fa fa-check"></i> Start Job
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Notifications Modal -->
            <div class="modal fade" id="notificationsModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-bell"></i> Vehicle Notifications
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">

                            <!-- Stats Row -->
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fa fa-bell"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total</span>
                                            <span class="info-box-number" id="statTotal">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box bg-danger">
                                        <span class="info-box-icon"><i class="fa fa-exclamation-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Unread</span>
                                            <span class="info-box-number" id="statNew">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fa fa-map-marked-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Geofence</span>
                                            <span class="info-box-number" id="statGeofence">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fa fa-key"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Ignition</span>
                                            <span class="info-box-number" id="statIgnition">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box bg-secondary">
                                        <span class="info-box-icon"><i class="fa fa-plug"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Power</span>
                                            <span class="info-box-number" id="statPower">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-right">
                                    <button class="btn btn-primary btn-block" id="refreshNotificationsBtn">
                                        <i class="fa fa-sync"></i> Refresh
                                    </button>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <form id="notificationFiltersForm" class="form-inline">
                                        <div class="form-group mr-2">
                                            <label class="mr-2">Category:</label>
                                            <select class="form-control form-control-sm" id="filterCategory">
                                                <option value="">All Categories</option>
                                                <option value="geofence">Geofence</option>
                                                <option value="ignition">Ignition (ACC)</option>
                                                <option value="power">Power/Offline</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group mr-2">
                                            <label class="mr-2">Vehicle:</label>
                                            <input type="text" class="form-control form-control-sm" id="filterVehicle"
                                                placeholder="Vehicle No">
                                        </div>
                                        <div class="form-group mr-2">
                                            <label class="mr-2">From:</label>
                                            <input type="date" class="form-control form-control-sm"
                                                id="filterFromDate">
                                        </div>
                                        <div class="form-group mr-2">
                                            <label class="mr-2">To:</label>
                                            <input type="date" class="form-control form-control-sm" id="filterToDate">
                                        </div>
                                        <div class="form-group mr-2">
                                            <label class="mr-2">Status:</label>
                                            <select class="form-control form-control-sm" id="filterStatus">
                                                <option value="">All</option>
                                                <option value="New">Unread</option>
                                                <option value="Read">Read</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                                            <i class="fa fa-filter"></i> Filter
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            id="clearNotificationFilters">
                                            <i class="fa fa-times"></i> Clear
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Tabs for Categories -->
                            <ul class="nav nav-tabs" id="notificationTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#allNotifications"
                                        role="tab">
                                        All <span class="badge badge-secondary" id="countAll">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="geofence-tab" data-toggle="tab"
                                        href="#geofenceNotifications" role="tab">
                                        <i class="fa fa-map-marked-alt"></i> Geofence <span class="badge badge-warning"
                                            id="countGeofence">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="ignition-tab" data-toggle="tab"
                                        href="#ignitionNotifications" role="tab">
                                        <i class="fa fa-key"></i> Ignition <span class="badge badge-primary"
                                            id="countIgnition">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="power-tab" data-toggle="tab" href="#powerNotifications"
                                        role="tab">
                                        <i class="fa fa-plug"></i> Power <span class="badge badge-secondary"
                                            id="countPower">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="other-tab" data-toggle="tab" href="#otherNotifications"
                                        role="tab">
                                        <i class="fa fa-bell"></i> Other <span class="badge badge-info"
                                            id="countOther">0</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content mt-3" id="notificationTabContent"
                                style="max-height: 500px; overflow-y: auto;">
                                <div class="tab-pane fade show active" id="allNotifications" role="tabpanel">
                                    <div id="allNotificationsContainer">
                                        <div class="text-center py-4">
                                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                                            <p class="mt-2">Loading notifications...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="geofenceNotifications" role="tabpanel">
                                    <div id="geofenceNotificationsContainer"></div>
                                </div>
                                <div class="tab-pane fade" id="ignitionNotifications" role="tabpanel">
                                    <div id="ignitionNotificationsContainer"></div>
                                </div>
                                <div class="tab-pane fade" id="powerNotifications" role="tabpanel">
                                    <div id="powerNotificationsContainer"></div>
                                </div>
                                <div class="tab-pane fade" id="otherNotifications" role="tabpanel">
                                    <div id="otherNotificationsContainer"></div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
        <!-- ADD THIS LINE -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            window.VEHICLE_IMEI_MAP = @json($finalMap ?? []);

            function getRealImei(vehicleNo) {
                if (!vehicleNo || !window.VEHICLE_IMEI_MAP) return null;
                if (window.VEHICLE_IMEI_MAP[vehicleNo]) {
                    return window.VEHICLE_IMEI_MAP[vehicleNo];
                }
                const clean = vehicleNo.split(' - ')[0].trim();
                return window.VEHICLE_IMEI_MAP[clean] || null;
            }
        </script>
        <script>
            $(document).ready(function() {
                let currentFilter = 'all';

                let tripsTable = $('#tripsTable').DataTable({
                    processing: true,
                    responsive: true,
                    autoWidth: false,
                    ajax: {
                        url: '/tracker/trips/get/' + currentFilter,
                        type: 'GET',
                        dataSrc: function(json) {
                            // ✅ SORTING FIX: Sort data on frontend before displaying
                            if (json.data && json.data.length > 0) {
                                json.data.sort(function(a, b) {
                                    // Extract numeric part from trip numbers
                                    let aNum = parseInt(a.trip_no.replace(/\D/g, '')) || 0;
                                    let bNum = parseInt(b.trip_no.replace(/\D/g, '')) || 0;

                                    // Sort descending (highest first)
                                    return bNum - aNum;
                                });
                            }
                            return json.data;
                        },
                        error: function(xhr) {
                            console.error('DataTable error:', xhr);
                            alert('Error: Failed to load trips');
                        }
                    },
                    order: [
                        [0, 'desc']
                    ], // ✅ Set default sort on Trip No column (descending)
                    columns: [{
                            data: 'trip_no',
                            // ✅ SORTING FIX: Custom sorting for trip numbers
                            render: function(data, type, row) {
                                if (type === 'sort' || type === 'type') {
                                    // Extract numeric part for sorting
                                    return parseInt(data.replace(/\D/g, '')) || 0;
                                }
                                return data; // Display normal text
                            }
                        },
                        {
                            data: 'vehicle_no'
                        },
                        {
                            data: 'driver_name',
                            render: data => data || '-'
                        },
                        {
                            data: 'start_date',
                            render: data => data ? moment(data).format('DD/MM/YY HH:mm') : '-'
                        },
                        {
                            data: 'end_date',
                            render: data => data ? moment(data).format('DD/MM/YY HH:mm') : 'Ongoing'
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                if (!row.start_date)
                                    return '<small class="text-muted">Not started</small>';
                                if (!row.end_date)
                                    return '<small class="text-warning">In Transit...</small>';

                                let start = moment(row.start_date);
                                let end = moment(row.end_date);
                                let duration = moment.duration(end.diff(start));
                                let hours = Math.floor(duration.asHours());
                                let minutes = duration.minutes();

                                return `<strong>${hours}h ${minutes}m</strong>`;
                            }
                        },
                        {
                            data: 'total_mileage',
                            render: data => data ? parseFloat(data).toFixed(2) + ' km' : '0.00 km'
                        },
                        {
                            data: 'status',
                            render: function(data, type, row) {
                                const badgeClass = {
                                    'Planned': 'badge-info',
                                    'In Transit': 'badge-warning',
                                    'Completed': 'badge-success'
                                } [data] || 'badge-secondary'; // ✅ SYNTAX FIX: Removed space

                                return `<span class="badge ${badgeClass}">${data}</span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            className: 'text-center',
                            render: function(data, type, row) {
                                let buttons =
                                    `<button class="btn btn-sm btn-info viewTrip" data-trip-id="${row.id}" title="View"><i class="fa fa-eye"></i></button> `;

                                if (row.status === 'In Transit') {
                                    buttons +=
                                        `<button class="btn btn-sm btn-danger endTrip" data-trip-id="${row.id}" title="End Trip"><i class="fa fa-stop"></i></button> `;
                                }

                                if (row.status === 'Planned' || row.status === 'In Transit') {
                                    buttons +=
                                        `<button class="btn btn-sm btn-warning addJobsBtn" data-trip-id="${row.id}" title="Add Jobs"><i class="fa fa-plus"></i></button>`;
                                }

                                return buttons;
                            }
                        }
                    ],
                    initComplete: function() {
                        $("#tripsTable_filter").append(
                            `<button type="button" class="btn btn-primary btn-sm ml-2" id="createTripBtn">
                 <i class="fa fa-plus"></i> Create Trip
                    </button>`
                        );
                    },
                    drawCallback: function() {
                        $('#tripsTable thead th').css({
                            'background-color': '#0d47a1',
                            'color': '#fff',
                            'font-weight': '600',
                            'padding': '10px',
                            'font-size': '0.85rem'
                        });
                    }
                });
                // Card Filters
                $('#createdCard').click(() => {
                    currentFilter = 'Planned';
                    tripsTable.ajax.url('/tracker/trips/get/Planned').load();
                });

                $('#inProgressCard').click(() => {
                    currentFilter = 'in-progress';
                    tripsTable.ajax.url('/tracker/trips/get/in-progress').load();
                });

                $('#completedCard').click(() => {
                    currentFilter = 'completed';
                    tripsTable.ajax.url('/tracker/trips/get/completed').load();
                });

                $('#totalCard').click(() => {
                    currentFilter = 'all';
                    tripsTable.ajax.url('/tracker/trips/get/all').load();
                });

                // ========================================
                // CREATE TRIP MODAL
                // ========================================

                function initializeLocationSelects() {
                    const selectors = ['#tripStartLocation', '#tripEndLocation'];

                    selectors.forEach(selector => {
                        const element = document.querySelector(selector);
                        if (!element) return;

                        // Destroy old instance if exists
                        if (element.tomselect) {
                            element.tomselect.destroy();
                        }

                        new TomSelect(selector, {
                            valueField: 'code',
                            labelField: 'text',
                            searchField: ['text'],

                            load: function(query, callback) {
                                query = query.trim();
                                if (query.length < 2) {
                                    callback();
                                    return;
                                }

                                $.get('/tracker/locations', {
                                        type: 'Road',
                                        include_country: 0,
                                        filter: query
                                    })
                                    .done(function(res) {
                                        if (res.success && res.data) {
                                            const items = res.data.map(loc => ({
                                                code: loc.code,
                                                text: `${loc.name} (${loc.code})`
                                            }));
                                            callback(items);
                                        } else {
                                            callback([]);
                                        }
                                    })
                                    .fail(function() {
                                        callback([]);
                                    });
                            },

                            placeholder: 'Type to search locations...',
                            loadThrottle: 300,
                            maxOptions: 100,
                            closeAfterSelect: true,
                            shouldLoad: function(query) {
                                return query.trim().length >= 2;
                            }
                        });
                    });
                }

                // Keep old function name working (so nothing breaks)
                function loadTripLocations() {
                    initializeLocationSelects();
                }
                $(document).on('click', '#createTripBtn', function() {
                    $.get('/tracker/vehicles/list', function(res) {
                        if (res.success) {
                            let options = '<option value="">Select Vehicle</option>';
                            res.data.forEach(v => {
                                options +=
                                    `<option value="${v.imei}" data-vehicle="${v.vehicle_no}">${v.display_name}</option>`;
                            });
                            $('#tripVehicle').html(options);
                        }
                    });

                    $('#createTripForm')[0].reset();

                    // Show the modal first
                    $('#createTripModal').modal('show');

                    // Initialize searchable locations ONLY after modal is fully shown
                    $('#createTripModal').one('shown.bs.modal', function() {
                        initializeLocationSelects();
                    });
                });

                $('#tripVehicle').change(function() {
                    $('#tripVehicleNo').val($(this).find(':selected').data('vehicle'));
                });

                $('#createTripForm').submit(function(e) {
                    e.preventDefault();

                    const formData = {
                        vehicle_no: $('#tripVehicleNo').val(),
                        imei: $('#tripVehicle').val(),
                        start_location_code: $('#tripStartLocation').val(),
                        end_location_code: $('#tripEndLocation').val(),
                        driver_name: $('#driverName').val(),
                        driver_phone: $('#driverPhone').val()
                    };

                    if (!formData.imei) {
                        return alert('Please select a vehicle');
                    }

                    if (!formData.start_location_code) {
                        return alert('Please select a start location');
                    }

                    if (!formData.end_location_code) {
                        return alert('Please select an end location');
                    }

                    // ❌ REMOVED — start/end cannot be same check
                    // if (formData.start_location_code === formData.end_location_code) {
                    //     return alert('Start and end locations cannot be the same');
                    // }

                    $('#submitCreateTrip').prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Creating...');

                    $.ajax({
                        url: '/tracker/trips/create',
                        type: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success === true || res.success === 'true' || res.success ===
                                1) {
                                $('#createTripModal').modal('hide');
                                alert('Trip created! Trip No: ' + res.trip_no);
                                tripsTable.ajax.reload();
                                $('#createTripForm')[0].reset();
                            } else {
                                let errorText = 'Failed to create trip:\n\n';
                                if (Array.isArray(res.errors)) {
                                    res.errors.forEach(err => {
                                        errorText += '• ' + (err.Remark || err) + '\n';
                                    });
                                } else {
                                    errorText += res.message || 'Unknown error occurred';
                                }
                                alert(errorText);
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + (xhr.responseJSON?.message ||
                                'Failed to create trip'));
                        },
                        complete: function() {
                            $('#submitCreateTrip').prop('disabled', false).html(
                                '<i class="fa fa-check"></i> Create Trip');
                        }
                    });
                });


                // ========================================
                // ADD JOBS MODAL
                // ========================================

                function loadJobLocations() {
                    $.get('/tracker/locations', {
                        type: 'Road',
                        include_country: 1,
                        country_code: 'KE'
                    }, function(res) {
                        if (res.success && res.data.length > 0) {
                            let startOptions = '<option value="">Select Start Location</option>';
                            let endOptions = '<option value="">Select End Location</option>';

                            res.data.forEach(location => {
                                startOptions +=
                                    `<option value="${location.code}">${location.name}</option>`;
                                endOptions +=
                                    `<option value="${location.code}">${location.name}</option>`;
                            });

                            $('#jobStartLocation').html(startOptions);
                            $('#jobEndLocation').html(endOptions);
                        }
                    });
                }

                $('#addJobType').change(function() {
                    const jobType = $(this).val();

                    if (jobType === 'internal') {
                        $('#internalJobFields').slideDown();
                        $('#customerDeliveryFields').slideUp();
                        $('#jobStartLocation').prop('required', true);
                        $('#jobEndLocation').prop('required', true);
                        $('#customerJobSelect').prop('required', false);
                        loadJobLocations();

                    } else if (jobType === 'customer_delivery') {
                        $('#customerDeliveryFields').slideDown();
                        $('#internalJobFields').slideUp();
                        $('#customerJobSelect').prop('required', true);
                        $('#jobStartLocation').prop('required', false);
                        $('#jobEndLocation').prop('required', false);
                        loadCustomerJobs();

                    } else {
                        $('#internalJobFields').hide();
                        $('#customerDeliveryFields').hide();
                    }
                });

                function loadCustomerJobs() {
                    const vehicleNo = $('#addJobsVehicle').text();

                    if (!vehicleNo || vehicleNo === '-' || vehicleNo === 'N/A') {
                        $('#customerJobSelect').html('<option value="">Vehicle not assigned to trip</option>');
                        return;
                    }

                    $('#customerJobSelect').html('<option value="">Loading jobs for ' + vehicleNo + '...</option>');

                    $.get('/tracker/jobs/available', {
                        vehicle_no: vehicleNo
                    }, function(res) {
                        if (res.success && res.data.length > 0) {
                            let options = '<option value="">Select Customer Job</option>';
                            res.data.forEach(job => {
                                options += `<option value="${job.id}"
                                    data-customer="${job.customer_name}"
                                    data-phone="${job.customer_phone || 'N/A'}"
                                    data-pickup="${job.pickup_location}"
                                    data-pickup-code="${job.pickup_location_code}"
                                    data-delivery="${job.delivery_location}"
                                    data-delivery-code="${job.delivery_location_code}"
                                    data-description="${job.description || 'No description'}">
                                    ${job.job_number} - ${job.pickup_location} → ${job.delivery_location}
                                </option>`;
                            });
                            $('#customerJobSelect').html(options);
                        } else {
                            $('#customerJobSelect').html(
                                '<option value="">No jobs available for this vehicle</option>');
                        }
                    });
                }

                $('#customerJobSelect').change(function() {
                    if ($(this).val()) {
                        const selected = $(this).find(':selected');
                        $('#previewCustomer').text(selected.data('customer'));
                        $('#previewPhone').text(selected.data('phone'));
                        $('#previewPickup').text(selected.data('pickup'));
                        $('#previewDelivery').text(selected.data('delivery'));
                        $('#previewDescription').text(selected.data('description'));
                        $('#customerJobDetails').slideDown();
                    } else {
                        $('#customerJobDetails').slideUp();
                    }
                });

                $(document).on('click', '.addJobsBtn', function() {
                    const tripId = $(this).data('trip-id');
                    $('#addJobsTripId').val(tripId);

                    $.get(`/tracker/trips/${tripId}`, function(res) {
                        if (res.success) {
                            const trip = res.data;
                            $('#addJobsTripNo').text(trip.trip_no || 'N/A');
                            $('#addJobsVehicle').text(trip.vehicle_no || 'N/A');
                            $('#addJobsStatus').text(trip.status || 'N/A');
                            $('#addJobsDriver').text(trip.driver_name || 'Not assigned');
                        }
                    });

                    $('#addJobsForm')[0].reset();
                    $('#internalJobFields').hide();
                    $('#customerDeliveryFields').hide();
                    $('#customerJobDetails').hide();

                    $('#addJobsModal').modal('show');
                });

                $('#addJobsForm').submit(function(e) {
                    e.preventDefault();

                    const jobType = $('#addJobType').val();

                    if (!jobType) {
                        return Swal.fire({
                            icon: 'warning',
                            title: 'Missing Selection',
                            text: 'Please select a job type'
                        });
                    }

                    let jobs = [];

                    if (jobType === 'internal') {
                        const startLocationCode = $('#jobStartLocation').val();
                        const endLocationCode = $('#jobEndLocation').val();

                        if (!startLocationCode || !endLocationCode) {
                            return Swal.fire({
                                icon: 'warning',
                                title: 'Missing Fields',
                                text: 'Please select start and end locations'
                            });
                        }

                        if (startLocationCode === endLocationCode) {
                            return Swal.fire({
                                icon: 'warning',
                                title: 'Invalid Input',
                                text: 'Start and end locations cannot be the same'
                            });
                        }

                        jobs.push({
                            job_type: 'Internal Job',
                            job_no: '',
                            start_location_code: startLocationCode,
                            end_location_code: endLocationCode
                        });

                    } else if (jobType === 'customer_delivery') {
                        const selectedJob = $('#customerJobSelect').val();

                        if (!selectedJob) {
                            return Swal.fire({
                                icon: 'warning',
                                title: 'Missing Job',
                                text: 'Please select a customer job'
                            });
                        }

                        const selectedOption = $('#customerJobSelect option:selected');

                        jobs.push({
                            job_type: 'Customer Delivery',
                            job_no: selectedOption.text().split(' - ')[0],
                            start_location_code: selectedOption.data('pickup-code'),
                            end_location_code: selectedOption.data('delivery-code')
                        });
                    }

                    const formData = {
                        trip_id: $('#addJobsTripId').val(),
                        jobs: jobs
                    };

                    $('#submitAddJob').prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Adding...'
                    );

                    $.ajax({
                        url: `/tracker/trips/${formData.trip_id}/jobs/add`,
                        type: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success === true || res.success === 'true' || res.success ===
                                1) {

                                $('#addJobsModal').modal('hide');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    html: `
                        <strong>${res.message}</strong><br><br>
                        <b>Trip No:</b> ${res.trip_no}<br>
                        <b>Jobs Added:</b> ${res.jobs_added}
                    `,
                                    confirmButtonText: 'OK'
                                });

                                $('#addJobsForm')[0].reset();
                                $('#internalJobFields').hide();
                                $('#customerDeliveryFields').hide();
                                $('#customerJobDetails').hide();

                            } else {
                                let errorHtml = '<p>Failed to add jobs:</p><ul>';

                                if (Array.isArray(res.errors)) {
                                    res.errors.forEach(err => {
                                        errorHtml += `<li>${err.Remark || err}</li>`;
                                    });
                                } else {
                                    errorHtml +=
                                        `<li>${res.message || 'Unknown error occurred'}</li>`;
                                }

                                errorHtml += '</ul>';

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    html: errorHtml
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorHtml = '<p>Failed to add jobs:</p><ul>';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.errors) {
                                    const errors = xhr.responseJSON.errors;

                                    if (Array.isArray(errors)) {
                                        errors.forEach(err => {
                                            errorHtml += `<li>${err.Remark || err}</li>`;
                                        });
                                    } else {
                                        Object.keys(errors).forEach(key => {
                                            errorHtml += `<li>${errors[key][0]}</li>`;
                                        });
                                    }

                                } else if (xhr.responseJSON.message) {
                                    errorHtml += `<li>${xhr.responseJSON.message}</li>`;
                                }
                            }

                            errorHtml += '</ul>';

                            Swal.fire({
                                icon: 'error',
                                title: 'Request Failed',
                                html: errorHtml
                            });
                        },
                        complete: () => {
                            $('#submitAddJob').prop('disabled', false).html(
                                '<i class="fa fa-check"></i> Add Job to Trip'
                            );
                        }
                    });
                });

                // ========================================
                // END TRIP - SIMPLIFIED (No backdate option)
                // ========================================
                $(document).on('click', '.endTrip', function() {
                    const tripId = $(this).data('trip-id');
                    const $btn = $(this);

                    if ($btn.prop('disabled')) return;

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                    $.get(`/tracker/trips/${tripId}`)
                        .done(function(res) {
                            if (!res.success) {
                                Swal.fire('Error', 'Failed to load trip details', 'error');
                                $btn.prop('disabled', false).html('<i class="fa fa-stop"></i>');
                                return;
                            }

                            // Check if all jobs are completed
                            const incompleteJobs = (res.data.jobs || []).filter(j => j.status !==
                                'Completed');

                            if (incompleteJobs.length > 0) {
                                const jobList = incompleteJobs
                                    .map(j =>
                                        `• ${j.job_number || 'Internal Job'} <small>(${j.status})</small>`)
                                    .join('<br>');

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Cannot End Trip',
                                    html: `<strong>All jobs must be completed first.</strong><br><br>Pending jobs:<br>${jobList}`,
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#d33'
                                });

                                $btn.prop('disabled', false).html('<i class="fa fa-stop"></i>');
                                return;
                            }

                            // Get last completed job info
                            const completedJobs = (res.data.jobs || [])
                                .filter(j => j.status === 'Completed' && j.end_date)
                                .sort((a, b) => new Date(b.end_date) - new Date(a.end_date));

                            const lastJob = completedJobs[0];
                            const lastJobInfo = lastJob ?
                                `<br><small>Trip will end at: <strong>${moment(lastJob.end_date).format('DD/MM/YY HH:mm')}</strong></small>` :
                                '';

                            // Show confirmation
                            Swal.fire({
                                title: 'End Trip?',
                                html: `
                    <p><strong>Trip:</strong> ${res.data.trip_no}</p>
                    <p><strong>Vehicle:</strong> ${res.data.vehicle_no}</p>
                    <p><strong>Driver:</strong> ${res.data.driver_name || 'Not assigned'}</p>
                    ${lastJobInfo}
                    <br>
                    <div class="alert alert-info mt-3" style="font-size: 0.9rem;">
                        <i class="fa fa-info-circle"></i>
                        Trip will be ended with the completion time of the last job.
                    </div>
                     `,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa fa-stop-circle"></i> End Trip',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // End trip
                                    $btn.prop('disabled', true).html(
                                        '<i class="fa fa-spinner fa-spin"></i> Ending...');

                                    $.ajax({
                                        url: `/tracker/trips/${tripId}/end`,
                                        type: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                                                .attr('content')
                                        },
                                        success: function(endRes) {
                                            if (endRes.success) {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Trip Ended Successfully!',
                                                    html: `
                                        <div style="text-align: left;">
                                            <p><strong>Trip:</strong> ${endRes.trip_no}</p>
                                            <p><strong>Ended at:</strong> ${moment(endRes.end_date).format('DD/MM/YY HH:mm')}</p>
                                            <p><strong>Last Job:</strong> ${endRes.last_job_no}</p>
                                            <hr>
                                            <p><strong>Total Distance:</strong> ${endRes.total_distance || 'N/A'} km</p>
                                            <p><strong>Duration:</strong> ${endRes.duration_hours || 'N/A'} hours</p>
                                            <p><strong>Avg Speed:</strong> ${endRes.avg_speed || 'N/A'} km/h</p>
                                            <p><strong>Max Speed:</strong> ${endRes.max_speed || 'N/A'} km/h</p>
                                        </div>
                                    `,
                                                    confirmButtonText: 'OK'
                                                });

                                                if (typeof tripsTable !== 'undefined') {
                                                    tripsTable.ajax.reload(null, false);
                                                }
                                            } else {
                                                Swal.fire('Failed', endRes.message ||
                                                    'Unknown error', 'error');
                                            }

                                            $btn.prop('disabled', false).html(
                                                '<i class="fa fa-stop"></i>');
                                        },
                                        error: function(xhr) {
                                            Swal.fire('Error', xhr.responseJSON
                                                ?.message || 'Server error', 'error'
                                            );
                                            $btn.prop('disabled', false).html(
                                                '<i class="fa fa-stop"></i>');
                                        }
                                    });
                                } else {
                                    $btn.prop('disabled', false).html('<i class="fa fa-stop"></i>');
                                }
                            });
                        })
                        .fail(function() {
                            Swal.fire('Error', 'Failed to connect to server', 'error');
                            $btn.prop('disabled', false).html('<i class="fa fa-stop"></i>');
                        });
                });

                // ... your existing .endTrip handler ends here ...

                // ─────────────────────────────────────────────────────────────
                // NEW: END TRIP AGAIN HANDLER
                // ─────────────────────────────────────────────────────────────
                $(document).on('click', '#endTripAgainBtn', function() {
                    const tripId = $('#viewTripModal').data('trip-id');
                    const tripNo = $('#viewTripNo').text();

                    Swal.fire({
                        title: 'Re-end & Recalculate Trip?',
                        html: `
                    <div class="alert alert-warning mb-3">
                    <strong>Warning:</strong><br>
                    This will <b>overwrite</b> current end time and<br>
                    <b>recalculate</b> distance, duration, speeds, etc.<br><br>
                    <small>Use only after correcting the last job's time.</small>
                    </div>
                    <p><strong>Trip No:</strong> ${tripNo}</p>
                      <p>Are you sure?</p>
                     `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Re-End & Recalculate',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Processing...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();

                                    $.ajax({
                                        url: `/tracker/trips/${tripId}/end`,
                                        type: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': $(
                                                'meta[name="csrf-token"]').attr(
                                                'content')
                                        },
                                        success: function(res) {
                                            if (res.success) {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Trip Re-Ended!',
                                                    html: `End time updated: ${moment(res.end_date).format('DD/MM/YY HH:mm')}<br><br>` +
                                                        `Distance: ${res.total_distance || 'N/A'} km<br>` +
                                                        `Duration: ${res.duration_hours || 'N/A'} h`,
                                                    confirmButtonText: 'OK'
                                                });

                                                tripsTable?.ajax.reload(null,
                                                    false);
                                                setTimeout(() => {
                                                    $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                        .trigger(
                                                            'click');
                                                }, 800);
                                            } else {
                                                Swal.fire('Failed', res
                                                    .message || 'Error',
                                                    'error');
                                            }
                                        },
                                        error: function(xhr) {
                                            Swal.fire('Error', xhr.responseJSON
                                                ?.message || 'Server error',
                                                'error');
                                        }
                                    });
                                }
                            });
                        }
                    });
                });

                // ========================================
                // VIEW TRIP - ✅ WITH START DATE, END DATE, DURATION, AND MILEAGE
                // ========================================
                $(document).on('click', '.viewTrip', function() {
                    const tripId = $(this).data('trip-id');

                    if (window.liveTrackingInterval) {
                        clearInterval(window.liveTrackingInterval);
                        window.liveTrackingInterval = null;
                    }

                    $.get(`/tracker/trips/${tripId}`, function(res) {
                        if (!res.success) {
                            alert('Error: Failed to load trip details');
                            return;
                        }

                        const trip = res.data;

                        $('#viewTripModal').data('trip-id', tripId);

                        $('#viewTripNo').text(trip.trip_no || 'N/A');
                        $('#viewVehicle').text(trip.vehicle_no || 'N/A');
                        $('#viewDriver').text(trip.driver_name || 'Not assigned');

                        $('#viewStartDate').text(trip.start_date ? moment(trip.start_date).format(
                            'DD/MM/YY HH:mm') : '-');
                        $('#viewEndDate').text(trip.end_date ? moment(trip.end_date).format(
                            'DD/MM/YY HH:mm') : 'Ongoing');

                        if (trip.start_date && trip.end_date) {
                            const duration = moment.duration(moment(trip.end_date).diff(moment(trip
                                .start_date)));
                            $('#viewDuration').html(
                                `<strong>${Math.floor(duration.asHours())}h ${duration.minutes()}m</strong>`
                            );
                        } else if (trip.start_date && !trip.end_date) {
                            $('#viewDuration').html(
                                '<small class="text-warning"><i class="fa fa-clock"></i> Running...</small>'
                            );
                        } else {
                            $('#viewDuration').text('-');
                        }

                        $('#viewMileage').text(trip.total_distance ? parseFloat(trip.total_distance)
                            .toFixed(2) + ' km' : '0.00 km');
                        $('#viewAvgSpeed').text(trip.avg_speed ? parseFloat(trip.avg_speed).toFixed(1) :
                            'N/A');
                        $('#viewMaxSpeed').text(trip.max_speed ? parseFloat(trip.max_speed).toFixed(0) :
                            'N/A');
                        $('#viewGpsPoints').text(trip.gps_points || 'N/A');

                        const statusMap = {
                            'Planned': {
                                text: 'Planned',
                                class: 'badge-info'
                            },
                            'In Transit': {
                                text: 'In Transit',
                                class: 'badge-warning'
                            },
                            'Completed': {
                                text: 'Completed',
                                class: 'badge-success'
                            }
                        };
                        const status = trip.status || (trip.start_date ? (trip.end_date ? 'Completed' :
                            'In Transit') : 'Planned');
                        const badge = statusMap[status] || {
                            text: status,
                            class: 'badge-secondary'
                        };

                        $('#viewStatus').text(badge.text);
                        $('#viewStatusBadge')
                            .removeClass('badge-info badge-warning badge-success badge-secondary')
                            .addClass(badge.class);
                        const $endAgainBtn = $('#endTripAgainBtn');

                        if (status === 'Completed') {
                            $endAgainBtn
                                .prop('disabled', false)
                                .removeClass('btn-secondary btn-info')
                                .addClass('btn-danger')
                                .html('<i class="fa fa-redo"></i> End Trip Again')
                                .attr('title', 'Recalculate trip after correcting last job timing')
                                .show();
                        } else {
                            $endAgainBtn.hide();
                        }


                        // Update "Add Historical Job" button based on trip status
                        const $histBtn = $('#addManualJobBtn');

                        if (trip.status === 'Completed') {
                            $histBtn.prop('disabled', true)
                                .removeClass('btn-info')
                                .addClass('btn-secondary')
                                .html('<i class="fa fa-lock"></i> Historical Jobs Locked')
                                .attr('title', 'Cannot add historical jobs to a completed trip');
                        } else {
                            $histBtn.prop('disabled', false)
                                .removeClass('btn-secondary')
                                .addClass('btn-info')
                                .html('<i class="fa fa-calendar-plus"></i> Add Historical Job')
                                .attr('title', 'Add a job that occurred in the past');
                        }

                        // ✅ Jobs table with START DATE, END DATE, DURATION, and MILEAGE
                        const jobs = Array.isArray(trip.jobs) ? trip.jobs : [];
                        $('#jobsCount').text(jobs.length);

                        if (jobs.length === 0) {
                            $('#allocatedJobsContainer').html(
                                '<div class="text-center text-muted py-4"><i class="fa fa-briefcase fa-2x mb-2"></i><p>No jobs allocated</p></div>'
                            );
                        } else {
                            let html = `<div class="table-responsive"><table class="table table-sm table-bordered" style="font-size: 0.85rem;">
                        <thead class="bg-primary text-white">
                        <tr>
                        <th>Job No</th>
                        <th>Type</th>
                        <th>Route</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Duration</th>
                        <th>Mileage</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                        </tr>
                         </thead>
                        <tbody>`;

                            jobs.forEach(job => {
                                const typeBadge = job.job_type === 'Internal Job' ? 'info' :
                                    'primary';
                                const statusBadge = job.status === 'Completed' ? 'success' :
                                    job.status === 'In Transit' ? 'warning' : 'secondary';

                                // ✅ Historical badge
                                const historicalBadge = job.is_historical ?
                                    '<span class="historical-badge">H</span>' : '';

                                // ✅ Format dates
                                const startDate = job.start_date ?
                                    moment(job.start_date).format('DD/MM/YY HH:mm') :
                                    '<small class="text-muted">Not started</small>';

                                const endDate = job.end_date ?
                                    moment(job.end_date).format('DD/MM/YY HH:mm') :
                                    (job.start_date ?
                                        '<small class="text-warning">In transit...</small>' :
                                        '<small class="text-muted">-</small>');

                                // ✅ Calculate duration
                                let duration = '<small class="text-muted">-</small>';
                                if (job.start_date && job.end_date) {
                                    const dur = moment.duration(moment(job.end_date).diff(
                                        moment(job.start_date)));
                                    const hours = Math.floor(dur.asHours());
                                    const minutes = dur.minutes();
                                    duration = `<strong>${hours}h ${minutes}m</strong>`;
                                } else if (job.start_date && !job.end_date) {
                                    duration =
                                        '<small class="text-warning"><i class="fa fa-clock"></i> Running...</small>';
                                }

                                // ✅ Format mileage from job data
                                const mileage = job.total_distance ?
                                    `<strong>${parseFloat(job.total_distance).toFixed(2)} km</strong>` :
                                    '<small class="text-muted">N/A</small>';

                                // Check if any job is active
                                const hasActiveJob = jobs.some(j => j.status === 'In Transit');
                                const activeJobNo = hasActiveJob ?
                                    jobs.find(j => j.status === 'In Transit')?.job_number ||
                                    'Job In Transit' :
                                    null;

                                let actionBtn = '-';

                                if (job.status === 'Allocated') {
                                    if (hasActiveJob) {
                                        actionBtn = `
                    <button class="btn btn-sm btn-secondary" disabled
                    title="Complete the active job first: ${activeJobNo}">
                    <i class="fa fa-play"></i> Start
                    </button>`;
                                    } else {
                                        actionBtn = `
                    <button class="btn btn-sm btn-success startJobBtn"
                    data-job-id="${job.job_id}"
                    data-job-no="${job.job_number || 'Internal Job'}"
                    data-trip-id="${tripId}">
                    <i class="fa fa-play"></i> Start
                     </button>`;
                                    }
                                } else if (job.status === 'In Transit') {
                                    actionBtn = `
                    <button class="btn btn-sm btn-danger stopJobBtn"
                     data-job-id="${job.job_id}"
                     data-job-no="${job.job_number || 'Internal Job'}"
                     data-trip-id="${tripId}">
                    <i class="fa fa-stop"></i> Stop
                    </button>`;
                                }

                                html += `<tr>
                    <td><strong>${job.job_number || 'Internal'}${historicalBadge}</strong></td>
                    <td><span class="badge badge-${typeBadge}">${job.job_type}</span></td>
                    <td><small>${job.pickup_location} → ${job.delivery_location}</small></td>
                    <td>${startDate}</td>
                    <td>${endDate}</td>
                    <td>${duration}</td>
                    <td>${mileage}</td>
                    <td><span class="badge badge-${statusBadge}">${job.status}</span></td>
                    <td class="text-center">${actionBtn}</td>
                     </tr>`;
                            });
                            html += '</tbody></table></div>';
                            $('#allocatedJobsContainer').html(html);
                        }

                        // Live Tracking
                        const vehicleNo = trip.vehicle_no || trip.vehicle_imei || 'Unknown';
                        const realImei = getRealImei(vehicleNo);

                        if (realImei) {
                            $('#liveTrackingSection').show();

                            if (window.liveTrackingInterval) clearInterval(window.liveTrackingInterval);

                            const updateLiveData = () => {
                                $.get(`/jimi-test/location/${realImei}`, function(res) {
                                    if (res.success && res.data && res.data.lat && res.data
                                        .lng) {
                                        const l = res.data;

                                        $('#liveTrackingSection').css({
                                            'background': '#e8f5e9',
                                            'border-left-color': '#4caf50'
                                        });
                                        $('#liveBadge').removeClass(
                                            'badge-danger badge-warning').addClass(
                                            'badge-success animate__animated animate__pulse animate__infinite'
                                        );

                                        $('#liveLat').text(parseFloat(l.lat).toFixed(6));
                                        $('#liveLng').text(parseFloat(l.lng).toFixed(6));
                                        $('#liveSpeed').html(
                                            `<strong style="color:#2e7d32; font-size:2.8rem;">${l.speed || 0} <small style="font-size:1.2rem;">km/h</small></strong>`
                                        );
                                        $('#liveLastUpdate').html(
                                            `<span style="color:#2e7d32; font-weight:bold;">Updated: ${moment().format('DD/MM HH:mm:ss')}</span>`
                                        );
                                        $('#openGoogleMaps').attr('href',
                                                `https://www.google.com/maps?q=${l.lat},${l.lng}`
                                            ).removeClass('btn-danger btn-warning')
                                            .addClass('btn-success').html(
                                                '<i class="fa fa-map-marker-alt"></i> Open in Google Maps'
                                            ).show();

                                    } else {
                                        $('#liveTrackingSection').css({
                                            'background': '#ffebee',
                                            'border-left-color': '#d32f2f'
                                        });
                                        $('#liveBadge').removeClass(
                                            'badge-success animate__pulse animate__infinite'
                                        ).addClass('badge-danger');
                                        $('#liveSpeed').html(
                                            '<strong style="color:#c62828; font-size:2rem;">Offline</strong>'
                                        );
                                        $('#liveLastUpdate').html(
                                            '<span style="color:#c62828;">No signal</span>'
                                        );
                                        $('#openGoogleMaps').hide();
                                    }
                                }).fail(() => {
                                    $('#liveTrackingSection').css({
                                        'background': '#fff3e0',
                                        'border-left-color': '#ff9800'
                                    });
                                    $('#liveBadge').removeClass(
                                        'badge-success animate__pulse animate__infinite'
                                    ).addClass('badge-warning');
                                    $('#liveSpeed').html(
                                        '<strong style="color:#e65100;">Error</strong>');
                                    $('#liveLastUpdate').html(
                                        '<span style="color:#e65100;">Connection failed</span>'
                                    );
                                    $('#openGoogleMaps').hide();
                                });
                            };

                            updateLiveData();
                            window.liveTrackingInterval = setInterval(updateLiveData, 15000);
                        } else {
                            $('#liveTrackingSection').hide();
                        }

                        $('#viewTripModal').modal('show');

                    }).fail(function() {
                        alert('Error: Failed to connect to server');
                    });
                });

                $('#viewTripModal').on('hidden.bs.modal', function() {
                    if (window.liveTrackingInterval) {
                        clearInterval(window.liveTrackingInterval);
                        window.liveTrackingInterval = null;
                    }
                    $('#liveTrackingSection').hide();
                });


                // ========================================
                // START JOB - WITH INLINE BACKDATE OPTION (FLATPICKR)
                // ========================================
                $(document).on('click', '.startJobBtn', function(e) {
                    e.preventDefault();
                    const jobId = $(this).data('job-id');
                    const jobNo = $(this).data('job-no') || 'Internal Job';
                    const tripId = $(this).data('trip-id');

                    $.get(`/tracker/trips/${tripId}`, function(res) {
                        if (!res.success) {
                            Swal.fire('Error', 'Failed to load trip details', 'error');
                            return;
                        }

                        const trip = res.data;
                        const now = new Date();
                        const currentDate = moment(now).format('YYYY-MM-DD');
                        const currentTime = moment(now).format('HH:mm');

                        Swal.fire({
                            title: `Start Job: ${jobNo}?`,
                            html: `
                  <div style="margin-top: 20px;">
                    <button class="btn btn-success btn-lg btn-block" id="startNowBtn" type="button">
                        <i class="fa fa-play-circle"></i> Start Now
                        <br><small>Use current time</small>
                    </button>

                    <button class="btn btn-warning btn-lg btn-block mt-2" id="manualStartToggleBtn" type="button">
                        <i class="fa fa-clock"></i> Backdate Start
                        <br><small>Specify past start time</small>
                    </button>

                    <div id="manualStartForm" style="display: none; margin-top: 20px; text-align: left; padding: 15px; background: #fff3cd; border-radius: 8px; border: 2px solid #ffc107;">
                        <div class="alert alert-warning mb-3" style="padding: 8px 12px; font-size: 0.9rem;">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Backdating:</strong>
                            Enter the actual time this job was started in the past.
                        </div>

                        ${trip.start_date ? `
                                                                                <div class="alert alert-info mb-3" style="padding: 8px 12px; font-size: 0.9rem;">
                                                                                    <strong>Trip Started:</strong> ${moment(trip.start_date).format('DD/MM/YY HH:mm')}
                                                                                </div>
                                                                                ` : ''}

                        <div class="form-group mb-3">
                            <label style="font-weight: 600; margin-bottom: 5px;">
                                <i class="fa fa-calendar-alt"></i> Start Date & Time
                                <span style="color: #dc3545;">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="startJobDateTimeInput"
                                placeholder="Select date and time"
                                readonly
                                style="font-size: 1rem; background-color: #fff; cursor: pointer;"
                                required
                            >
                            <small class="text-muted">Cannot be in the future • Nairobi timezone (EAT)</small>
                        </div>

                        ${!trip.start_date ? `
                                                                                <div class="alert alert-info mb-3" style="padding: 8px 12px; font-size: 0.85rem;">
                                                                                    <i class="fa fa-info-circle"></i>
                                                                                    <strong>Note:</strong> This is the first job. The trip will be started automatically with this time.
                                                                                </div>
                                                                                ` : ''}

                        <button class="btn btn-success btn-block" id="submitManualStartBtn" type="button">
                            <i class="fa fa-play"></i> Start Job with this Time
                        </button>
                    </div>
                  </div>
                  `,
                            showConfirmButton: false,
                            showCancelButton: true,
                            cancelButtonText: 'Cancel',
                            width: '500px',
                            customClass: {
                                popup: 'swal-job-start'
                            },
                            didOpen: () => {
                                // Initialize Flatpickr after SweetAlert opens
                                const startDateTimePicker = flatpickr(
                                    "#startJobDateTimeInput", {
                                        enableTime: true,
                                        dateFormat: "Y-m-d H:i",
                                        time_24hr: true,
                                        maxDate: "today",
                                        defaultDate: now,
                                        minuteIncrement: 1,
                                        allowInput: false,
                                        clickOpens: true
                                    });
                            }
                        });

                        // Toggle manual start form
                        $(document).off('click', '#manualStartToggleBtn').on('click',
                            '#manualStartToggleBtn',
                            function() {
                                const $form = $('#manualStartForm');
                                const $btn = $(this);

                                if ($form.is(':visible')) {
                                    $form.slideUp(300);
                                    $btn.html(
                                        '<i class="fa fa-clock"></i> Backdate Start<br><small>Specify past start time</small>'
                                    );
                                    $('#startNowBtn').prop('disabled', false).removeClass(
                                        'btn-secondary').addClass('btn-success');
                                } else {
                                    $form.slideDown(300);
                                    $btn.html(
                                        '<i class="fa fa-times"></i> Cancel Backdate<br><small>Hide date/time selection</small>'
                                    );
                                    $('#startNowBtn').prop('disabled', true).addClass(
                                        'btn-secondary').removeClass('btn-success');
                                }
                            });

                        // Start now handler
                        $(document).off('click', '#startNowBtn').on('click', '#startNowBtn',
                            function() {
                                if ($(this).prop('disabled')) return;

                                Swal.close();

                                const $btn = $(`.startJobBtn[data-job-id="${jobId}"]`);
                                const $row = $btn.closest('tr');
                                const $actionCell = $btn.parent();

                                $btn.prop('disabled', true).html(
                                    '<i class="fa fa-spinner fa-spin"></i> Starting...');

                                $.ajax({
                                    url: `/tracker/trips/${tripId}/jobs/${jobId}/start`,
                                    type: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(res) {
                                        if (res.success) {
                                            const stopBtn = `
                            <button class="btn btn-sm btn-danger stopJobBtn"
                                    data-job-id="${jobId}"
                                    data-job-no="${jobNo}"
                                    data-trip-id="${tripId}">
                                <i class="fa fa-stop"></i> Stop
                            </button>
                        `;
                                            $actionCell.html(stopBtn);

                                            $row.find('td:eq(7)')
                                                .find('.badge')
                                                .removeClass(
                                                    'badge-secondary badge-info')
                                                .addClass('badge-warning')
                                                .text('In Transit');

                                            let message =
                                                `Job "${jobNo}" started successfully!`;
                                            if (res.trip_auto_started) {
                                                message +=
                                                    `\n\nTrip has been automatically started!\nLive tracking is now ACTIVE.`;
                                            } else {
                                                message +=
                                                    `\n\nTrip is already In Transit.`;
                                            }

                                            Swal.fire('Success!', message, 'success');

                                            if (typeof tripsTable !== 'undefined') {
                                                tripsTable.ajax.reload(null, false);
                                            }

                                            setTimeout(() => {
                                                $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                    .trigger('click');
                                            }, 500);
                                        }
                                    },
                                    error: function(xhr) {
                                        let msg = 'Failed to start job';
                                        if (xhr.responseJSON?.message) {
                                            msg = xhr.responseJSON.message;
                                        }
                                        Swal.fire('Error', msg, 'error');
                                        $btn.prop('disabled', false).html(
                                            '<i class="fa fa-play"></i> Start');
                                    }
                                });
                            });

                        // Submit manual start handler
                        $(document).off('click', '#submitManualStartBtn').on('click',
                            '#submitManualStartBtn',
                            function() {
                                const startDateTime = $('#startJobDateTimeInput').val();

                                console.log('Start DateTime:', startDateTime);

                                if (!startDateTime) {
                                    Swal.fire('Error', 'Please select a date and time', 'error');
                                    return;
                                }

                                // Split into date and time
                                const [startDate, startTime] = startDateTime.split(' ');

                                const $submitBtn = $(this);
                                $submitBtn.prop('disabled', true).html(
                                    '<i class="fa fa-spinner fa-spin"></i> Processing...');

                                $.ajax({
                                    url: `/tracker/trips/${tripId}/jobs/${jobId}/start`,
                                    type: 'POST',
                                    data: {
                                        start_date: startDate,
                                        start_time: startTime
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(res) {
                                        if (res.success) {
                                            Swal.close();

                                            let message =
                                                `Started: ${moment(startDateTime).format('DD/MM/YY HH:mm')}`;
                                            if (res.trip_auto_started) {
                                                message +=
                                                    `<br><br><strong>Trip was automatically started!</strong>`;
                                            }

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Job Started!',
                                                html: message,
                                                confirmButtonText: 'OK'
                                            });

                                            setTimeout(() => {
                                                $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                    .trigger('click');
                                            }, 500);

                                            if (typeof tripsTable !== 'undefined') {
                                                tripsTable.ajax.reload(null, false);
                                            }
                                        } else {
                                            Swal.fire('Failed', res.message ||
                                                'Unknown error', 'error');
                                        }
                                    },
                                    error: function(xhr) {
                                        let errorMsg = 'Server error';
                                        let errorTitle = 'Validation Error';

                                        if (xhr.responseJSON) {
                                            errorMsg = xhr.responseJSON.message ||
                                                'Server error';

                                            if (xhr.responseJSON.validation_type ===
                                                'overlap') {
                                                errorTitle = 'Job Timeline Conflict';
                                                errorMsg = `
                                <div style="text-align: left; font-size: 0.95rem;">
                                    <p style="font-size: 1.1rem; margin-bottom: 15px;">
                                        <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                                    </p>

                                    <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #856404;">
                                            <i class="fa fa-briefcase"></i> <strong>Conflicting Job:</strong>
                                            ${xhr.responseJSON.conflicting_job}
                                        </p>
                                        <p style="margin: 5px 0; color: #856404;">
                                            <i class="fa fa-clock"></i> <strong>Was In Transit:</strong><br>
                                            ${xhr.responseJSON.conflicting_job_start} → ${xhr.responseJSON.conflicting_job_end}
                                        </p>
                                    </div>

                                    <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #721c24;">
                                            <i class="fa fa-times-circle"></i> <strong>Your Start Time:</strong>
                                            ${xhr.responseJSON.your_start_time}
                                        </p>
                                    </div>

                                    <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #0c5460;">
                                            <i class="fa fa-lightbulb"></i> <strong>Solution:</strong><br>
                                            ${xhr.responseJSON.suggestion}
                                        </p>
                                    </div>
                                </div>
                            `;
                                            } else if (xhr.responseJSON
                                                .validation_type === 'trip_start') {
                                                errorTitle = 'Trip Timeline Conflict';
                                                errorMsg = `
                                <div style="text-align: left; font-size: 0.95rem;">
                                    <p style="font-size: 1.1rem; margin-bottom: 15px;">
                                        <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                                    </p>

                                    <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #0c5460;">
                                            <i class="fa fa-truck"></i> <strong>Trip Started:</strong>
                                            ${xhr.responseJSON.trip_started_at}
                                        </p>
                                    </div>

                                    <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #721c24;">
                                            <i class="fa fa-times-circle"></i> <strong>Your Start Time:</strong>
                                            ${xhr.responseJSON.your_start_time}
                                        </p>
                                    </div>

                                    <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #856404;">
                                            <i class="fa fa-info-circle"></i> Jobs must start at or after the trip start time.
                                        </p>
                                    </div>
                                </div>
                            `;
                                            }
                                        }

                                        Swal.fire({
                                            icon: 'error',
                                            title: errorTitle,
                                            html: errorMsg,
                                            confirmButtonText: 'OK',
                                            width: '600px',
                                            customClass: {
                                                popup: 'swal-wide'
                                            }
                                        });
                                    },
                                    complete: function() {
                                        $submitBtn.prop('disabled', false).html(
                                            '<i class="fa fa-play"></i> Start Job with this Time'
                                        );
                                    }
                                });
                            });
                    });
                });


                // ========================================
                // STOP JOB - WITH INLINE BACKDATE OPTION (FLATPICKR)
                // ========================================
                $(document).on('click', '.stopJobBtn', function() {
                    const jobId = $(this).data('job-id');
                    const jobNo = $(this).data('job-no');
                    const tripId = $(this).data('trip-id');

                    $.get(`/tracker/trips/${tripId}`, function(res) {
                        if (!res.success) {
                            Swal.fire('Error', 'Failed to load job details', 'error');
                            return;
                        }

                        const job = res.data.jobs.find(j => j.job_id == jobId);
                        if (!job || !job.start_date) {
                            Swal.fire('Error', 'Job start date not found', 'error');
                            return;
                        }

                        const jobStartDateTime = moment(job.start_date).format('YYYY-MM-DD HH:mm');
                        const now = new Date();

                        Swal.fire({
                            title: `Complete Job: ${jobNo}?`,
                            html: `
                    <div style="margin-top: 20px;">
                    <button class="btn btn-success btn-lg btn-block" id="completeNowBtn" type="button">
                        <i class="fa fa-check-circle"></i> Complete Now
                        <br><small>Use current time</small>
                    </button>

                    <button class="btn btn-warning btn-lg btn-block mt-2" id="backdateToggleBtn" type="button">
                        <i class="fa fa-clock"></i> Backdate Completion
                        <br><small>Specify past completion time</small>
                    </button>

                    <div id="backdateForm" style="display: none; margin-top: 20px; text-align: left; padding: 15px; background: #fff3cd; border-radius: 8px; border: 2px solid #ffc107;">
                        <div class="alert alert-info mb-3" style="padding: 8px 12px; font-size: 0.9rem;">
                            <strong>Job Started:</strong> ${moment(job.start_date).format('DD/MM/YY HH:mm')}
                        </div>

                        <div class="form-group mb-3">
                            <label style="font-weight: 600; margin-bottom: 5px;">
                                <i class="fa fa-calendar-alt"></i> Completion Date & Time
                                <span style="color: #dc3545;">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="stopJobDateTimeInput"
                                placeholder="Select date and time"
                                readonly
                                style="font-size: 1rem; background-color: #fff; cursor: pointer;"
                                required
                            >
                            <small class="text-muted">Must be on or after job start • Nairobi timezone (EAT)</small>
                        </div>

                        <button class="btn btn-warning btn-block" id="submitBackdateBtn" type="button">
                            <i class="fa fa-check"></i> Complete with this Time
                        </button>
                    </div>
                    </div>
                    `,
                            showConfirmButton: false,
                            showCancelButton: true,
                            cancelButtonText: 'Cancel',
                            width: '500px',
                            customClass: {
                                popup: 'swal-job-complete'
                            },
                            didOpen: () => {
                                // Initialize Flatpickr after SweetAlert opens
                                const stopDateTimePicker = flatpickr(
                                    "#stopJobDateTimeInput", {
                                        enableTime: true,
                                        dateFormat: "Y-m-d H:i",
                                        time_24hr: true,
                                        minDate: jobStartDateTime,
                                        defaultDate: now,
                                        minuteIncrement: 1,
                                        allowInput: false,
                                        clickOpens: true
                                    });
                            }
                        });

                        $(document).off('click', '#backdateToggleBtn').on('click', '#backdateToggleBtn',
                            function() {
                                const $form = $('#backdateForm');
                                const $btn = $(this);

                                if ($form.is(':visible')) {
                                    $form.slideUp(300);
                                    $btn.html(
                                        '<i class="fa fa-clock"></i> Backdate Completion<br><small>Specify past completion time</small>'
                                    );
                                    $('#completeNowBtn').prop('disabled', false).removeClass(
                                        'btn-secondary').addClass('btn-success');
                                } else {
                                    $form.slideDown(300);
                                    $btn.html(
                                        '<i class="fa fa-times"></i> Cancel Backdate<br><small>Hide date/time selection</small>'
                                    );
                                    $('#completeNowBtn').prop('disabled', true).addClass(
                                        'btn-secondary').removeClass('btn-success');
                                }
                            });

                        $(document).off('click', '#completeNowBtn').on('click', '#completeNowBtn',
                            function() {
                                if ($(this).prop('disabled')) return;

                                Swal.close();

                                const $btn = $(`.stopJobBtn[data-job-id="${jobId}"]`);
                                $btn.prop('disabled', true).html(
                                    '<i class="fa fa-spinner fa-spin"></i> Completing...');

                                $.ajax({
                                    url: `/tracker/trips/${tripId}/jobs/${jobId}/complete`,
                                    type: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(res) {
                                        if (res.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Job Completed!',
                                                html: `
                                <strong>Distance:</strong> ${res.total_distance || '0'} km<br>
                                <strong>Duration:</strong> ${res.duration_hours || '0'} hours<br>
                                <strong>Avg Speed:</strong> ${res.avg_speed || '0'} km/h
                                `,
                                                confirmButtonText: 'OK'
                                            });

                                            $('#viewTripModal').modal('hide');
                                            setTimeout(() => {
                                                $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                    .trigger('click');
                                            }, 400);

                                            if (typeof tripsTable !== 'undefined') {
                                                tripsTable.ajax.reload(null, false);
                                            }
                                        } else {
                                            Swal.fire('Failed', res.message ||
                                                'Unknown error', 'error');
                                            $btn.prop('disabled', false).html(
                                                '<i class="fa fa-stop"></i> Stop');
                                        }
                                    },
                                    error: function() {
                                        Swal.fire('Error', 'Server error occurred',
                                            'error');
                                        $btn.prop('disabled', false).html(
                                            '<i class="fa fa-stop"></i> Stop');
                                    }
                                });
                            });

                        $(document).off('click', '#submitBackdateBtn').on('click', '#submitBackdateBtn',
                            function() {
                                const endDateTime = $('#stopJobDateTimeInput').val();

                                console.log('End DateTime:', endDateTime);

                                if (!endDateTime) {
                                    Swal.fire('Error', 'Please select a date and time', 'error');
                                    return;
                                }

                                // Split into date and time
                                const [endDate, endTime] = endDateTime.split(' ');

                                const $submitBtn = $(this);
                                $submitBtn.prop('disabled', true).html(
                                    '<i class="fa fa-spinner fa-spin"></i> Processing...');

                                $.ajax({
                                    url: `/tracker/trips/${tripId}/jobs/${jobId}/complete`,
                                    type: 'POST',
                                    data: {
                                        end_date: endDate,
                                        end_time: endTime
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(res) {
                                        if (res.success) {
                                            Swal.close();

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Job Completed!',
                                                html: `
                                <strong>Completed:</strong> ${moment(endDateTime).format('DD/MM/YY HH:mm')}<br>
                                <strong>Distance:</strong> ${res.total_distance || '0'} km<br>
                                <strong>Duration:</strong> ${res.duration_hours || '0'} hours
                                `,
                                                confirmButtonText: 'OK'
                                            });

                                            setTimeout(() => {
                                                $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                    .trigger('click');
                                            }, 500);

                                            if (typeof tripsTable !== 'undefined') {
                                                tripsTable.ajax.reload(null, false);
                                            }
                                        } else {
                                            Swal.fire('Failed', res.message ||
                                                'Unknown error', 'error');
                                        }
                                    },
                                    error: function(xhr) {
                                        let errorMsg = 'Server error';
                                        let errorTitle = 'Validation Error';

                                        if (xhr.responseJSON) {
                                            errorMsg = xhr.responseJSON.message ||
                                                'Server error';

                                            if (xhr.responseJSON.validation_type ===
                                                'overlap_end') {
                                                errorTitle = 'Job Timeline Conflict';
                                                errorMsg = `
                                <div style="text-align: left; font-size: 0.95rem;">
                                    <p style="font-size: 1.1rem; margin-bottom: 15px;">
                                        <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                                    </p>

                                    <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #856404;">
                                            <i class="fa fa-briefcase"></i> <strong>Conflicting Job:</strong>
                                            ${xhr.responseJSON.conflicting_job}
                                        </p>
                                        <p style="margin: 5px 0; color: #856404;">
                                            <i class="fa fa-clock"></i> <strong>Was In Transit:</strong><br>
                                            ${xhr.responseJSON.conflicting_job_start} → ${xhr.responseJSON.conflicting_job_end}
                                        </p>
                                    </div>

                                    <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #721c24;">
                                            <i class="fa fa-times-circle"></i> <strong>Your End Time:</strong>
                                            ${xhr.responseJSON.your_end_time}
                                        </p>
                                    </div>

                                    <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                                        <p style="margin: 5px 0; color: #0c5460;">
                                            <i class="fa fa-lightbulb"></i> <strong>Solution:</strong><br>
                                            ${xhr.responseJSON.suggestion}
                                        </p>
                                    </div>
                                </div>
                            `;
                                            }
                                        }

                                        Swal.fire({
                                            icon: 'error',
                                            title: errorTitle,
                                            html: errorMsg,
                                            confirmButtonText: 'OK',
                                            width: '600px'
                                        });
                                    },
                                    complete: function() {
                                        $submitBtn.prop('disabled', false).html(
                                            '<i class="fa fa-check"></i> Complete with this Time'
                                        );
                                    }
                                });
                            });
                    });
                });

                // ========================================
                // BACKDATE JOB FORM SUBMIT
                // ========================================
                $('#backdateJobForm').submit(function(e) {
                    e.preventDefault();

                    const jobId = $('#backdateJobId').val();
                    const tripId = $('#backdateTripId').val();
                    const endDate = $('#backdateJobDate').val();
                    const endTime = $('#backdateJobTime').val();

                    if (!endDate || !endTime) {
                        return Swal.fire('Error', 'Please enter both date and time', 'error');
                    }

                    $('#submitBackdateJob').prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Processing...'
                    );

                    $.ajax({
                        url: `/tracker/trips/${tripId}/jobs/${jobId}/complete`, // SAME ENDPOINT!
                        type: 'POST',
                        data: {
                            end_date: endDate,
                            end_time: endTime
                        }, // Just add these parameters
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                $('#backdateJobModal').modal('hide');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Job Completed!',
                                    html: `
                        <strong>Completed:</strong> ${endDate} ${endTime}<br>
                        <strong>Distance:</strong> ${res.total_distance || '0'} km<br>
                        <strong>Duration:</strong> ${res.duration_hours || '0'} hours
                        `,
                                    confirmButtonText: 'OK'
                                });

                                setTimeout(() => {
                                    $(`.viewTrip[data-trip-id="${tripId}"]`).trigger(
                                        'click');
                                }, 500);

                                if (typeof tripsTable !== 'undefined') {
                                    tripsTable.ajax.reload(null, false);
                                }
                            } else {
                                Swal.fire('Failed', res.message || 'Unknown error', 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Server error';
                            let errorTitle = 'Validation Error';

                            if (xhr.responseJSON) {
                                errorMsg = xhr.responseJSON.message || 'Server error';

                                // ✅ VALIDATION TYPE 1: Overlapping job error (for completion)
                                if (xhr.responseJSON.validation_type === 'overlap_end') {
                                    errorTitle = 'Job Timeline Conflict';
                                    errorMsg = `
                    <div style="text-align: left; font-size: 0.95rem;">
                    <p style="font-size: 1.1rem; margin-bottom: 15px;">
                        <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                    </p>

                    <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                        <p style="margin: 5px 0; color: #856404;">
                            <i class="fa fa-briefcase"></i> <strong>Conflicting Job:</strong>
                            ${xhr.responseJSON.conflicting_job}
                        </p>
                        <p style="margin: 5px 0; color: #856404;">
                            <i class="fa fa-clock"></i> <strong>Was In Transit:</strong><br>
                            ${xhr.responseJSON.conflicting_job_start} → ${xhr.responseJSON.conflicting_job_end}
                        </p>
                    </div>

                    <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                        <p style="margin: 5px 0; color: #721c24;">
                            <i class="fa fa-times-circle"></i> <strong>Your End Time:</strong>
                            ${xhr.responseJSON.your_end_time}
                        </p>
                    </div>

                    <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                        <p style="margin: 5px 0; color: #0c5460;">
                            <i class="fa fa-lightbulb"></i> <strong>Solution:</strong><br>
                            ${xhr.responseJSON.suggestion}
                        </p>
                    </div>
                    </div>
                    `;
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: errorTitle,
                                html: errorMsg,
                                confirmButtonText: 'OK',
                                width: '600px'
                            });
                        },
                        complete: function() {
                            $('#submitBackdateJob').prop('disabled', false).html(
                                '<i class="fa fa-check"></i> Complete Job'
                            );
                        }
                    });
                });

                $('.modal').on('hidden.bs.modal', function() {
                    $(this).find('form')[0]?.reset();
                });
            });


            // ========================================
            // MANUAL START JOB FORM SUBMIT - WITH ENHANCED ERROR DISPLAY
            // ========================================
            $('#manualStartJobForm').submit(function(e) {
                e.preventDefault();

                const jobId = $('#manualStartJobId').val();
                const tripId = $('#manualStartTripId').val();
                const startDate = $('#manualStartJobDate').val();
                const startTime = $('#manualStartJobTime').val();

                if (!startDate || !startTime) {
                    return Swal.fire('Error', 'Please enter both date and time', 'error');
                }

                $('#submitManualStartJob').prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Processing...'
                );

                $.ajax({
                    url: `/tracker/trips/${tripId}/jobs/${jobId}/start`,
                    type: 'POST',
                    data: {
                        start_date: startDate,
                        start_time: startTime
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#manualStartJobModal').modal('hide');

                            let message = `Started: ${startDate} ${startTime}`;
                            if (res.trip_auto_started) {
                                message += `<br><br><strong>Trip was automatically started!</strong>`;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Job Started!',
                                html: message,
                                confirmButtonText: 'OK'
                            });

                            setTimeout(() => {
                                $(`.viewTrip[data-trip-id="${tripId}"]`).trigger('click');
                            }, 500);

                            if (typeof tripsTable !== 'undefined') {
                                tripsTable.ajax.reload(null, false);
                            }
                        } else {
                            Swal.fire('Failed', res.message || 'Unknown error', 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Server error';
                        let errorTitle = 'Validation Error';

                        if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.message || 'Server error';

                            // ✅ VALIDATION TYPE 1: Overlapping job error
                            if (xhr.responseJSON.validation_type === 'overlap') {
                                errorTitle = 'Job Timeline Conflict';
                                errorMsg = `
                        <div style="text-align: left; font-size: 0.95rem;">
                            <p style="font-size: 1.1rem; margin-bottom: 15px;">
                                <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                            </p>

                            <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                                <p style="margin: 5px 0; color: #856404;">
                                    <i class="fa fa-briefcase"></i> <strong>Conflicting Job:</strong>
                                    ${xhr.responseJSON.conflicting_job}
                                </p>
                                <p style="margin: 5px 0; color: #856404;">
                                    <i class="fa fa-clock"></i> <strong>Was In Transit:</strong><br>
                                    ${xhr.responseJSON.conflicting_job_start} → ${xhr.responseJSON.conflicting_job_end}
                                </p>
                            </div>

                            <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                                <p style="margin: 5px 0; color: #721c24;">
                                    <i class="fa fa-times-circle"></i> <strong>Your Start Time:</strong>
                                    ${xhr.responseJSON.your_start_time}
                                </p>
                            </div>

                            <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                                <p style="margin: 5px 0; color: #0c5460;">
                                    <i class="fa fa-lightbulb"></i> <strong>Solution:</strong><br>
                                    ${xhr.responseJSON.suggestion}
                                </p>
                            </div>
                        </div>
                    `;
                            }
                            // ✅ VALIDATION TYPE 2: Trip start time error
                            else if (xhr.responseJSON.validation_type === 'trip_start') {
                                errorTitle = 'Trip Timeline Conflict';
                                errorMsg = `
                        <div style="text-align: left; font-size: 0.95rem;">
                            <p style="font-size: 1.1rem; margin-bottom: 15px;">
                                <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                            </p>

                            <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                                <p style="margin: 5px 0; color: #0c5460;">
                                    <i class="fa fa-truck"></i> <strong>Trip Started:</strong>
                                    ${xhr.responseJSON.trip_started_at}
                                </p>
                            </div>

                            <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                                <p style="margin: 5px 0; color: #721c24;">
                                    <i class="fa fa-times-circle"></i> <strong>Your Start Time:</strong>
                                    ${xhr.responseJSON.your_start_time}
                                </p>
                            </div>

                            <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                                <p style="margin: 5px 0; color: #856404;">
                                    <i class="fa fa-info-circle"></i> Jobs must start at or after the trip start time.
                                </p>
                            </div>
                        </div>
                    `;
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: errorTitle,
                            html: errorMsg,
                            confirmButtonText: 'OK',
                            width: '600px',
                            customClass: {
                                popup: 'swal-wide'
                            }
                        });
                    },
                    complete: function() {
                        $('#submitManualStartJob').prop('disabled', false).html(
                            '<i class="fa fa-check"></i> Start Job'
                        );
                    }
                });
            });



            // ========================================
            // MANUAL JOB ENTRY - ✅ HISTORICAL
            // ========================================

            function loadManualJobLocations() {
                $.get('/tracker/locations', {
                    type: 'Road',
                    include_country: 1,
                    country_code: 'KE'
                }, function(res) {
                    if (res.success && res.data.length > 0) {
                        let options = '<option value="">Select Location</option>';
                        res.data.forEach(location => {
                            options += `<option value="${location.code}">${location.name}</option>`;
                        });
                        $('#manualStartLocation').html(options);
                        $('#manualEndLocation').html(options);
                    }
                });
            }

            function loadManualCustomerJobs() {
                const vehicleNo = $('#manualJobVehicle').text();

                if (!vehicleNo || vehicleNo === '-') {
                    $('#manualCustomerJob').html('<option value="">Vehicle not assigned</option>');
                    return;
                }

                $.get('/tracker/jobs/available', {
                    vehicle_no: vehicleNo
                }, function(res) {
                    if (res.success && res.data.length > 0) {
                        let options = '<option value="">Select Customer Job</option>';
                        res.data.forEach(job => {
                            options += `<option value="${job.id}"
                                data-customer="${job.customer_name}"
                                data-phone="${job.customer_phone}"
                                data-pickup="${job.pickup_location}"
                                data-pickup-code="${job.pickup_location_code}"
                                data-delivery="${job.delivery_location}"
                                data-delivery-code="${job.delivery_location_code}">
                                ${job.job_number} - ${job.pickup_location} → ${job.delivery_location}
                            </option>`;
                        });
                        $('#manualCustomerJob').html(options);
                    }
                });
            }

            $(document).on('click', '#addManualJobBtn', function() {
                const tripId = $('#viewTripModal').data('trip-id');

                if (!tripId || !/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(tripId)) {
                    alert('Error: Invalid trip ID');
                    return;
                }

                $('#manualJobTripId').val(tripId);
                $('#manualJobTripNo').text($('#viewTripNo').text());
                $('#manualJobVehicle').text($('#viewVehicle').text());
                $('#manualJobStatus').text($('#viewStatus').text());
                $('#manualJobDriver').text($('#viewDriver').text());

                $('#manualJobForm')[0].reset();
                $('#manualInternalFields').hide();
                $('#manualCustomerFields').hide();
                $('#manualJobPreview').hide();

                const now = new Date();
                const yesterday = new Date(now);
                yesterday.setDate(yesterday.getDate() - 1);

                $('#manualStartDate').val(yesterday.toISOString().slice(0, 16));
                $('#manualEndDate').val(now.toISOString().slice(0, 16));

                $('#viewTripModal').modal('hide');
                setTimeout(() => {
                    $('#manualJobModal').modal('show');
                }, 300);
            });

            $('#manualJobType').change(function() {
                const jobType = $(this).val();

                if (jobType === 'internal') {
                    $('#manualInternalFields').slideDown();
                    $('#manualCustomerFields').slideUp();
                    $('#manualStartLocation').prop('required', true);
                    $('#manualEndLocation').prop('required', true);
                    $('#manualCustomerJob').prop('required', false);
                    loadManualJobLocations();

                } else if (jobType === 'customer_delivery') {
                    $('#manualCustomerFields').slideDown();
                    $('#manualInternalFields').slideUp();
                    $('#manualCustomerJob').prop('required', true);
                    $('#manualStartLocation').prop('required', false);
                    $('#manualEndLocation').prop('required', false);
                    loadManualCustomerJobs();

                } else {
                    $('#manualInternalFields').hide();
                    $('#manualCustomerFields').hide();
                }
            });

            $('#manualCustomerJob').change(function() {
                if ($(this).val()) {
                    const selected = $(this).find(':selected');
                    $('#manualPreviewCustomer').text(selected.data('customer'));
                    $('#manualPreviewPhone').text(selected.data('phone'));
                    $('#manualPreviewPickup').text(selected.data('pickup'));
                    $('#manualPreviewDelivery').text(selected.data('delivery'));
                    $('#manualJobPreview').slideDown();
                } else {
                    $('#manualJobPreview').slideUp();
                }
            });

            $('#manualJobForm').submit(function(e) {
                e.preventDefault();

                const jobType = $('#manualJobType').val();
                const startDate = $('#manualStartDate').val();
                const endDate = $('#manualEndDate').val();
                const tripId = $('#manualJobTripId').val();

                if (!jobType || !startDate || !endDate) {
                    return alert('Please fill all required fields');
                }

                if (new Date(endDate) <= new Date(startDate)) {
                    return alert('End date must be after start date');
                }

                let jobs = [];

                if (jobType === 'internal') {
                    const startLoc = $('#manualStartLocation').val();
                    const endLoc = $('#manualEndLocation').val();

                    if (!startLoc || !endLoc) {
                        return alert('Please select locations');
                    }

                    jobs.push({
                        job_type: 'Internal Job',
                        job_no: '',
                        start_location_code: startLoc,
                        end_location_code: endLoc,
                        is_historical: true
                    });

                } else if (jobType === 'customer_delivery') {
                    const selectedJob = $('#manualCustomerJob').val();
                    if (!selectedJob) {
                        return alert('Please select a customer job');
                    }

                    const selectedOption = $('#manualCustomerJob option:selected');
                    jobs.push({
                        job_type: 'Customer Delivery',
                        job_no: selectedOption.text().split(' - ')[0],
                        start_location_code: selectedOption.data('pickup-code'),
                        end_location_code: selectedOption.data('delivery-code'),
                        is_historical: true
                    });
                }

                $('#submitManualJob').prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: `/tracker/trips/${tripId}/jobs/add`,
                    type: 'POST',
                    data: JSON.stringify({
                        trip_id: tripId,
                        jobs: jobs
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(addResponse) {
                        if (!addResponse.success) {
                            $('#submitManualJob').prop('disabled', false).html(
                                '<i class="fa fa-save"></i> Save Historical Job');
                            return alert('Failed: ' + addResponse.message);
                        }

                        $.get(`/tracker/trips/${tripId}`, function(tripDetails) {
                            if (!tripDetails.success) {
                                $('#submitManualJob').prop('disabled', false).html(
                                    '<i class="fa fa-save"></i> Save Historical Job');
                                return alert('Failed: Could not retrieve job details');
                            }

                            const newJob = tripDetails.data.jobs
                                .filter(j => !j.start_date && !j.end_date)
                                .sort((a, b) => b.job_id - a.job_id)[0];

                            if (!newJob) {
                                $('#submitManualJob').prop('disabled', false).html(
                                    '<i class="fa fa-save"></i> Save Historical Job');
                                return alert('Failed: Could not find newly created job');
                            }

                            $.ajax({
                                url: `/tracker/trips/${tripId}/jobs/${newJob.job_id}/complete`,
                                type: 'POST',
                                data: JSON.stringify({
                                    is_historical: true,
                                    custom_start_date: startDate,
                                    custom_end_date: endDate
                                }),
                                contentType: 'application/json',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        $('#manualJobModal').modal('hide');

                                        alert(
                                            `Success!\n\nHistorical job added!\n\nJob: ${res.job_no}\nDistance: ${res.total_distance} km\nDuration: ${res.duration_hours} hours\nAvg Speed: ${res.avg_speed} km/h`
                                        );

                                        setTimeout(() => {
                                            $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                .trigger('click');
                                        }, 500);

                                    } else {
                                        alert('Failed: ' + res.message);
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Failed to complete job';
                                    let errorTitle = 'Error';

                                    if (xhr.responseJSON) {
                                        errorMsg = xhr.responseJSON.message ||
                                            'Server error';

                                        // ✅ Handle overlap validation
                                        if (xhr.responseJSON.validation_type ===
                                            'overlap_end') {
                                            errorTitle = 'Job Timeline Conflict';
                                            errorMsg = `
                 <div style="text-align: left; font-size: 0.95rem;">
                    <p style="font-size: 1.1rem; margin-bottom: 15px;">
                        <strong><i class="fa fa-exclamation-triangle"></i> ${xhr.responseJSON.message}</strong>
                    </p>

                    <div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; border-radius: 5px; margin: 15px 0;">
                        <p style="margin: 5px 0; color: #856404;">
                            <i class="fa fa-briefcase"></i> <strong>Conflicting Job:</strong>
                            ${xhr.responseJSON.conflicting_job}
                        </p>
                        <p style="margin: 5px 0; color: #856404;">
                            <i class="fa fa-clock"></i> <strong>Was In Transit:</strong><br>
                            ${xhr.responseJSON.conflicting_job_start} → ${xhr.responseJSON.conflicting_job_end}
                        </p>
                    </div>

                    <div style="background: #f8d7da; padding: 12px; border-left: 4px solid #dc3545; border-radius: 5px; margin: 15px 0;">
                        <p style="margin: 5px 0; color: #721c24;">
                            <i class="fa fa-times-circle"></i> <strong>Your End Time:</strong>
                            ${xhr.responseJSON.your_end_time}
                        </p>
                    </div>

                    <div style="background: #d1ecf1; padding: 12px; border-left: 4px solid #17a2b8; border-radius: 5px; margin: 15px 0;">
                        <p style="margin: 5px 0; color: #0c5460;">
                            <i class="fa fa-lightbulb"></i> <strong>Solution:</strong><br>
                            ${xhr.responseJSON.suggestion}
                        </p>
                    </div>
                    </div>
                 `;
                                        }
                                    }

                                    Swal.fire({
                                        icon: 'error',
                                        title: errorTitle,
                                        html: errorMsg,
                                        confirmButtonText: 'OK',
                                        width: '600px'
                                    });
                                },
                                complete: () => {
                                    $('#submitManualJob').prop('disabled', false).html(
                                        '<i class="fa fa-save"></i> Save Historical Job'
                                    );
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add job'));
                        $('#submitManualJob').prop('disabled', false).html(
                            '<i class="fa fa-save"></i> Save Historical Job');
                    }
                });
            });
        </script>

        
        <script>
            $(document).ready(function() {
                // ========================================
                // NOTIFICATIONS MODAL
                // ========================================

                let notificationsData = [];

                // Open notifications modal
                $('#openNotificationsBtn').click(function() {
                    $('#notificationsModal').modal('show');
                    loadNotifications();
                    loadNotificationStats();
                });

                // Refresh notifications
                $('#refreshNotificationsBtn').click(function() {
                    loadNotifications();
                    loadNotificationStats();
                });

                // Load notification stats
                function loadNotificationStats() {
                    $.get('/tracker/notifications/get', {
                        summary: true
                    }, function(res) {
                        if (res.success) {
                            // Count stats from the data
                            const all = res.data || [];
                            const newCount = all.filter(n => n.Status === 'New').length;
                            const geofenceCount = all.filter(n => n.AlarmType === '1006').length;
                            const ignitionCount = all.filter(n => ['1001', '1002'].includes(n.AlarmType))
                            .length;
                            const powerCount = all.filter(n => ['2', '1003'].includes(n.AlarmType)).length;

                            $('#statTotal').text(all.length);
                            $('#statNew').text(newCount);
                            $('#statGeofence').text(geofenceCount);
                            $('#statIgnition').text(ignitionCount);
                            $('#statPower').text(powerCount);

                            // Update unread badge on button
                            if (newCount > 0) {
                                $('#unreadBadge').text(newCount).show();
                            } else {
                                $('#unreadBadge').hide();
                            }
                        }
                    }).fail(function() {
                        console.error('Failed to load notification stats');
                    });
                }

                // Load notifications
                function loadNotifications() {
                    const filters = {
                        category: $('#filterCategory').val(),
                        vehicle_no: $('#filterVehicle').val(),
                        from_date: $('#filterFromDate').val(),
                        to_date: $('#filterToDate').val(),
                        status: $('#filterStatus').val()
                    };

                    $('#refreshNotificationsBtn').html('<i class="fa fa-spinner fa-spin"></i> Loading...');

                    $.get('/tracker/notifications/get', filters, function(res) {
                        if (res.success) {
                            notificationsData = res.data || [];
                            renderNotifications();
                        }
                        $('#refreshNotificationsBtn').html('<i class="fa fa-sync"></i> Refresh');
                    }).fail(function() {
                        $('#refreshNotificationsBtn').html('<i class="fa fa-sync"></i> Refresh');
                        Swal.fire('Error', 'Failed to load notifications', 'error');
                    });
                }

                // Render notifications in tabs
                function renderNotifications() {
                    const all = notificationsData;
                    const geofence = all.filter(n => n.AlarmType === '1006');
                    const ignition = all.filter(n => ['1001', '1002'].includes(n.AlarmType));
                    const power = all.filter(n => ['2', '1003'].includes(n.AlarmType));
                    const other = all.filter(n => !['1006', '1001', '1002', '2', '1003'].includes(n.AlarmType));

                    // Update counts
                    $('#countAll').text(all.length);
                    $('#countGeofence').text(geofence.length);
                    $('#countIgnition').text(ignition.length);
                    $('#countPower').text(power.length);
                    $('#countOther').text(other.length);

                    // Render each category
                    $('#allNotificationsContainer').html(buildNotificationCards(all));
                    $('#geofenceNotificationsContainer').html(buildNotificationCards(geofence));
                    $('#ignitionNotificationsContainer').html(buildNotificationCards(ignition));
                    $('#powerNotificationsContainer').html(buildNotificationCards(power));
                    $('#otherNotificationsContainer').html(buildNotificationCards(other));
                }

                // Build notification cards
                function buildNotificationCards(notifications) {
                    if (notifications.length === 0) {
                        return '<div class="alert alert-info text-center"><i class="fa fa-info-circle"></i> No notifications in this category</div>';
                    }

                    let html = '';
                    notifications.forEach(notif => {
                        const isNew = notif.Status === 'New';
                        const bgClass = isNew ? 'bg-warning' : 'bg-light';
                        const badgeClass = getAlarmBadge(notif.AlarmType);

                        html += `
            <div class="card mb-2 ${bgClass}" style="border-left: 4px solid ${isNew ? '#ffc107' : '#6c757d'};">
                <div class="card-body p-2">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <small class="text-muted">
                                <i class="fa fa-clock"></i>
                                ${moment(notif.AlarmTime).format('DD/MM/YY HH:mm:ss')}
                            </small>
                        </div>
                        <div class="col-md-2">
                            <strong>${notif.DeviceName || notif.VehicleNo || 'Unknown'}</strong>
                            ${notif.TripNo ? '<br><small class="text-muted">Trip: ' + notif.TripNo + '</small>' : ''}
                        </div>
                        <div class="col-md-3">
                            <span class="badge ${badgeClass}">
                                ${notif.AlarmName}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">
                                <i class="fa fa-map-marker-alt"></i>
                                ${parseFloat(notif.Latitude).toFixed(6)}, ${parseFloat(notif.Longitude).toFixed(6)}
                            </small>
                        </div>
                        <div class="col-md-3 text-right">
                            <a href="https://www.google.com/maps?q=${notif.Latitude},${notif.Longitude}"
                               target="_blank"
                               class="btn btn-sm btn-info"
                               title="View on Map">
                                <i class="fa fa-map"></i>
                            </a>
                            ${isNew ? `
                                        <button class="btn btn-sm btn-success markReadBtn"
                                                data-id="${notif.Id}"
                                                title="Mark as Read">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    ` : ''}
                            <button class="btn btn-sm btn-danger deleteNotifBtn"
                                    data-id="${notif.Id}"
                                    title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
         `;
                    });

                    return html;
                }

                // Get badge class for alarm type
                function getAlarmBadge(alarmType) {
                    const badges = {
                        '1006': 'badge-warning', // Geofence
                        '1001': 'badge-secondary', // ACC OFF
                        '1002': 'badge-primary', // ACC ON
                        '2': 'badge-danger', // Power Cut
                        '3': 'badge-info', // Vibration
                        '1003': 'badge-dark', // Offline
                        '1004': 'badge-success' // Parking
                    };
                    return badges[alarmType] || 'badge-secondary';
                }

                // Filter notifications
                $('#notificationFiltersForm').submit(function(e) {
                    e.preventDefault();
                    loadNotifications();
                });

                // Clear filters
                $('#clearNotificationFilters').click(function() {
                    $('#notificationFiltersForm')[0].reset();
                    loadNotifications();
                });

                // Mark as read
                $(document).on('click', '.markReadBtn', function() {
                    const id = $(this).data('id');

                    $.post(`/tracker/notifications/${id}/read`, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).done(function(res) {
                        if (res.success) {
                            loadNotifications();
                            loadNotificationStats();
                        }
                    }).fail(function() {
                        Swal.fire('Error', 'Failed to mark as read', 'error');
                    });
                });

                // Delete notification
                $(document).on('click', '.deleteNotifBtn', function() {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Delete Notification?',
                        text: 'This action cannot be undone',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/tracker/notifications/${id}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        loadNotifications();
                                        loadNotificationStats();
                                        Swal.fire('Deleted!',
                                            'Notification deleted successfully',
                                            'success');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Failed to delete notification',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // Auto-refresh stats every 60 seconds
                setInterval(loadNotificationStats, 60000);

                // Load initial stats on page load
                loadNotificationStats();
            });
        </script>
    @endsection

@extends('adminlte::page')

@section('title', 'GPS Trip Tracking')

@section('content_header')
    <h1>Truck Tracking</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/tracker/tracker.css') }}">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Status Cards Row -->
        <div class="row justify-content-start">

            <div class="col-md-3 col-lg-3 col-xl-auto p-1 m-0 flex-fill">
                <div class="card bg-created order-card mb-1" id="createdCard" style="cursor: pointer;">
                    <div class="card-block">
                        <h6 class="m-b-20">Created</h6>
                        <h2 class="text-right">
                            <i class="fa fa-plus-circle f-left"></i>
                            <span>{{ $stats->created ?? 0 }}</span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-lg-3 col-xl-auto p-1 m-0 flex-fill">
                <div class="card bg-transit order-card mb-1" id="inProgressCard" style="cursor: pointer;">
                    <div class="card-block">
                        <h6 class="m-b-20">In Progress</h6>
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
            <!-- View Trip Modal -->
            <div class="modal fade" id="viewTripModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <!-- HEADER -->
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
                                    <!-- ✅ NEW BUTTON -->
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

                            <!-- LIVE GPS TRACKING - ONLY SHOWS WHEN IN PROGRESS -->
                            <!-- LIVE TRACKING SECTION — BEAUTIFUL & PULSING GREEN WHEN ONLINE -->
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

                        <!-- FOOTER -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- New trip --}}
            <div class="modal fade" id="createTripModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Create New Trip</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form id="createTripForm">
                            <div class="modal-body">

                                <!-- Vehicle Selection -->
                                <div class="form-group">
                                    <label>Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tripVehicle" name="imei" required>
                                        <option value="">Loading vehicles...</option>
                                    </select>
                                    <input type="hidden" id="tripVehicleNo" name="vehicle_no">
                                </div>

                                <!-- Start Location -->
                                <div class="form-group">
                                    <label>Start Location <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tripStartLocation" name="start_location_code"
                                        required>
                                        <option value="">Loading locations...</option>
                                    </select>
                                    <small class="text-muted">Where the trip begins</small>
                                </div>

                                <!-- End Location -->
                                <div class="form-group">
                                    <label>End Location <span class="text-danger">*</span></label>
                                    <select class="form-control" id="tripEndLocation" name="end_location_code" required>
                                        <option value="">Loading locations...</option>
                                    </select>
                                    <small class="text-muted">Final destination</small>
                                </div>

                                <!-- Driver Details -->
                                <div class="form-group">
                                    <label>Driver Name</label>
                                    <input type="text" class="form-control" id="driverName" name="driver_name"
                                        placeholder="Enter driver name">
                                </div>

                                <div class="form-group">
                                    <label>Driver Phone</label>
                                    <input type="tel" class="form-control" id="driverPhone" name="driver_phone"
                                        placeholder="0712345678">
                                </div>

                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <strong>Note:</strong> Trip will be created with
                                    "Created" status.
                                    You can add jobs later using the "Add Jobs" button.
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

            <!-- Add Jobs Modal - FIXED WITH SCROLLING -->
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

                                <!-- Trip Info - Compact -->
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

                                <!-- Job Type Selection -->
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

                                <!-- INTERNAL JOB SECTION -->
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

                                    <div class="form-group">
                                        <label>Job Description</label>
                                        <textarea class="form-control form-control-sm" id="jobDescription" name="description" rows="2"
                                            placeholder="E.g., Pick up 50 cartons of electronics"></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Contact Person</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="jobContactPerson" name="contact_person"
                                                    placeholder="Person at pickup location">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label>Contact Phone</label>
                                                <input type="tel" class="form-control form-control-sm"
                                                    id="jobContactPhone" name="contact_phone" placeholder="0712345678">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- CUSTOMER DELIVERY SECTION -->
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

                                    <!-- Job Details Preview -->
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
            <!-- ✅ NEW MODAL STARTS HERE -->
            <div class="modal fade" id="manualJobModal" tabindex="-1" data-backdrop="static" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
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

                                <!-- Trip Info Display -->
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
                                    Use this to record jobs that have already been completed. GPS data will be fetched for
                                    the specified date range.
                                </div>

                                <!-- Job Type -->
                                <div class="form-group">
                                    <label>Job Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="manualJobType" name="job_type" required>
                                        <option value="">Select Job Type</option>
                                        <option value="internal">Internal Job (Pickup goods)</option>
                                        <option value="customer_delivery">Customer Delivery</option>
                                    </select>
                                </div>

                                <!-- Date & Time Section -->
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

                                <!-- Internal Job Fields -->
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

                                    <div class="form-group">
                                        <label>Job Description</label>
                                        <textarea class="form-control" id="manualDescription" name="description" rows="2"
                                            placeholder="E.g., Picked up cargo from warehouse"></textarea>
                                    </div>
                                </div>

                                <!-- Customer Delivery Fields -->
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

                                    <!-- Job Preview -->
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

        </div>
    @endsection

    @section('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        <script>
            // Global IMEI map from Laravel
            window.VEHICLE_IMEI_MAP = @json($finalMap ?? []);

            // Convert any vehicle format → real IMEI
            function getRealImei(vehicleNo) {
                if (!vehicleNo || !window.VEHICLE_IMEI_MAP) return null;

                // Try exact match first
                if (window.VEHICLE_IMEI_MAP[vehicleNo]) {
                    return window.VEHICLE_IMEI_MAP[vehicleNo];
                }

                // Fallback: clean plate
                const clean = vehicleNo.split(' - ')[0].trim();
                return window.VEHICLE_IMEI_MAP[clean] || null;
            }
        </script>
        <script>
            $(document).ready(function() {
                let currentFilter = 'all';

                // Initialize DataTable
                let tripsTable = $('#tripsTable').DataTable({
                    processing: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [3, 'desc']
                    ],
                    ajax: {
                        url: '/tracker/trips/get/' + currentFilter,
                        type: 'GET',
                        dataSrc: 'data',
                        error: function(xhr) {
                            console.error('DataTable error:', xhr);
                            swal('Error', 'Failed to load trips', 'error');
                        }
                    },
                    columns: [{
                            data: 'trip_no'
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
                                    return '<small class="text-warning">In Progress...</small>';

                                // Only calculate if both start and end exist
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
                                // Use exact status from backend (Created / In Progress / Completed)
                                const badgeClass = {
                                    'Created': 'badge-info',
                                    'In Progress': 'badge-warning',
                                    'Completed': 'badge-success'
                                } [data] || 'badge-secondary';

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

                                if (row.status === 'Created') {
                                    buttons +=
                                        `<button class="btn btn-sm btn-success startTrip" data-trip-id="${row.id}" title="Start Trip"><i class="fa fa-play"></i></button> `;
                                }

                                if (row.status === 'In Progress') {
                                    buttons +=
                                        `<button class="btn btn-sm btn-danger endTrip" data-trip-id="${row.id}" title="End Trip"><i class="fa fa-stop"></i></button> `;
                                }

                                if (row.status === 'Created' || row.status === 'In Progress') {
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
                    currentFilter = 'created';
                    tripsTable.ajax.url('/tracker/trips/get/created').load();
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
                // CREATE TRIP MODAL - WITH LOCATIONS
                // ========================================

                // Load locations from sp_LocationList
                function loadTripLocations() {
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
                            $('#tripStartLocation').html(options);
                            $('#tripEndLocation').html(options);
                        } else {
                            $('#tripStartLocation').html('<option value="">No locations available</option>');
                            $('#tripEndLocation').html('<option value="">No locations available</option>');
                        }
                    }).fail(function() {
                        $('#tripStartLocation').html('<option value="">Failed to load locations</option>');
                        $('#tripEndLocation').html('<option value="">Failed to load locations</option>');
                    });
                }

                // === CREATE TRIP - Load Vehicles (FIXED) ===
                $(document).on('click', '#createTripBtn', function() {
                    // Load vehicles
                    $.get('/tracker/vehicles/list', function(res) {
                        if (res.success) {
                            let options = '<option value="">Select Vehicle</option>';
                            res.data.forEach(v => {
                                // Use display_name for UI, but store full vehicle_no in data attribute
                                options +=
                                    `<option value="${v.imei}" data-vehicle="${v.vehicle_no}">${v.display_name}</option>`;
                            });
                            $('#tripVehicle').html(options);
                        }
                    });

                    // Load locations from stored procedure
                    loadTripLocations();

                    // Reset form
                    $('#createTripForm')[0].reset();

                    $('#createTripModal').modal('show');
                });

                // Update vehicle_no when vehicle selected (stores FULL vehicle number)
                $('#tripVehicle').change(function() {
                    $('#tripVehicleNo').val($(this).find(':selected').data('vehicle'));
                });

                // ✅ SIMPLIFIED DEBUG VERSION - Replace your $('#createTripForm').submit() with this:

                $('#createTripForm').submit(function(e) {
                    e.preventDefault();

                    console.log('=== FORM SUBMITTED ===');

                    const formData = {
                        vehicle_no: $('#tripVehicleNo').val(),
                        imei: $('#tripVehicle').val(),
                        start_location_code: $('#tripStartLocation').val(),
                        end_location_code: $('#tripEndLocation').val(),
                        driver_name: $('#driverName').val(),
                        driver_phone: $('#driverPhone').val()
                    };

                    console.log('Form Data:', formData);

                    // Validation
                    if (!formData.imei) {
                        return swal('Warning', 'Please select a vehicle', 'warning');
                    }

                    if (!formData.start_location_code) {
                        return swal('Warning', 'Please select a start location', 'warning');
                    }

                    if (!formData.end_location_code) {
                        return swal('Warning', 'Please select an end location', 'warning');
                    }

                    if (formData.start_location_code === formData.end_location_code) {
                        return swal('Warning', 'Start and end locations cannot be the same', 'warning');
                    }

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
                            console.log('=== SUCCESS HANDLER CALLED ===');
                            console.log('Full Response:', res);
                            console.log('res.success:', res.success);
                            console.log('Type of res.success:', typeof res.success);

                            // Force it to work even if res.success is string "true"
                            if (res.success === true || res.success === 'true' || res.success ===
                                1) {
                                console.log('✅ SUCCESS CONDITION MET');

                                // Close modal
                                console.log('Closing modal...');
                                $('#createTripModal').modal('hide');

                                // Show alert
                                console.log('Showing swal...');
                                alert('Trip created! Trip No: ' + res
                                    .trip_no); // Simple alert first

                                // Reload table
                                console.log('Reloading table...');
                                tripsTable.ajax.reload();

                                // Reset form
                                console.log('Resetting form...');
                                $('#createTripForm')[0].reset();

                            } else {
                                console.log('❌ SUCCESS CONDITION NOT MET');
                                console.log('Response:', res);

                                let errorText = 'Failed to create trip:\n\n';
                                if (Array.isArray(res.errors)) {
                                    res.errors.forEach(err => {
                                        errorText += '• ' + (err.Remark || err) + '\n';
                                    });
                                } else {
                                    errorText += res.message || 'Unknown error occurred';
                                }
                                alert(errorText); // Simple alert
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('=== ERROR HANDLER CALLED ===');
                            console.log('XHR:', xhr);
                            console.log('Status:', status);
                            console.log('Error:', error);
                            console.log('Response:', xhr.responseJSON);

                            alert('AJAX Error: ' + (xhr.responseJSON?.message || error));
                        },
                        complete: function() {
                            console.log('=== COMPLETE HANDLER CALLED ===');
                            $('#submitCreateTrip').prop('disabled', false).html(
                                '<i class="fa fa-check"></i> Create Trip');
                        }
                    });
                });

                // ========================================
                // ADD JOBS MODAL - WITH JOB TYPES
                // ========================================

                // Load locations for internal jobs from sp_LocationList
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
                        } else {
                            $('#jobStartLocation').html('<option value="">No locations available</option>');
                            $('#jobEndLocation').html('<option value="">No locations available</option>');
                        }
                    }).fail(function() {
                        $('#jobStartLocation').html('<option value="">Failed to load locations</option>');
                        $('#jobEndLocation').html('<option value="">Failed to load locations</option>');
                    });
                }

                // Job Type Change Handler
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

                // Load Customer Jobs
                // ✅ UPDATED: Load Customer Jobs - passes vehicle_no to SP
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
                    }).fail((xhr) => {
                        let errorMsg = xhr.responseJSON?.message || 'Failed to load jobs';
                        $('#customerJobSelect').html(`<option value="">${errorMsg}</option>`);
                    });
                }


                // Customer Job Selection Preview
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

                // Open Add Jobs Modal
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

                // ✅ FIXED: Submit Add Job Form with proper modal handling
                $('#addJobsForm').submit(function(e) {
                    e.preventDefault();

                    console.log('=== ADD JOBS FORM SUBMITTED ===');

                    const jobType = $('#addJobType').val();

                    if (!jobType) {
                        return swal('Warning', 'Please select a job type', 'warning');
                    }

                    // Prepare jobs array (SP expects array of jobs)
                    let jobs = [];

                    if (jobType === 'internal') {
                        const startLocationCode = $('#jobStartLocation').val();
                        const endLocationCode = $('#jobEndLocation').val();

                        if (!startLocationCode || !endLocationCode) {
                            return swal('Warning', 'Please select start and end locations', 'warning');
                        }

                        if (startLocationCode === endLocationCode) {
                            return swal('Warning', 'Start and end locations cannot be the same', 'warning');
                        }

                        // Add internal job to jobs array
                        jobs.push({
                            job_type: 'Internal Job', // ← Must match SP: "Internal Job"
                            job_no: '', // Empty for internal jobs (SP will generate)
                            start_location_code: startLocationCode,
                            end_location_code: endLocationCode
                        });

                    } else if (jobType === 'customer_delivery') {
                        const selectedJob = $('#customerJobSelect').val();

                        if (!selectedJob) {
                            return swal('Warning', 'Please select a customer job', 'warning');
                        }

                        const selectedOption = $('#customerJobSelect option:selected');

                        // Add customer delivery job to jobs array
                        jobs.push({
                            job_type: 'Customer Delivery', // ← Must match SP: "Customer Delivery"
                            job_no: selectedOption.text().split(' - ')[
                                0], // Extract ProcessNumber (e.g., "BKG001")
                            start_location_code: selectedOption.data('pickup-code'),
                            end_location_code: selectedOption.data('delivery-code')
                        });
                    }

                    const formData = {
                        trip_id: $('#addJobsTripId').val(),
                        jobs: jobs // ← Array of jobs
                    };

                    console.log('Jobs Data:', formData);

                    $('#submitAddJob').prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> Adding...');

                    $.ajax({
                        url: `/tracker/trips/${formData.trip_id}/jobs/add`,
                        type: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json', // ← Important for array handling
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            console.log('=== ADD JOBS SUCCESS ===');
                            console.log('Response:', res);

                            if (res.success === true || res.success === 'true' || res.success ===
                                1) {
                                console.log('✅ Jobs added successfully');

                                // Close modal
                                $('#addJobsModal').modal('hide');

                                // Show success with simple alert
                                alert(
                                    `Success!\n\n${res.message}\nTrip No: ${res.trip_no}\nJobs Added: ${res.jobs_added}`
                                );

                                // Reset form
                                $('#addJobsForm')[0].reset();
                                $('#internalJobFields').hide();
                                $('#customerDeliveryFields').hide();
                                $('#customerJobDetails').hide();

                            } else {
                                console.log('❌ Jobs addition failed');

                                let errorText = 'Failed to add jobs:\n\n';
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
                        error: function(xhr, status, error) {
                            console.log('=== ADD JOBS ERROR ===');
                            console.log('XHR:', xhr);
                            console.log('Status:', status);
                            console.log('Error:', error);

                            let errorText = 'Failed to add jobs';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.errors) {
                                    errorText += ':\n\n';
                                    const errors = xhr.responseJSON.errors;

                                    if (Array.isArray(errors)) {
                                        errors.forEach(err => {
                                            errorText += '• ' + (err.Remark || err) + '\n';
                                        });
                                    } else {
                                        Object.keys(errors).forEach(key => {
                                            errorText += '• ' + errors[key][0] + '\n';
                                        });
                                    }
                                } else if (xhr.responseJSON.message) {
                                    errorText += ':\n' + xhr.responseJSON.message;
                                }
                            }

                            alert(errorText);
                        },
                        complete: () => {
                            console.log('=== ADD JOBS COMPLETE ===');
                            $('#submitAddJob').prop('disabled', false).html(
                                '<i class="fa fa-check"></i> Add Job to Trip');
                        }
                    });
                });
                // ========================================
                // START TRIP
                // ========================================
                $(document).on('click', '.startTrip', function() {
                    const tripId = $(this).data('trip-id');

                    swal({
                        title: 'Start Trip?',
                        text: 'This will activate GPS tracking and start the first job.',
                        icon: 'warning',
                        buttons: ['Cancel', 'Start Trip']
                    }).then((confirm) => {
                        if (confirm) {
                            $.ajax({
                                url: `/tracker/trips/${tripId}/start`,
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        swal('Started!', res.message, 'success');
                                        tripsTable.ajax.reload(null, false);
                                    }
                                },
                                error: function() {
                                    swal('Error', 'Failed to start trip', 'error');
                                }
                            });
                        }
                    });
                });

                // ========================================
                // END TRIP
                // ========================================
                $(document).on('click', '.endTrip', function() {
                    const tripId = $(this).data('trip-id');

                    swal({
                        title: 'End Trip?',
                        text: 'This will complete the trip and fetch final mileage.',
                        icon: 'warning',
                        buttons: ['Cancel', 'End Trip'],
                        dangerMode: true
                    }).then((confirm) => {
                        if (confirm) {
                            $.ajax({
                                url: `/tracker/trips/${tripId}/end`,
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.success) {
                                        swal('Completed!',
                                            `Distance: ${res.total_distance || '0'} km`,
                                            'success');
                                        tripsTable.ajax.reload(null, false);
                                    }
                                },
                                error: function() {
                                    swal('Error', 'Failed to end trip', 'error');
                                }
                            });
                        }
                    });
                });

                // ========================================
                // VIEW TRIP - WITH START AND STOP BUTTONS
                // ========================================
                $(document).on('click', '.viewTrip', function() {
                    const tripId = $(this).data('trip-id');

                    // Clear any previous live tracking
                    if (window.liveTrackingInterval) {
                        clearInterval(window.liveTrackingInterval);
                        window.liveTrackingInterval = null;
                    }

                    $.get(`/tracker/trips/${tripId}`, function(res) {
                        if (!res.success) {
                            swal('Error', 'Failed to load trip details', 'error');
                            return;
                        }

                        const trip = res.data;

                        // ✅ ADD THIS LINE - Store the GUID in the modal
                        $('#viewTripModal').data('trip-id', tripId);

                        // === BASIC INFO ===
                        $('#viewTripNo').text(trip.trip_no || 'N/A');
                        $('#viewVehicle').text(trip.vehicle_no || 'N/A');
                        $('#viewDriver').text(trip.driver_name || 'Not assigned');

                        // === TIMELINE ===
                        $('#viewStartDate').text(trip.start_date ? moment(trip.start_date).format(
                            'DD/MM/YY HH:mm') : '-');
                        $('#viewEndDate').text(trip.end_date ? moment(trip.end_date).format(
                            'DD/MM/YY HH:mm') : 'Ongoing');

                        // Duration
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

                        // Mileage
                        $('#viewMileage').text(trip.total_distance ? parseFloat(trip.total_distance)
                            .toFixed(2) + ' km' : '0.00 km');

                        // Speed Stats (from DB - final values when completed)
                        $('#viewAvgSpeed').text(trip.avg_speed ? parseFloat(trip.avg_speed).toFixed(1) :
                            'N/A');
                        $('#viewMaxSpeed').text(trip.max_speed ? parseFloat(trip.max_speed).toFixed(0) :
                            'N/A');
                        $('#viewGpsPoints').text(trip.gps_points || 'N/A');

                        // Status Badge
                        const statusMap = {
                            'Created': {
                                text: 'Created',
                                class: 'badge-info'
                            },
                            'In Progress': {
                                text: 'In Progress',
                                class: 'badge-warning'
                            },
                            'Completed': {
                                text: 'Completed',
                                class: 'badge-success'
                            }
                        };
                        const status = trip.status || (trip.start_date ? (trip.end_date ? 'Completed' :
                            'In Progress') : 'Created');
                        const badge = statusMap[status] || {
                            text: status,
                            class: 'badge-secondary'
                        };

                        $('#viewStatus').text(badge.text);
                        $('#viewStatusBadge')
                            .removeClass('badge-info badge-warning badge-success badge-secondary')
                            .addClass(badge.class);

                        // === JOBS TABLE ===
                        const jobs = Array.isArray(trip.jobs) ? trip.jobs : [];
                        $('#jobsCount').text(jobs.length);

                        if (jobs.length === 0) {
                            $('#allocatedJobsContainer').html(
                                '<div class="text-center text-muted py-4"><i class="fa fa-briefcase fa-2x mb-2"></i><p>No jobs allocated</p></div>'
                            );
                        } else {
                            let html = `<div class="table-responsive"><table class="table table-sm table-bordered">
                <thead class="bg-primary text-white"><tr>
                    <th>Job No</th><th>Type</th><th>Route</th><th>Status</th><th class="text-center">Action</th>
                </tr></thead><tbody>`;

                            jobs.forEach(job => {
                                const typeBadge = job.job_type === 'Internal Job' ? 'info' :
                                    'primary';
                                const statusBadge = job.status === 'Completed' ? 'success' :
                                    job.status === 'In Progress' ? 'warning' : 'secondary';

                                let actionBtn = '-';
                                if (job.status === 'Allocated') {
                                    actionBtn = `<button class="btn btn-sm btn-success startJobBtn"
                                   data-job-id="${job.job_id}" data-job-no="${job.job_number}"
                                   data-trip-id="${tripId}">
                                   <i class="fa fa-play"></i> Start
                                 </button>`;
                                } else if (job.status === 'In Progress') {
                                    actionBtn = `<button class="btn btn-sm btn-danger stopJobBtn"
                                   data-job-id="${job.job_id}" data-job-no="${job.job_number}"
                                   data-trip-id="${tripId}">
                                   <i class="fa fa-stop"></i> Stop
                                 </button>`;
                                }

                                html += `<tr>
                    <td><strong>${job.job_number || 'Internal'}</strong></td>
                    <td><span class="badge badge-${typeBadge}">${job.job_type}</span></td>
                    <td><small>${job.pickup_location} → ${job.delivery_location}</small></td>
                    <td><span class="badge badge-${statusBadge}">${job.status}</span></td>
                    <td class="text-center">${actionBtn}</td>
                </tr>`;
                            });
                            html += '</tbody></table></div>';
                            $('#allocatedJobsContainer').html(html);
                        }

                        // === LIVE TRACKING: NOW WORKS WITH CORRECT IMEI (even in Created state!) ===
                        const vehicleNo = trip.vehicle_no || trip.vehicle_imei || 'Unknown';
                        const realImei = getRealImei(vehicleNo);

                        if (realImei) {
                            $('#liveTrackingSection').show();
                            console.log('Starting live tracking for IMEI:', realImei);

                            if (window.liveTrackingInterval) clearInterval(window.liveTrackingInterval);

                            const updateLiveData = () => {
                                $.get(`/jimi-test/location/${realImei}`, function(res) {
                                    console.log('GPS Update:', res);

                                    if (res.success && res.data && res.data.lat && res.data
                                        .lng) {
                                        const l = res.data;

                                        // ONLINE — GREEN & PULSING
                                        $('#liveTrackingSection')
                                            .css({
                                                'background': '#e8f5e9',
                                                'border-left-color': '#4caf50'
                                            });

                                        $('#liveBadge')
                                            .removeClass('badge-danger badge-warning')
                                            .addClass(
                                                'badge-success animate__animated animate__pulse animate__infinite'
                                            );

                                        $('#liveLat').text(parseFloat(l.lat).toFixed(6));
                                        $('#liveLng').text(parseFloat(l.lng).toFixed(6));
                                        $('#liveSpeed').html(`
                <strong style="color:#2e7d32; font-size:2.8rem;">
                    ${l.speed || 0} <small style="font-size:1.2rem;">km/h</small>
                </strong>
            `);
                                        $('#liveLastUpdate').html(`
                <span style="color:#2e7d32; font-weight:bold;">
                    Updated: ${moment().format('DD/MM HH:mm:ss')}
                </span>
            `);
                                        $('#openGoogleMaps')
                                            .attr('href',
                                                `https://www.google.com/maps?q=${l.lat},${l.lng}`
                                            )
                                            .removeClass('btn-danger btn-warning')
                                            .addClass('btn-success')
                                            .html(
                                                '<i class="fa fa-map-marker-alt"></i> Open in Google Maps'
                                            )
                                            .show();

                                    } else {
                                        // OFFLINE — RED
                                        $('#liveTrackingSection')
                                            .css({
                                                'background': '#ffebee',
                                                'border-left-color': '#d32f2f'
                                            });

                                        $('#liveBadge')
                                            .removeClass(
                                                'badge-success animate__pulse animate__infinite'
                                            )
                                            .addClass('badge-danger');

                                        $('#liveSpeed').html(
                                            '<strong style="color:#c62828; font-size:2rem;">Offline</strong>'
                                        );
                                        $('#liveLastUpdate').html(
                                            '<span style="color:#c62828;">No signal</span>'
                                        );
                                        $('#openGoogleMaps').hide();
                                    }
                                }).fail(() => {
                                    // ERROR — ORANGE
                                    $('#liveTrackingSection')
                                        .css({
                                            'background': '#fff3e0',
                                            'border-left-color': '#ff9800'
                                        });

                                    $('#liveBadge')
                                        .removeClass(
                                            'badge-success animate__pulse animate__infinite'
                                        )
                                        .addClass('badge-warning');

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
                            $('#liveSpeed').text('No IMEI');
                            $('#liveLastUpdate').text('Vehicle not mapped');
                            $('#openGoogleMaps').hide();
                        }

                        // === SHOW MODAL ===
                        $('#viewTripModal').modal('show');

                    }).fail(function() {
                        swal('Error', 'Failed to connect to server', 'error');
                    });
                });

                // Stop live tracking when modal is closed
                $('#viewTripModal').on('hidden.bs.modal', function() {
                    if (window.liveTrackingInterval) {
                        clearInterval(window.liveTrackingInterval);
                        window.liveTrackingInterval = null;
                    }
                    $('#liveTrackingSection').hide();
                });

                // ========================================
                // START JOB BUTTON - PROPERLY CHANGES TO STOP
                // ========================================
                $(document).on('click', '.startJobBtn', function(e) {
                    e.preventDefault(); // Prevent any default action

                    const jobId = $(this).data('job-id');
                    const jobNo = $(this).data('job-no');
                    const tripId = $(this).data('trip-id');

                    console.log('Starting job:', {
                        jobId,
                        jobNo,
                        tripId
                    });

                    if (!confirm(`Start job ${jobNo}?\n\nLive GPS tracking will begin immediately.`)) {
                        return;
                    }

                    const $btn = $(this);
                    const $row = $btn.closest('tr');
                    const $actionCell = $btn.parent(); // Get the TD containing the button

                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Starting...');

                    $.ajax({
                        url: `/tracker/trips/${tripId}/jobs/${jobId}/start`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            console.log('✅ Start Job Success:', res);

                            // ✅ SOLUTION: Completely replace the button HTML (not just modify classes)
                            const newStopButton = `
                <button class="btn btn-sm btn-danger stopJobBtn"
                        data-job-id="${jobId}"
                        data-job-no="${jobNo}"
                        data-trip-id="${tripId}">
                    <i class="fa fa-stop"></i> Stop
                </button>
            `;

                            // Replace the entire action cell content
                            $actionCell.html(newStopButton);

                            // ✅ Update status badge in the same row
                            $row.find('.badge')
                                .removeClass('badge-secondary badge-success badge-info')
                                .addClass('badge-warning')
                                .text('In Progress');

                            // ✅ Show success message
                            swal('Started!', `Job ${jobNo} is now active.\nGPS tracking enabled.`,
                                'success');

                            // ✅ Reload trips table (background update)
                            if (typeof tripsTable !== 'undefined') {
                                tripsTable.ajax.reload(null, false);
                            }

                            // ✅ Start live tracking
                            if (typeof forceStartLiveTracking === 'function') {
                                forceStartLiveTracking(tripId);
                            }
                        },
                        error: function(xhr) {
                            console.error('❌ Start Job Error:', xhr);

                            let errorMsg = 'Failed to start job';
                            if (xhr.responseJSON?.message) {
                                errorMsg = xhr.responseJSON.message;
                            }

                            swal('Error', errorMsg, 'error');
                            $btn.prop('disabled', false).html('<i class="fa fa-play"></i> Start');
                        }
                    });
                });

                // NEW FUNCTION: Force live tracking regardless of DB status
                function forceStartLiveTracking(tripId) {
                    $.get(`/tracker/trips/${tripId}`, function(res) {
                        if (!res.success || !res.data.vehicle_imei) return;

                        const imei = res.data.vehicle_imei;

                        // Force show live section
                        $('#liveTrackingSection').show();

                        if (window.liveTrackingInterval) clearInterval(window.liveTrackingInterval);

                        const updateLive = () => {
                            $.get(`/jimi-test/location/${imei}`, function(locRes) {
                                if (locRes.success && locRes.location) {
                                    const l = locRes.location;
                                    $('#liveLat').text((l.lat || 0).toFixed(6));
                                    $('#liveLng').text((l.lng || 0).toFixed(6));
                                    $('#liveSpeed').text((l.speed || 0) + ' km/h');
                                    $('#liveLastUpdate').text(moment().format('DD/MM HH:mm:ss'));
                                    $('#openGoogleMaps').attr('href',
                                            `https://www.google.com/maps?q=${l.lat},${l.lng}`)
                                        .show();
                                }
                            });
                        };

                        updateLive();
                        window.liveTrackingInterval = setInterval(updateLive, 15000);
                    });
                }

                // ========================================
                // STOP JOB BUTTON
                // ========================================
                $(document).on('click', '.stopJobBtn', function() {
                    const jobId = $(this).data('job-id');
                    const jobNo = $(this).data('job-no');
                    const tripId = $(this).data('trip-id');

                    if (!confirm(`Complete job ${jobNo}?\n\nGPS mileage will be fetched and saved.`)) return;

                    const $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Completing...');

                    $.ajax({
                        url: `/tracker/trips/${tripId}/jobs/${jobId}/complete`, // or /end
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                alert(`Job completed!\nDistance: ${res.total_distance || '0'} km`);
                                $('#viewTripModal').modal('hide');
                                setTimeout(() => {
                                    $(`.viewTrip[data-trip-id="${tripId}"]`).trigger(
                                        'click');
                                }, 400);
                                if (typeof tripsTable !== 'undefined') {
                                    tripsTable.ajax.reload(null, false);
                                }
                            } else {
                                alert('Failed: ' + (res.message || 'Unknown error'));
                                $btn.prop('disabled', false).html(
                                    '<i class="fa fa-stop"></i> Stop');
                            }
                        },
                        error: function() {
                            alert('Server error');
                            $btn.prop('disabled', false).html('<i class="fa fa-stop"></i> Stop');
                        }
                    });
                });

                // ========================================
                // MODAL CLEANUP
                // ========================================
                $('.modal').on('hidden.bs.modal', function() {
                    $(this).find('form')[0]?.reset();
                });
            });
            // ========================================
            // MANUAL JOB ENTRY
            // ========================================

            // Load locations for manual job
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

            // Load customer jobs
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

            // Open manual job modal
            $(document).on('click', '#addManualJobBtn', function() {
                const tripId = $('#viewTripModal').data('trip-id');

                // ✅ DEBUG
                console.log('=== MANUAL JOB MODAL OPENING ===');
                console.log('Trip ID from modal:', tripId);
                console.log('Type:', typeof tripId);
                console.log('Is GUID?', /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(tripId));

                if (!tripId || !/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(tripId)) {
                    alert('ERROR: Trip ID is not valid! Got: ' + tripId);
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

                // Set default dates (yesterday to now)
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

            // Job type change
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

            // Customer job preview
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

            // Submit manual job
            $('#manualJobForm').submit(function(e) {
                e.preventDefault();

                console.log('=== MANUAL JOB SUBMISSION ===');

                const jobType = $('#manualJobType').val();
                const startDate = $('#manualStartDate').val();
                const endDate = $('#manualEndDate').val();
                const tripId = $('#manualJobTripId').val();

                console.log('Job Type:', jobType);
                console.log('Start Date:', startDate);
                console.log('End Date:', endDate);
                console.log('Trip ID:', tripId);

                if (!jobType || !startDate || !endDate) {
                    return swal('Warning', 'Please fill all required fields', 'warning');
                }

                if (new Date(endDate) <= new Date(startDate)) {
                    return swal('Warning', 'End date must be after start date', 'warning');
                }

                let jobs = [];

                if (jobType === 'internal') {
                    const startLoc = $('#manualStartLocation').val();
                    const endLoc = $('#manualEndLocation').val();

                    if (!startLoc || !endLoc) {
                        return swal('Warning', 'Please select locations', 'warning');
                    }

                    jobs.push({
                        job_type: 'Internal Job',
                        job_no: '',
                        start_location_code: startLoc,
                        end_location_code: endLoc
                    });

                } else if (jobType === 'customer_delivery') {
                    const selectedJob = $('#manualCustomerJob').val();
                    if (!selectedJob) {
                        return swal('Warning', 'Please select a customer job', 'warning');
                    }

                    const selectedOption = $('#manualCustomerJob option:selected');
                    jobs.push({
                        job_type: 'Customer Delivery',
                        job_no: selectedOption.text().split(' - ')[0],
                        start_location_code: selectedOption.data('pickup-code'),
                        end_location_code: selectedOption.data('delivery-code')
                    });
                }

                $('#submitManualJob').prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> Processing...');

                // ✅ STEP 1: Add the job to the trip
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
                        console.log('✅ Job added:', addResponse);

                        if (!addResponse.success) {
                            $('#submitManualJob').prop('disabled', false).html(
                                '<i class="fa fa-save"></i> Save Historical Job');
                            return swal('Failed', addResponse.message, 'error');
                        }

                        // ✅ STEP 2: Get the newly created job ID
                        $.get(`/tracker/trips/${tripId}`, function(tripDetails) {
                            if (!tripDetails.success) {
                                $('#submitManualJob').prop('disabled', false).html(
                                    '<i class="fa fa-save"></i> Save Historical Job');
                                return swal('Failed', 'Could not retrieve job details', 'error');
                            }

                            // Find the newest job (no dates set)
                            const newJob = tripDetails.data.jobs
                                .filter(j => !j.start_date && !j.end_date)
                                .sort((a, b) => b.job_id - a.job_id)[0];

                            if (!newJob) {
                                $('#submitManualJob').prop('disabled', false).html(
                                    '<i class="fa fa-save"></i> Save Historical Job');
                                return swal('Failed', 'Could not find newly created job', 'error');
                            }

                            console.log('✅ New job found:', newJob);

                            // ✅ STEP 3: Complete job directly with BOTH custom dates
                            $.ajax({
                                url: `/tracker/trips/${tripId}/jobs/${newJob.job_id}/complete`,
                                type: 'POST',
                                data: JSON.stringify({
                                    is_historical: true,
                                    custom_start_date: startDate, // ← Both dates sent
                                    custom_end_date: endDate
                                }),
                                contentType: 'application/json',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(res) {
                                    console.log('✅ Job completed:', res);

                                    if (res.success) {
                                        $('#manualJobModal').modal('hide');

                                        swal({
                                            title: 'Success!',
                                            text: `Historical job added!\n\nJob: ${res.job_no}\nDistance: ${res.total_distance} km\nDuration: ${res.duration_hours} hours\nAvg Speed: ${res.avg_speed} km/h`,
                                            icon: 'success'
                                        });

                                        // Refresh trip details
                                        setTimeout(() => {
                                            $(`.viewTrip[data-trip-id="${tripId}"]`)
                                                .trigger('click');
                                        }, 500);

                                    } else {
                                        swal('Failed', res.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    console.error('❌ Complete job error:', xhr);
                                    swal('Error', xhr.responseJSON?.message ||
                                        'Failed to complete job', 'error');
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
                        console.error('❌ Add job error:', xhr);
                        swal('Error', xhr.responseJSON?.message || 'Failed to add job', 'error');
                        $('#submitManualJob').prop('disabled', false).html(
                            '<i class="fa fa-save"></i> Save Historical Job');
                    }
                });
            });

            // Store trip ID when view modal opens
            $('#viewTripModal').on('shown.bs.modal', function() {
                // Trip ID was already set when opening the modal - don't overwrite it!
                // Or if you need to set it, get it from the button that opened the modal
            });
        </script>
    @endsection

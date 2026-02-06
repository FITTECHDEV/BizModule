<!-- Failed Verification Card -->
<div class="col-6 col-md-6 col-lg-4 mb-2">
    <div class="small-box bg-danger card-filter-trigger compact-card" data-status="failed_verification">
        <div class="inner">
            <h3>{{ $dashboardData['cards']['failed_verification'] ?? 0 }}</h3>
            <p>Failed Verification</p>
        </div>
        <div class="icon">
            <i class="fas fa-times-circle"></i>
        </div>
    </div>
</div>

<!-- Verified Card -->
<div class="col-6 col-md-6 col-lg-4 mb-2">
    <div class="small-box bg-success card-filter-trigger compact-card" data-status="verified">
        <div class="inner">
            <h3>{{ $dashboardData['cards']['verified'] ?? 0 }}</h3>
            <p>Verified</p>
        </div>
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>

<!-- Failed RA Integration Card -->
<div class="col-6 col-md-6 col-lg-4 mb-2">
    <div class="small-box bg-warning card-filter-trigger compact-card" data-status="failed_ra_integration">
        <div class="inner">
            <h3>{{ $dashboardData['cards']['failed_ra_integration'] ?? 0 }}</h3>
            <p>Failed RA Integration</p>
        </div>
        <div class="icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
    </div>
</div>

<!-- Pending D365 Integration Card -->
<div class="col-6 col-md-6 col-lg-4 mb-2">
    <div class="small-box bg-info card-filter-trigger compact-card" data-status="pending_d365_integration">
        <div class="inner">
            <h3>{{ $dashboardData['cards']['pending_d365_integration'] ?? 0 }}</h3>
            <p>Pending D365 Integration</p>
        </div>
        <div class="icon">
            <i class="fas fa-clock"></i>
        </div>
    </div>
</div>

<!-- Failed D365 Integration Card -->
<div class="col-6 col-md-6 col-lg-4 mb-2">
    <div class="small-box bg-danger card-filter-trigger compact-card" data-status="failed_d365_integration">
        <div class="inner">
            <h3>{{ $dashboardData['cards']['failed_d365_integration'] ?? 0 }}</h3>
            <p>Failed D365 Integration</p>
        </div>
        <div class="icon">
            <i class="fas fa-times"></i>
        </div>
    </div>
</div>

<!-- Success Card -->
<div class="col-6 col-md-6 col-lg-4 mb-2">
    <div class="small-box bg-primary card-filter-trigger compact-card" data-status="success">
        <div class="inner">
            <h3>{{ $dashboardData['cards']['success'] ?? 0 }}</h3>
            <p>Success</p>
        </div>
        <div class="icon">
            <i class="fas fa-check-double"></i>
        </div>
    </div>
</div>
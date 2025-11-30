<!DOCTYPE html>
<html>
<head>
    <title>Jimi API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .test-section { margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .result { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; }
        .badge { font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Jimi IoT API Test Dashboard</h1>
        <p class="text-muted">Test your Jimi API integration before using in production</p>
        <hr>

        <!-- Test 1: Connection Test -->
        <div class="test-section">
            <h3>1️⃣ Test Connection</h3>
            <p>Check if API credentials are valid and connection works</p>
            <button class="btn btn-primary" onclick="testConnection()">Test Connection</button>
            <div id="connectionResult" class="result" style="display: none;"></div>
        </div>

        <!-- Test 2: Get All Vehicles -->
        <div class="test-section">
            <h3>2️⃣ Get All Vehicles</h3>
            <p>Retrieve list of all vehicles with their online status</p>
            <button class="btn btn-success" onclick="getVehicles()">Get Vehicles</button>
            <div id="vehiclesResult" class="result" style="display: none;"></div>
        </div>

        <!-- Test 3: Get Current Location -->
        <div class="test-section">
            <h3>3️⃣ Get Current Location</h3>
            <p>Get real-time location of a specific vehicle</p>
            <div class="form-group">
                <label>Select Vehicle IMEI:</label>
                <select id="locationImei" class="form-control" style="max-width: 400px;">
                    <option value="865784055073301">KDN 345Y - 865784055073301</option>
                    <option value="865784055073302">KDN 352Y - 865784055073302</option>
                    <option value="865784055073303">KDN 351Y - 865784055073303</option>
                    <option value="865784055073304">KDN 349Y - 865784055073304</option>
                    <option value="865784055073305">KDN 124Z - 865784055073305</option>
                </select>
            </div>
            <button class="btn btn-info" onclick="getCurrentLocation()">Get Location</button>
            <div id="locationResult" class="result" style="display: none;"></div>
        </div>

        <!-- Test 4: Get Mileage -->
        <div class="test-section">
            <h3>4️⃣ Get Mileage Data</h3>
            <p>Calculate distance traveled between two timestamps</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>IMEI:</label>
                        <select id="mileageImei" class="form-control">
                            <option value="865784055073301">KDN 345Y</option>
                            <option value="865784055073302">KDN 352Y</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Start Time:</label>
                        <input type="datetime-local" id="mileageStart" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>End Time:</label>
                        <input type="datetime-local" id="mileageEnd" class="form-control">
                    </div>
                </div>
            </div>
            <button class="btn btn-warning" onclick="getMileage()">Get Mileage</button>
            <button class="btn btn-secondary btn-sm" onclick="fillTestDates()">Use Test Dates</button>
            <div id="mileageResult" class="result" style="display: none;"></div>
        </div>

        <!-- Test 5: Get Track Data -->
        <div class="test-section">
            <h3>5️⃣ Get GPS Track Data</h3>
            <p>Get GPS breadcrumbs (route history) for a time period</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>IMEI:</label>
                        <select id="trackImei" class="form-control">
                            <option value="865784055073301">KDN 345Y</option>
                            <option value="865784055073302">KDN 352Y</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Start Time:</label>
                        <input type="datetime-local" id="trackStart" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>End Time:</label>
                        <input type="datetime-local" id="trackEnd" class="form-control">
                    </div>
                </div>
            </div>
            <button class="btn btn-danger" onclick="getTrackData()">Get Track Data</button>
            <div id="trackResult" class="result" style="display: none;"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function testConnection() {
            $('#connectionResult').html('⏳ Testing...').show();

            $.get('/jimi-test/connection', function(res) {
                if (res.success) {
                    $('#connectionResult').html(`
                        <span class="badge badge-success">✅ SUCCESS</span>
                        <p class="mt-2"><strong>Message:</strong> ${res.message}</p>
                        <p><strong>Token:</strong> ${res.token}</p>
                        <p><strong>Vehicles:</strong> ${res.vehicles_count}</p>
                    `);
                } else {
                    $('#connectionResult').html(`
                        <span class="badge badge-danger">❌ FAILED</span>
                        <p class="mt-2">${res.message}</p>
                    `);
                }
            }).fail(function() {
                $('#connectionResult').html('<span class="badge badge-danger">❌ ERROR</span><p>Connection failed</p>');
            });
        }

        function getVehicles() {
            $('#vehiclesResult').html('⏳ Loading...').show();

            $.get('/jimi-test/vehicles', function(res) {
                let html = `<span class="badge badge-success">✅ ${res.count} Vehicles Found</span><pre>${JSON.stringify(res.data, null, 2)}</pre>`;
                $('#vehiclesResult').html(html);
            }).fail(function() {
                $('#vehiclesResult').html('<span class="badge badge-danger">❌ ERROR</span>');
            });
        }

        function getCurrentLocation() {
            const imei = $('#locationImei').val();
            $('#locationResult').html('⏳ Loading...').show();

            $.get('/jimi-test/location/' + imei, function(res) {
                if (res.success) {
                    let html = `<span class="badge badge-success">✅ Location Found</span><pre>${JSON.stringify(res.data, null, 2)}</pre>`;
                    $('#locationResult').html(html);
                } else {
                    $('#locationResult').html(`<span class="badge badge-warning">⚠️ ${res.message}</span>`);
                }
            }).fail(function() {
                $('#locationResult').html('<span class="badge badge-danger">❌ ERROR</span>');
            });
        }

        function getMileage() {
            const imei = $('#mileageImei').val();
            const startTime = $('#mileageStart').val().replace('T', ' ') + ':00';
            const endTime = $('#mileageEnd').val().replace('T', ' ') + ':00';

            $('#mileageResult').html('⏳ Calculating...').show();

            $.post('/jimi-test/mileage', {
                imei: imei,
                start_time: startTime,
                end_time: endTime
            }, function(res) {
                if (res.success) {
                    let html = `
                        <span class="badge badge-success">✅ Mileage Calculated</span>
                        <p class="mt-2"><strong>Distance:</strong> ${res.mileage_km} km</p>
                        <p><strong>Duration:</strong> ${res.duration_minutes} minutes</p>
                        <pre>${JSON.stringify(res, null, 2)}</pre>
                    `;
                    $('#mileageResult').html(html);
                } else {
                    $('#mileageResult').html(`<span class="badge badge-danger">❌ ${res.message}</span>`);
                }
            }).fail(function() {
                $('#mileageResult').html('<span class="badge badge-danger">❌ ERROR</span>');
            });
        }

        function getTrackData() {
            const imei = $('#trackImei').val();
            const startTime = $('#trackStart').val().replace('T', ' ') + ':00';
            const endTime = $('#trackEnd').val().replace('T', ' ') + ':00';

            $('#trackResult').html('⏳ Loading...').show();

            $.post('/jimi-test/track', {
                imei: imei,
                start_time: startTime,
                end_time: endTime
            }, function(res) {
                let html = `<span class="badge badge-success">✅ ${res.count} GPS Points</span><pre>${JSON.stringify(res.data.slice(0, 10), null, 2)}</pre>`;
                if (res.count > 10) {
                    html += `<p class="text-muted">Showing first 10 of ${res.count} points</p>`;
                }
                $('#trackResult').html(html);
            }).fail(function() {
                $('#trackResult').html('<span class="badge badge-danger">❌ ERROR</span>');
            });
        }

        function fillTestDates() {
            const now = new Date();
            const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

            $('#mileageStart, #trackStart').val(formatDateTime(yesterday));
            $('#mileageEnd, #trackEnd').val(formatDateTime(now));
        }

        function formatDateTime(date) {
            return date.getFullYear() + '-' +
                   String(date.getMonth() + 1).padStart(2, '0') + '-' +
                   String(date.getDate()).padStart(2, '0') + 'T' +
                   String(date.getHours()).padStart(2, '0') + ':' +
                   String(date.getMinutes()).padStart(2, '0');
        }
    </script>
</body>
</html>

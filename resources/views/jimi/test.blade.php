<!DOCTYPE html>
<html>

<head>
    <title>Jimi IoT - Enhanced Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-bg: #1a1d23;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .header-card h1 {
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-card h1 i {
            color: var(--primary-color);
        }

        .test-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .test-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .test-section h3 {
            color: #2c3e50;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Vehicle Data Cards */
        .vehicle-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .data-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .data-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .data-card .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .data-card .label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .data-card .value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 5px;
        }

        .data-card .subvalue {
            font-size: 0.9rem;
            color: #95a5a6;
            margin-top: 5px;
        }

        /* Map Container */
        #map {
            height: 500px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-top: 20px;
        }

        /* Speed Gauge */
        .gauge-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 20px auto;
        }

        .gauge-canvas {
            width: 100%;
            height: 100%;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .status-online {
            background: var(--success-color);
            color: white;
        }

        .status-offline {
            background: var(--danger-color);
            color: white;
        }

        /* Signal Strength */
        .signal-bars {
            display: flex;
            gap: 3px;
            align-items: flex-end;
            height: 30px;
        }

        .signal-bar {
            width: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            transition: background 0.3s;
        }

        .signal-bar.active {
            background: var(--success-color);
        }

        .signal-bar:nth-child(1) {
            height: 20%;
        }

        .signal-bar:nth-child(2) {
            height: 40%;
        }

        .signal-bar:nth-child(3) {
            height: 60%;
        }

        .signal-bar:nth-child(4) {
            height: 80%;
        }

        .signal-bar:nth-child(5) {
            height: 100%;
        }

        /* Loading Spinner */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            z-index: 1000;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Result Card */
        .result-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .result-card.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Battery Indicator */
        .battery-indicator {
            width: 60px;
            height: 30px;
            border: 2px solid #2c3e50;
            border-radius: 4px;
            position: relative;
            display: inline-block;
        }

        .battery-indicator::after {
            content: '';
            position: absolute;
            right: -6px;
            top: 8px;
            width: 4px;
            height: 12px;
            background: #2c3e50;
            border-radius: 0 2px 2px 0;
        }

        .battery-level {
            height: 100%;
            background: linear-gradient(to right, #28a745, #5cb85c);
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .battery-level.low {
            background: linear-gradient(to right, #dc3545, #e74c3c);
        }

        .battery-level.medium {
            background: linear-gradient(to right, #ffc107, #ffdb4d);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .data-grid {
                grid-template-columns: 1fr;
            }

            #map {
                height: 350px;
            }
        }

        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 10px;
            max-height: 400px;
            overflow-y: auto;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <div class="container dashboard-container">
        <!-- Header -->
        <div class="header-card">
            <h1>
                <i class="fas fa-satellite-dish"></i>
                Jimi IoT Vehicle Tracking Dashboard
            </h1>
            <p class="text-muted mb-0">Real-time vehicle monitoring and GPS tracking system</p>
        </div>

        <!-- Test 1: Connection Test -->
        <div class="test-section">
            <h3><i class="fas fa-plug"></i> Connection Test</h3>
            <p class="text-muted">Verify API credentials and system connectivity</p>
            <button class="btn btn-primary btn-custom" onclick="testConnection()">
                <i class="fas fa-check-circle"></i> Test Connection
            </button>
            <div id="connectionResult" class="result-card"></div>
        </div>

        <!-- Test 2: Get All Vehicles -->
        <div class="test-section">
            <h3><i class="fas fa-cars"></i> Fleet Overview</h3>
            <p class="text-muted">View all vehicles with their current status</p>
            <button class="btn btn-success btn-custom" onclick="getVehicles()">
                <i class="fas fa-list"></i> Get All Vehicles
            </button>
            <div id="vehiclesResult" class="result-card"></div>
        </div>

        <!-- Test 3: Get Current Location -->
        <div class="test-section" style="position: relative;">
            <h3><i class="fas fa-map-marker-alt"></i> Vehicle Location Tracker</h3>
            <p class="text-muted">Real-time GPS location and vehicle telemetry</p>

            <div class="form-group">
                <label><i class="fas fa-car"></i> Select Vehicle:</label>
                <select id="locationImei" class="form-control" style="max-width: 400px;">
                    <option value="865784055081171">KDN 124Z - 865784055081171</option>
                    <option value="865784055077419">KDN 209Y - 865784055077419</option>
                    <option value="865784055074523">KDN 331Y - 865784055074523</option>
                    <option value="865784055073301" selected>KDN 345Y - 865784055073301</option>
                    <option value="865784055081189">KDN 346Y - 865784055081189</option>
                    <option value="865784055076759">KDN 347Y - 865784055076759</option>
                    <option value="865784055073996">KDN 348Y - 865784055073996</option>
                    <option value="865784055074077">KDN 349Y - 865784055074077</option>
                    <option value="865784055073303">KDN 351Y - 865784055073303</option>
                    <option value="865784055082542">KDN 352Y - 865784055082542</option>
                </select>
            </div>

            <button class="btn btn-info btn-custom" onclick="getCurrentLocation()">
                <i class="fas fa-location-arrow"></i> Track Vehicle
            </button>

            <div id="locationResult" class="result-card"></div>
        </div>

        <!-- Test 4: Get Mileage -->
        <div class="test-section">
            <h3><i class="fas fa-route"></i> Mileage Calculator</h3>
            <p class="text-muted">Calculate distance traveled over time</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>IMEI:</label>
                        <select id="mileageImei" class="form-control">
                            <option value="865784055073301">KDN 345Y</option>
                            <option value="865784055082542">KDN 352Y</option>
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
            <button class="btn btn-warning btn-custom" onclick="getMileage()">
                <i class="fas fa-calculator"></i> Calculate Mileage
            </button>
            <button class="btn btn-secondary btn-sm" onclick="fillTestDates()">
                <i class="fas fa-clock"></i> Use Test Dates
            </button>
            <div id="mileageResult" class="result-card"></div>
        </div>

        <!-- Test 5: Get Track Data -->
        <div class="test-section">
            <h3><i class="fas fa-map"></i> GPS Track History</h3>
            <p class="text-muted">View complete route history with GPS breadcrumbs</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>IMEI:</label>
                        <select id="trackImei" class="form-control">
                            <option value="865784055073301">KDN 345Y</option>
                            <option value="865784055082542">KDN 352Y</option>
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
            <button class="btn btn-danger btn-custom" onclick="getTrackData()">
                <i class="fas fa-route"></i> Get Track Data
            </button>
            <div id="trackResult" class="result-card"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let map = null;
        let marker = null;

        function testConnection() {
            $('#connectionResult').html('<div class="loading-overlay"><div class="spinner"></div></div>').addClass('show');

            $.get('/jimi-test/connection', function (res) {
                if (res.success) {
                    $('#connectionResult').html(`
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">Connection Successful!</h4>
                            <div class="data-grid mt-4">
                                <div class="data-card">
                                    <div class="icon text-primary"><i class="fas fa-key"></i></div>
                                    <div class="label">Token Status</div>
                                    <div class="value">Valid</div>
                                </div>
                                <div class="data-card">
                                    <div class="icon text-success"><i class="fas fa-cars"></i></div>
                                    <div class="label">Total Vehicles</div>
                                    <div class="value">${res.vehicles_count}</div>
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    $('#connectionResult').html(`
                        <div class="text-center">
                            <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">Connection Failed</h4>
                            <p class="text-muted">${res.message}</p>
                        </div>
                    `);
                }
            }).fail(function () {
                $('#connectionResult').html(`
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Error</h4>
                        <p class="text-muted">Failed to connect to API</p>
                    </div>
                `);
            });
        }

        function getVehicles() {
            $('#vehiclesResult').html('<div class="loading-overlay"><div class="spinner"></div></div>').addClass('show');

            $.get('/jimi-test/vehicles', function (res) {
                console.log('Vehicles Response:', res); // Debug log

                let vehicles = [];

                // Handle different response formats
                if (Array.isArray(res)) {
                    vehicles = res;
                } else if (res.data && Array.isArray(res.data)) {
                    vehicles = res.data;
                } else if (res.result && Array.isArray(res.result)) {
                    vehicles = res.result;
                }

                let html = `
                    <h5 class="mb-4"><i class="fas fa-cars"></i> ${vehicles.length} Vehicles in Fleet</h5>
                    <div class="row">
                `;

                vehicles.forEach(vehicle => {
                    const gpsTime = new Date(vehicle.gps_time || vehicle.gpsTime);
                    const now = new Date();
                    const minutesSinceGPS = (now - gpsTime) / (1000 * 60);
                    // Trust backend's is_online status
                    const isOnline = vehicle.is_online === true || vehicle.is_online === 'true' || vehicle.status === '1';

                    const speed = vehicle.speed || '0';
                    const mileage = vehicle.current_mileage || vehicle.currentMileage || '0';
                    const deviceName = vehicle.device_name || vehicle.deviceName || 'Unknown';
                    const imei = vehicle.imei;

                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="vehicle-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1"><i class="fas fa-car"></i> ${deviceName}</h5>
                                        <small>IMEI: ${imei}</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="status-badge ${isOnline ? 'status-online' : 'status-offline'}">
                                            ${isOnline ? 'Online' : 'Offline'}
                                        </span>
                                        ${!isOnline && minutesSinceGPS > 30 ? `<br><small style="font-size: 0.7rem;"><i class="fas fa-clock"></i> ${Math.floor(minutesSinceGPS)}m ago</small>` : ''}
                                    </div>
                                </div>
                                <div class="row text-white">
                                    <div class="col-4">
                                        <small>Speed:</small>
                                        <div><strong>${speed} km/h</strong></div>
                                    </div>
                                    <div class="col-4">
                                        <small>ACC:</small>
                                        <div><strong>${(vehicle.acc_status || vehicle.accStatus) === '1' ? 'ON' : 'OFF'}</strong></div>
                                    </div>
                                    <div class="col-4">
                                        <small>Direction:</small>
                                        <div><strong>${vehicle.direction || vehicle.direction || '0'}°</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div><pre class="mt-3">${JSON.stringify(vehicles, null, 2)}</pre>`;
                $('#vehiclesResult').html(html);
            }).fail(function () {
                $('#vehiclesResult').html(`
                    <div class="text-center">
                        <i class="fas fa-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                        <p class="mt-2">Failed to load vehicles</p>
                    </div>
                `);
            });
        }

        function getCurrentLocation() {
            const imei = $('#locationImei').val();
            $('#locationResult').html('<div class="loading-overlay"><div class="spinner"></div></div>').addClass('show');

            $.get('/jimi-test/location/' + imei, function (res) {
                console.log('Location Response:', res); // Debug log

                let vehicle = null;
                let rawData = null;

                // Handle different response formats
                if (res.success && res.data) {
                    // Format: {success: true, data: {...}} - NESTED OBJECT
                    rawData = res.data;
                    vehicle = {
                        imei: res.data.imei,
                        deviceName: res.data.device_name || res.data.deviceName || $('#locationImei option:selected').text().split(' - ')[0],
                        mcType: 'VG03',
                        icon: 'other',
                        status: (res.data.is_online === true || res.data.is_online === 'true') ? '1' : '0',
                        posType: 'GPS',
                        lat: parseFloat(res.data.lat),
                        lng: parseFloat(res.data.lng),
                        hbTime: res.data.hb_time || res.data.gps_time || new Date().toISOString().slice(0, 19).replace('T', ' '),
                        accStatus: res.data.acc_status || '0',
                        gpsSignal: res.data.gps_signal || '4',
                        powerValue: res.data.power_value || null,
                        batteryPowerVal: res.data.battery_power_val || '0',
                        speed: res.data.speed || '0',
                        gpsNum: res.data.gps_num || '0',
                        gpsTime: res.data.gps_time,
                        direction: res.data.direction || '0',
                        activationFlag: '1',
                        expireFlag: '1',
                        electQuantity: '',
                        locDesc: res.data.address || null,
                        distance: '0',
                        temperature: null,
                        trackerOil: null,
                        trackerOils: null,
                        currentMileage: res.data.current_mileage || '0',
                        account: 'freightintime',
                        customerName: 'FREIGHT IN TIME',
                        state: null,
                        stateTime: null,
                        iccid: res.data.iccid || '',
                        mac: null,
                        chargeStatus: null,
                        shutdown: null,
                        confidence: null
                    };
                } else if (res.data && Array.isArray(res.data) && res.data.length > 0) {
                    // Format: {success: true, data: [{...}]} - ARRAY
                    rawData = res.data[0];
                    vehicle = res.data[0];
                } else if (res.imei && res.lat && res.lng) {
                    // Format: Flat object {imei, lat, lng, ...}
                    rawData = res;
                    vehicle = {
                        imei: res.imei,
                        deviceName: $('#locationImei option:selected').text().split(' - ')[0],
                        mcType: 'VG03',
                        icon: 'other',
                        status: res.is_online ? '1' : '0',
                        posType: 'GPS',
                        lat: parseFloat(res.lat),
                        lng: parseFloat(res.lng),
                        hbTime: res.hb_time || res.gps_time || new Date().toISOString().slice(0, 19).replace('T', ' '),
                        accStatus: res.acc_status || '0',
                        gpsSignal: res.gps_signal || '4',
                        powerValue: res.power_value || null,
                        batteryPowerVal: res.battery_power_val || '0',
                        speed: res.speed || '0',
                        gpsNum: res.gps_num || '0',
                        gpsTime: res.gps_time,
                        direction: res.direction || '0',
                        activationFlag: '1',
                        expireFlag: '1',
                        electQuantity: '',
                        locDesc: res.address || null,
                        distance: '0',
                        temperature: null,
                        trackerOil: null,
                        trackerOils: null,
                        currentMileage: res.current_mileage || '0',
                        account: 'freightintime',
                        customerName: 'FREIGHT IN TIME',
                        state: null,
                        stateTime: null,
                        iccid: res.iccid || '',
                        mac: null,
                        chargeStatus: null,
                        shutdown: null,
                        confidence: null
                    };
                } else if (res.success === false) {
                    $('#locationResult').html(`
                        <div class="text-center">
                            <i class="fas fa-map-marker-alt text-warning" style="font-size: 2rem;"></i>
                            <p class="mt-2">${res.message || 'No location data available'}</p>
                        </div>
                    `);
                    return;
                }

                if (vehicle) {
                    displayVehicleLocation(vehicle, rawData);
                } else {
                    $('#locationResult').html(`
                        <div class="text-center">
                            <i class="fas fa-map-marker-alt text-warning" style="font-size: 2rem;"></i>
                            <p class="mt-2">Unable to parse location data</p>
                            <pre class="mt-3 text-left">${JSON.stringify(res, null, 2)}</pre>
                        </div>
                    `);
                }
            }).fail(function () {
                $('#locationResult').html(`
                    <div class="text-center">
                        <i class="fas fa-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                        <p class="mt-2">Failed to get location</p>
                    </div>
                `);
            });
        }

        function displayVehicleLocation(vehicle, rawData) {
            // Check if vehicle is truly online based on GPS time recency
            const gpsTime = new Date(vehicle.gpsTime || vehicle.gps_time);
            const now = new Date();
            const minutesSinceGPS = (now - gpsTime) / (1000 * 60);
            // Trust backend's is_online status
            const isOnline = vehicle.status === '1' || vehicle.is_online === true || vehicle.is_online === 'true';

            const speed = parseInt(vehicle.speed) || 0;
            const gpsSignal = parseInt(vehicle.gpsSignal || vehicle.gps_signal) || 0;
            const batteryLevel = parseFloat(vehicle.batteryPowerVal || vehicle.battery_power_val) || 0;

            let html = `
                <!-- Vehicle Header -->
                <div class="vehicle-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-car"></i> ${vehicle.deviceName}</h4>
                            <small>${vehicle.mcType} - ${vehicle.imei}</small>
                        </div>
                        <div class="text-right">
                            <span class="status-badge ${isOnline ? 'status-online' : 'status-offline'}">
                                <i class="fas fa-circle"></i> ${isOnline ? 'Online' : 'Offline'}
                            </span>
                            ${minutesSinceGPS > 30 && isOnline ? `<br><small class="text-warning"><i class="fas fa-clock"></i> GPS ${Math.floor(minutesSinceGPS)}m old</small>` : ''}
                        </div>
                    </div>
                    ${minutesSinceGPS > 60 && isOnline ? `
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-satellite-dish"></i> <strong>Stale GPS Data</strong>
                            <p class="mb-0">Device is online but last GPS update was ${formatTimeDifference(minutesSinceGPS)} ago. Vehicle may be in poor signal area or stationary.</p>
                        </div>
                    ` : ''}
                </div>

                <!-- Data Cards Grid -->
                <div class="data-grid">
                    <!-- Speed -->
                    <div class="data-card">
                        <div class="icon text-primary"><i class="fas fa-tachometer-alt"></i></div>
                        <div class="label">Current Speed</div>
                        <div class="value">${speed}</div>
                        <div class="subvalue">km/h</div>
                    </div>

                    <!-- GPS Coordinates -->
                    <div class="data-card">
                        <div class="icon text-success"><i class="fas fa-map-pin"></i></div>
                        <div class="label">Latitude</div>
                        <div class="value">${vehicle.lat.toFixed(6)}</div>
                    </div>

                    <div class="data-card">
                        <div class="icon text-info"><i class="fas fa-map-pin"></i></div>
                        <div class="label">Longitude</div>
                        <div class="value">${vehicle.lng.toFixed(6)}</div>
                    </div>

                    <!-- GPS Signal -->
                    <div class="data-card">
                        <div class="icon text-warning"><i class="fas fa-satellite"></i></div>
                        <div class="label">GPS Signal</div>
                        <div class="value">${gpsSignal}/5</div>
                        <div class="signal-bars mt-2 justify-content-center">
                            ${Array.from({length: 5}, (_, i) =>
                                `<div class="signal-bar ${i < gpsSignal ? 'active' : ''}"></div>`
                            ).join('')}
                        </div>
                    </div>

                    <!-- GPS Satellites -->
                    <div class="data-card">
                        <div class="icon text-secondary"><i class="fas fa-satellite-dish"></i></div>
                        <div class="label">GPS Satellites</div>
                        <div class="value">${vehicle.gpsNum}</div>
                        <div class="subvalue">Connected</div>
                    </div>

                    <!-- Direction -->
                    <div class="data-card">
                        <div class="icon text-danger"><i class="fas fa-compass"></i></div>
                        <div class="label">Direction</div>
                        <div class="value">${vehicle.direction}°</div>
                        <div class="subvalue">${getDirection(vehicle.direction)}</div>
                    </div>

                    <!-- Mileage -->
                    <div class="data-card">
                        <div class="icon text-primary"><i class="fas fa-route"></i></div>
                        <div class="label">Total Mileage</div>
                        <div class="value">${parseFloat(vehicle.currentMileage).toFixed(2)}</div>
                        <div class="subvalue">km</div>
                    </div>

                    <!-- Battery -->
                    <div class="data-card">
                        <div class="icon ${getBatteryColor(batteryLevel)}"><i class="fas fa-battery-half"></i></div>
                        <div class="label">Battery</div>
                        <div class="value">${batteryLevel.toFixed(2)}</div>
                        <div class="subvalue">V</div>
                    </div>

                    <!-- ACC Status -->
                    <div class="data-card">
                        <div class="icon ${vehicle.accStatus === '1' ? 'text-success' : 'text-secondary'}">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="label">Ignition</div>
                        <div class="value">${vehicle.accStatus === '1' ? 'ON' : 'OFF'}</div>
                    </div>

                    <!-- Last Update -->
                    <div class="data-card">
                        <div class="icon text-info"><i class="fas fa-clock"></i></div>
                        <div class="label">Last GPS Time</div>
                        <div class="value" style="font-size: 1rem;">${formatDateTime(vehicle.gpsTime)}</div>
                    </div>

                    <!-- Heartbeat -->
                    <div class="data-card">
                        <div class="icon text-danger"><i class="fas fa-heartbeat"></i></div>
                        <div class="label">Last Heartbeat</div>
                        <div class="value" style="font-size: 1rem;">${formatDateTime(vehicle.hbTime)}</div>
                    </div>

                    <!-- SIM Card -->
                    <div class="data-card">
                        <div class="icon text-success"><i class="fas fa-sim-card"></i></div>
                        <div class="label">SIM ICCID</div>
                        <div class="value" style="font-size: 0.9rem;">${vehicle.iccid}</div>
                    </div>
                </div>

                <!-- Map -->
                <div id="map"></div>

                <!-- Raw JSON -->
                <h5 class="mt-4"><i class="fas fa-code"></i> Raw JSON Data</h5>
                <pre>${JSON.stringify(rawData || vehicle, null, 2)}</pre>
            `;

            $('#locationResult').html(html);

            // Initialize map
            setTimeout(() => initMap(vehicle.lat, vehicle.lng, vehicle.deviceName || vehicle.device_name, speed, isOnline), 100);
        }

        function initMap(lat, lng, name, speed, isOnline) {
            if (map) {
                map.remove();
            }

            map = L.map('map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const iconColor = isOnline ? 'green' : 'red';
            const customIcon = L.divIcon({
                html: `<div style="background: ${iconColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-car" style="color: white; font-size: 14px;"></i>
                </div>`,
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            marker.bindPopup(`
                <div style="text-align: center;">
                    <strong>${name}</strong><br>
                    Speed: ${speed} km/h<br>
                    Lat: ${lat.toFixed(6)}<br>
                    Lng: ${lng.toFixed(6)}
                </div>
            `).openPopup();

            // Add circle around vehicle
            L.circle([lat, lng], {
                color: iconColor,
                fillColor: iconColor,
                fillOpacity: 0.1,
                radius: 100
            }).addTo(map);
        }

        function getMileage() {
            const imei = $('#mileageImei').val();
            const startTime = $('#mileageStart').val().replace('T', ' ') + ':00';
            const endTime = $('#mileageEnd').val().replace('T', ' ') + ':00';

            $('#mileageResult').html('<div class="loading-overlay"><div class="spinner"></div></div>').addClass('show');

            $.post('/jimi-test/mileage', {
                imei: imei,
                start_time: startTime,
                end_time: endTime
            }, function (res) {
                if (res.success) {
                    let html = `
                        <div class="data-grid">
                            <div class="data-card">
                                <div class="icon text-primary"><i class="fas fa-route"></i></div>
                                <div class="label">Distance Traveled</div>
                                <div class="value">${res.mileage_km}</div>
                                <div class="subvalue">kilometers</div>
                            </div>
                            <div class="data-card">
                                <div class="icon text-info"><i class="fas fa-clock"></i></div>
                                <div class="label">Duration</div>
                                <div class="value">${res.duration_minutes}</div>
                                <div class="subvalue">minutes</div>
                            </div>
                            <div class="data-card">
                                <div class="icon text-success"><i class="fas fa-tachometer-alt"></i></div>
                                <div class="label">Average Speed</div>
                                <div class="value">${(res.mileage_km / (res.duration_minutes / 60)).toFixed(2)}</div>
                                <div class="subvalue">km/h</div>
                            </div>
                        </div>
                        <pre class="mt-3">${JSON.stringify(res, null, 2)}</pre>
                    `;
                    $('#mileageResult').html(html);
                } else {
                    $('#mileageResult').html(`
                        <div class="text-center">
                            <i class="fas fa-exclamation-circle text-warning" style="font-size: 2rem;"></i>
                            <p class="mt-2">${res.message}</p>
                        </div>
                    `);
                }
            }).fail(function () {
                $('#mileageResult').html(`
                    <div class="text-center">
                        <i class="fas fa-times-circle text-danger" style="font-size: 2rem;"></i>
                        <p class="mt-2">Failed to calculate mileage</p>
                    </div>
                `);
            });
        }

        function getTrackData() {
            const imei = $('#trackImei').val();
            const startTime = $('#trackStart').val().replace('T', ' ') + ':00';
            const endTime = $('#trackEnd').val().replace('T', ' ') + ':00';

            $('#trackResult').html('<div class="loading-overlay"><div class="spinner"></div></div>').addClass('show');

            $.post('/jimi-test/track', {
                imei: imei,
                start_time: startTime,
                end_time: endTime
            }, function (res) {
                let html = `
                    <h5><i class="fas fa-map"></i> ${res.count} GPS Points Recorded</h5>
                    <div id="trackMap" style="height: 400px; border-radius: 15px; margin: 20px 0;"></div>
                    <pre>${JSON.stringify(res.data.slice(0, 10), null, 2)}</pre>
                `;
                if (res.count > 10) {
                    html += `<p class="text-muted">Showing first 10 of ${res.count} points in JSON</p>`;
                }
                $('#trackResult').html(html);

                // Initialize track map
                setTimeout(() => initTrackMap(res.data), 100);
            }).fail(function () {
                $('#trackResult').html(`
                    <div class="text-center">
                        <i class="fas fa-times-circle text-danger" style="font-size: 2rem;"></i>
                        <p class="mt-2">Failed to load track data</p>
                    </div>
                `);
            });
        }

        function initTrackMap(trackData) {
            const trackMap = L.map('trackMap');

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(trackMap);

            if (trackData && trackData.length > 0) {
                const latLngs = trackData.map(point => [point.lat || point.latitude, point.lng || point.longitude]);

                // Draw route line
                L.polyline(latLngs, {
                    color: '#667eea',
                    weight: 4,
                    opacity: 0.8
                }).addTo(trackMap);

                // Add start marker
                const startIcon = L.divIcon({
                    html: '<div style="background: green; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">S</div>',
                    className: '',
                    iconSize: [30, 30]
                });
                L.marker(latLngs[0], { icon: startIcon }).addTo(trackMap).bindPopup('Start');

                // Add end marker
                const endIcon = L.divIcon({
                    html: '<div style="background: red; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">E</div>',
                    className: '',
                    iconSize: [30, 30]
                });
                L.marker(latLngs[latLngs.length - 1], { icon: endIcon }).addTo(trackMap).bindPopup('End');

                // Fit bounds
                trackMap.fitBounds(latLngs);
            }
        }

        function fillTestDates() {
            const now = new Date();
            const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);

            $('#mileageStart, #trackStart').val(formatDateTimeInput(yesterday));
            $('#mileageEnd, #trackEnd').val(formatDateTimeInput(now));
        }

        function formatDateTimeInput(date) {
            return date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0') + 'T' +
                String(date.getHours()).padStart(2, '0') + ':' +
                String(date.getMinutes()).padStart(2, '0');
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getDirection(degrees) {
            const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            const index = Math.round(degrees / 45) % 8;
            return directions[index];
        }

        function getBatteryColor(voltage) {
            if (voltage < 3.3) return 'text-danger';
            if (voltage < 3.7) return 'text-warning';
            return 'text-success';
        }

        function formatTimeDifference(minutes) {
            if (minutes < 60) return `${Math.floor(minutes)} minutes`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''}`;
            const days = Math.floor(hours / 24);
            return `${days} day${days > 1 ? 's' : ''}`;
        }
    </script>
</body>

</html>

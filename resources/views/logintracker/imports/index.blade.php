@extends('adminlte::page')

@section('title', 'Import Data')

@section('content_header')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
        <h1 class="page-title mb-0">Import Data</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1 me-sm-2"></i> <span class="d-none d-sm-inline">Back to </span>Dashboard
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <p class="lead mb-4">Import users and sign-in logs. User-system associations and login status are automatically
            updated when importing sign-in data.</p>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>ℹ️ Info:</strong> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Import failed due to the following errors:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Import Forms --}}
        <div class="row g-3 g-lg-4">
            {{-- Users Import --}}
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0 h5"><i class="fas fa-users me-2"></i>Import Users</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('imports.users') }}" method="POST" enctype="multipart/form-data"
                            id="users-import-form">
                            @csrf
                            <div class="mb-3">
                                <label for="userFile" class="form-label fw-bold">Upload Users CSV</label>
                                <input type="file" class="form-control" id="userFile" name="import_file"
                                    accept=".csv,.txt" required>
                                <div class="mt-2" id="user-file-info" style="display:none;">
                                    <small class="text-muted">Selected: <span id="user-file-name"></span> <span
                                            id="user-file-size" class="file-size-badge"></span></small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-upload me-2"></i><span class="btn-text">Import Users</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sign-Ins Import --}}
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0 h5"><i class="fas fa-sign-in-alt me-2"></i>Import Sign-Ins</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('imports.sign_ins') }}" method="POST" enctype="multipart/form-data"
                            id="signin-import-form">
                            @csrf
                            <div class="mb-3">
                                <label for="signInFile" class="form-label fw-bold">Upload Sign-Ins CSV</label>
                                <input type="file" class="form-control" id="signInFile" name="import_file"
                                    accept=".csv,.txt" required>
                                <div class="mt-2" id="signin-file-info" style="display:none;">
                                    <small class="text-muted">Selected: <span id="signin-file-name"></span> <span
                                            id="signin-file-size" class="file-size-badge"></span></small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-upload me-2"></i><span class="btn-text">Import Sign-Ins</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Status Modal with Progress Table --}}
    <div class="overlay" id="import-overlay"></div>
    <div class="import-status" id="import-status">
        <div class="text-center mb-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 id="status-title">⏳ Importing Data...</h4>
            <p id="status-message" class="text-muted">Please wait while your data is being processed.</p>
        </div>

        {{-- Progress Table --}}
        <table class="table table-sm table-bordered progress-table">
            <thead class="table-light">
                <tr>
                    <th width="40%">Stage</th>
                    <th width="40%">Status</th>
                    <th width="20%">Time</th>
                </tr>
            </thead>
            <tbody>
                <tr id="stage-1">
                    <td>📤 File Upload</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>-</td>
                </tr>
                <tr id="stage-2">
                    <td>🔍 File Validation</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>-</td>
                </tr>
                <tr id="stage-3">
                    <td>⚙️ Processing Data</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>-</td>
                </tr>
                <tr id="stage-4">
                    <td>💾 Saving to Database</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>-</td>
                </tr>
                <tr id="stage-5">
                    <td>🧹 Cache Cleanup</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>-</td>
                </tr>
                <tr id="stage-6">
                    <td>✅ Finalizing</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>

        {{-- Timer and Stats --}}
        <div class="row text-center mt-3">
            <div class="col-4">
                <small class="text-muted">Elapsed Time</small>
                <div><strong id="elapsed-time">0</strong>s</div>
            </div>
            <div class="col-4">
                <small class="text-muted">Current Stage</small>
                <div><strong id="current-stage">1</strong> of 6</div>
            </div>
            <div class="col-4">
                <small class="text-muted">Estimated</small>
                <div><strong id="estimated-time">5-50</strong> min</div>
            </div>
        </div>

        {{-- Action Button (hidden initially) --}}
        <div class="text-center mt-4" id="action-container" style="display: none;">
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                Go to Dashboard →
            </a>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f1f1f;
        }

        @media (min-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
        }

        .import-status {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
            width: 95vw;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        @media (min-width: 768px) {
            .import-status {
                padding: 30px;
                width: 90vw;
            }
        }

        .import-status.show {
            display: block;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9998;
        }

        .overlay.show {
            display: block;
        }

        .spinner-border {
            width: 2.5rem;
            height: 2.5rem;
        }

        @media (min-width: 768px) {
            .spinner-border {
                width: 3rem;
                height: 3rem;
            }
        }

        .progress-table {
            font-size: 0.8rem;
        }

        @media (min-width: 768px) {
            .progress-table {
                font-size: 0.9rem;
            }
        }

        .status-badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
        }

        @media (min-width: 768px) {
            .status-badge {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-running {
            background: #17a2b8;
            color: #fff;
        }

        .status-complete {
            background: #28a745;
            color: #fff;
        }

        .pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .file-size-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e3f2fd;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #1976d2;
            margin-left: 5px;
        }

        /* Responsive card improvements */
        .card {
            border: 1px solid #dee2e6;
        }

        .card-header h3 {
            font-size: 1.1rem;
        }

        @media (min-width: 768px) {
            .card-header h3 {
                font-size: 1.25rem;
            }
        }

        /* Responsive button improvements */
        .btn-lg {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }

        @media (min-width: 768px) {
            .btn-lg {
                font-size: 1.125rem;
                padding: 0.875rem 1.25rem;
            }
        }

        /* Alert responsiveness */
        .alert {
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .alert {
                font-size: 1rem;
            }
        }

        /* Progress table mobile optimization */
        .progress-table td:first-child {
            max-width: 120px;
            word-wrap: break-word;
        }

        @media (min-width: 576px) {
            .progress-table td:first-child {
                max-width: none;
            }
        }

        /* Status message responsiveness */
        #status-title {
            font-size: 1.25rem;
        }

        @media (min-width: 768px) {
            #status-title {
                font-size: 1.5rem;
            }
        }

        #status-message {
            font-size: 0.875rem;
        }

        @media (min-width: 768px) {
            #status-message {
                font-size: 1rem;
            }
        }

        /* Timer stats responsiveness */
        .row.text-center.mt-3 {
            font-size: 0.85rem;
        }

        @media (min-width: 768px) {
            .row.text-center.mt-3 {
                font-size: 1rem;
            }
        }
    </style>
@endsection

@section('js')
    <script>
        // ============================================
        // FILE SIZE DISPLAY - Show file size when selected
        // ============================================
        document.getElementById('userFile').addEventListener('change', function(e) {
            displayFileInfo(e.target, 'user-file-info', 'user-file-name', 'user-file-size');
        });

        document.getElementById('signInFile').addEventListener('change', function(e) {
            displayFileInfo(e.target, 'signin-file-info', 'signin-file-name', 'signin-file-size');
        });

        function displayFileInfo(input, infoId, nameId, sizeId) {
            const file = input.files[0];
            if (file) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                document.getElementById(infoId).style.display = 'block';
                document.getElementById(nameId).textContent = file.name;
                document.getElementById(sizeId).textContent = sizeMB + ' MB';

                // ✅ ENHANCED: More granular warnings with better UX
                const sizeSpan = document.getElementById(sizeId);

                if (sizeMB > 15) {
                    sizeSpan.style.background = '#dc3545';
                    sizeSpan.style.color = '#fff';
                    sizeSpan.title = 'File too large - will likely timeout. Split into 5MB chunks.';

                    const shouldContinue = confirm(
                        '⚠️ WARNING: ' + sizeMB + 'MB file is very large and will likely timeout.\n\n' +
                        'Your import may fail after 50+ minutes of processing.\n\n' +
                        'Recommendation: Split your file into chunks of 5MB or less.\n\n' +
                        'Do you want to continue anyway?'
                    );

                    if (!shouldContinue) {
                        input.value = '';
                        document.getElementById(infoId).style.display = 'none';
                        return;
                    }
                } else if (sizeMB > 10) {
                    sizeSpan.style.background = '#ff6b6b';
                    sizeSpan.style.color = '#fff';
                    sizeSpan.title = 'Very large file - may take 40+ minutes or timeout';
                    console.warn('Large file selected:', sizeMB, 'MB - Import may take 40+ minutes');
                } else if (sizeMB > 6) {
                    sizeSpan.style.background = '#ffc107';
                    sizeSpan.style.color = '#000';
                    sizeSpan.title = 'Large file - may take 30+ minutes';
                } else if (sizeMB > 3) {
                    sizeSpan.style.background = '#17a2b8';
                    sizeSpan.style.color = '#fff';
                    sizeSpan.title = 'Medium file - estimated 10-15 minutes';
                } else {
                    sizeSpan.style.background = '#28a745';
                    sizeSpan.style.color = '#fff';
                    sizeSpan.title = 'Good file size - should complete quickly';
                }
            }
        }

        // ✅ NEW: Validate file before form submission
        function validateFileBeforeSubmit(form) {
            const fileInput = form.querySelector('input[type="file"]');

            if (!fileInput || !fileInput.files.length) {
                alert('⚠️ Please select a file to import.');
                return false;
            }

            const file = fileInput.files[0];
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);

            if (sizeMB > 250) {
                alert('❌ File too large (' + sizeMB +
                    'MB).\n\nMaximum file size is 250MB.\n\nPlease split your file into smaller chunks.');
                return false;
            }

            if (sizeMB > 12 && sizeMB <= 15) {
                const confirmLarge = confirm(
                    '⚠️ Large file detected (' + sizeMB + 'MB).\n\n' +
                    'This import may take 30-50 minutes.\n\n' +
                    'Are you ready to wait?\n\n' +
                    '💡 Tip: You can safely close this tab after clicking OK. ' +
                    'The import will continue on the server.'
                );

                if (!confirmLarge) {
                    return false;
                }
            }

            return true;
        }

        // ============================================
        // BACKEND PROGRESS POLLING VARIABLES
        // ============================================
        let pollInterval = null;
        let timerInterval = null;
        let elapsedSeconds = 0;
        let currentStage = 1;
        let lastMessageUpdate = 0;

        // ============================================
        // FORM SUBMISSION HANDLERS
        // ============================================
        document.getElementById('users-import-form').addEventListener('submit', function(e) {
            if (!validateFileBeforeSubmit(this)) {
                e.preventDefault();
                return false;
            }
            handleImportSubmit(this, 'Users');
        });

        document.getElementById('signin-import-form').addEventListener('submit', function(e) {
            if (!validateFileBeforeSubmit(this)) {
                e.preventDefault();
                return false;
            }
            handleImportSubmit(this, 'Sign-Ins');
        });

        function handleImportSubmit(form, importType) {
            const btn = form.querySelector('button[type="submit"]');
            const btnText = btn.querySelector('.btn-text');
            const fileInput = form.querySelector('input[type="file"]');

            if (!fileInput.files.length) {
                alert('Please select a file to import');
                return false;
            }

            const file = fileInput.files[0];
            const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);

            btn.disabled = true;
            btnText.innerHTML = '⏳ Uploading...';

            showImportStatus(importType, fileSizeMB);
            startProgressSimulation(fileSizeMB);

            // ✅ CRITICAL: Start polling backend for real progress
            pollImportProgress();
        }

        function showImportStatus(importType, fileSizeMB) {
            const overlay = document.getElementById('import-overlay');
            const status = document.getElementById('import-status');
            const title = document.getElementById('status-title');
            const message = document.getElementById('status-message');
            const estimatedTime = document.getElementById('estimated-time');

            let estimatedMinutes = Math.ceil(fileSizeMB * 5);
            estimatedMinutes = Math.max(2, Math.min(50, estimatedMinutes));

            title.innerHTML = `⏳ Importing ${importType}...`;
            message.innerHTML =
                `Processing ${fileSizeMB}MB of ${importType.toLowerCase()} data. Large files may take up to ${estimatedMinutes} minutes.`;
            estimatedTime.textContent = `${estimatedMinutes}`;

            overlay.classList.add('show');
            status.classList.add('show');
        }

        // ============================================
        // PROGRESS SIMULATION (FALLBACK)
        // ============================================
        function startProgressSimulation(fileSizeMB) {
            const timerSpan = document.getElementById('elapsed-time');
            const stageSpan = document.getElementById('current-stage');
            const statusMessage = document.getElementById('status-message');

            const fileSize = parseFloat(fileSizeMB);

            updateStage(1, 'running');
            setTimeout(() => updateStage(1, 'complete', '1s'), 1000);

            setTimeout(() => {
                updateStage(2, 'running');
                currentStage = 2;
                stageSpan.textContent = currentStage;
            }, 1000);
            setTimeout(() => updateStage(2, 'complete', '3s'), 4000);

            setTimeout(() => {
                updateStage(3, 'running');
                currentStage = 3;
                stageSpan.textContent = currentStage;
                statusMessage.innerHTML = `📊 Processing ${fileSizeMB}MB file - this is the longest stage...`;
            }, 4000);

            timerInterval = setInterval(function() {
                elapsedSeconds++;
                timerSpan.textContent = elapsedSeconds;
                updateProgressMessages(statusMessage, elapsedSeconds, fileSize);
                autoAdvanceStages(stageSpan);
            }, 1000);
        }

        function updateProgressMessages(statusMessage, elapsed, fileSize) {
            if (elapsed - lastMessageUpdate < 30) return;
            lastMessageUpdate = elapsed;

            const messages = getProgressMessages(fileSize, elapsed);
            for (const msg of messages) {
                if (elapsed >= msg.time && elapsed < msg.time + 30) {
                    statusMessage.innerHTML = msg.text;
                    break;
                }
            }
        }

        function getProgressMessages(fileSize, elapsed) {
            const messages = [];

            if (fileSize <= 2) {
                messages.push({
                    time: 30,
                    text: '📊 Processing data...'
                }, {
                    time: 60,
                    text: '💾 Saving to database...'
                }, {
                    time: 120,
                    text: '🔄 Finalizing import...'
                }, {
                    time: 180,
                    text: '⏳ Almost done...'
                });
            } else if (fileSize <= 5) {
                messages.push({
                    time: 60,
                    text: '📊 Processing medium-sized file...'
                }, {
                    time: 180,
                    text: '💾 Saving batches to database...'
                }, {
                    time: 300,
                    text: '🔄 Still processing - this is normal for larger files...'
                }, {
                    time: 600,
                    text: '⏳ Processing continues - please be patient...'
                }, {
                    time: 900,
                    text: '⏰ Large import in progress...'
                });
            } else {
                messages.push({
                    time: 60,
                    text: `📊 Processing large ${fileSize}MB file - Stage 1/4...`
                }, {
                    time: 180,
                    text: '📈 Processing continues - Stage 2/4...'
                }, {
                    time: 300,
                    text: '💾 Saving data batches - Stage 3/4...'
                }, {
                    time: 600,
                    text: '🔄 Still saving - this is normal for large files...'
                }, {
                    time: 900,
                    text: '⏳ Large import in progress - Stage 4/4...'
                }, {
                    time: 1200,
                    text: '⏰ Processing large file - almost there...'
                }, {
                    time: 1800,
                    text: '⚠️ Large file processing - please continue waiting...'
                }, {
                    time: 2400,
                    text: '🔥 Very large file - final processing stages...'
                }, {
                    time: 3000,
                    text: '⏳ Completing import - just a bit longer...'
                });
            }

            return messages;
        }

        function autoAdvanceStages(stageSpan) {
            if (elapsedSeconds === 300 && currentStage === 3) {
                updateStage(3, 'complete', '~5 min');
                updateStage(4, 'running');
                currentStage = 4;
                stageSpan.textContent = currentStage;
            }

            if (elapsedSeconds === 900 && currentStage === 4) {
                updateStage(4, 'complete', '~15 min');
                updateStage(5, 'running');
                currentStage = 5;
                stageSpan.textContent = currentStage;
            }

            if (elapsedSeconds === 1200 && currentStage === 5) {
                updateStage(5, 'complete', '5s');
                updateStage(6, 'running');
                currentStage = 6;
                stageSpan.textContent = currentStage;
            }

            if (elapsedSeconds === 3300) {
                document.getElementById('status-message').innerHTML =
                    '⚠️ Import is taking very long (55 min). It may timeout soon. Please wait or contact support if it fails.';
            }

            if (elapsedSeconds === 3480) {
                document.getElementById('status-message').innerHTML =
                    '🔴 Approaching timeout limit. If import fails, please split your file into smaller chunks.';
            }
        }

        function updateStage(stageNum, status, time) {
            const row = document.getElementById(`stage-${stageNum}`);
            if (!row) return;

            const badge = row.querySelector('.status-badge');
            const timeCell = row.cells[2];

            badge.classList.remove('status-pending', 'status-running', 'status-complete', 'pulse');

            if (status === 'running') {
                badge.classList.add('status-running', 'pulse');
                badge.textContent = 'Running...';
            } else if (status === 'complete') {
                badge.classList.add('status-complete');
                badge.textContent = '✓ Complete';
                if (time) timeCell.textContent = time;
            }
        }

        function showCompletionMessage() {
            clearInterval(timerInterval);
            if (pollInterval) clearInterval(pollInterval);

            const title = document.getElementById('status-title');
            const message = document.getElementById('status-message');
            const actionContainer = document.getElementById('action-container');
            const spinner = document.querySelector('.spinner-border');

            const minutes = Math.floor(elapsedSeconds / 60);
            const seconds = elapsedSeconds % 60;
            const timeString = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;

            title.innerHTML = '✅ Import Complete!';
            message.innerHTML =
                `Your data has been successfully imported in ${timeString}. If the page doesn't redirect automatically, click the button below.`;

            if (spinner) spinner.style.display = 'none';
            if (actionContainer) actionContainer.style.display = 'block';

            for (let i = currentStage; i <= 6; i++) {
                updateStage(i, 'complete', '✓');
            }

            setTimeout(() => {
                window.location.href = '{{ route('dashboard') }}';
            }, 3000);
        }

        // ============================================
        // ✅ BACKEND PROGRESS POLLING
        // ============================================
        function pollImportProgress() {
            console.log('🔄 Starting backend progress polling...');

            pollInterval = setInterval(function() {
                fetch('/imports/status')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.progress) {
                            const progress = data.data.progress;
                            console.log('📊 Backend progress:', progress);

                            if (progress.stages) {
                                updateStagesFromBackend(progress.stages);
                            }

                            if (progress.current_stage) {
                                document.getElementById('current-stage').textContent = progress.current_stage;
                                currentStage = progress.current_stage;
                            }

                            if (progress.status === 'completed') {
                                console.log('✅ Import completed - stopping polling');
                                clearInterval(pollInterval);
                                showCompletionMessage();
                            } else if (progress.status === 'failed') {
                                console.log('❌ Import failed - stopping polling');
                                clearInterval(pollInterval);
                                showErrorMessage(progress.error || 'Import failed');
                            }
                        }
                    })
                    .catch(error => {
                        console.warn('⚠️ Progress polling failed:', error);
                    });
            }, 2000);

            setTimeout(() => {
                if (pollInterval) {
                    clearInterval(pollInterval);
                    console.log('⏱️ Polling timeout reached (65 minutes)');
                }
            }, 3900000);
        }

        function updateStagesFromBackend(stages) {
            const stageMapping = {
                'file_upload': 1,
                'file_validation': 2,
                'processing_data': 3,
                'saving_to_database': 4,
                'cache_cleanup': 5,
                'finalizing': 6
            };

            Object.keys(stages).forEach(stageName => {
                const stageNum = stageMapping[stageName];
                const stageData = stages[stageName];

                if (stageNum && stageData) {
                    updateStage(stageNum, stageData.status, stageData.time);
                    console.log(`✓ Stage ${stageNum} (${stageName}): ${stageData.status} - ${stageData.time}`);
                }
            });
        }

        function showErrorMessage(errorText) {
            clearInterval(timerInterval);
            if (pollInterval) clearInterval(pollInterval);

            const title = document.getElementById('status-title');
            const message = document.getElementById('status-message');
            const actionContainer = document.getElementById('action-container');
            const spinner = document.querySelector('.spinner-border');

            title.innerHTML = '❌ Import Failed';
            message.innerHTML = errorText || 'An error occurred during import. Please check the logs or try again.';

            if (spinner) spinner.style.display = 'none';
            if (actionContainer) actionContainer.style.display = 'block';
        }

        // ============================================
        // CLEANUP HANDLERS
        // ============================================
        window.addEventListener('beforeunload', function() {
            if (timerInterval) clearInterval(timerInterval);
            if (pollInterval) clearInterval(pollInterval);
        });

        window.addEventListener('beforeunload', function(e) {
            if (timerInterval && elapsedSeconds > 10) {
                const confirmationMessage =
                    'Import is still in progress. Are you sure you want to leave? The import will continue on the server.';
                e.returnValue = confirmationMessage;
                return confirmationMessage;
            }
        });

        console.log('🚀 Import progress tracker initialized - Ready for large file imports (up to 60 minutes)');
    </script>
@endsection

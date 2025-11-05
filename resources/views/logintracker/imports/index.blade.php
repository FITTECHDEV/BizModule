<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .import-status {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0,0,0,0.4);
            min-width: 600px;
            max-width: 90vw;
            max-height: 80vh;
            overflow-y: auto;
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
            background: rgba(0,0,0,0.7);
            z-index: 9998;
        }
        .overlay.show {
            display: block;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .progress-table {
            font-size: 0.9rem;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
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
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
    </style>
</head>
<body>
<div class="container mt-5">
   {{-- Page Title and Back Button on same line --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Import Data</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <p class="lead">Import users and sign-in logs. User-system associations and login status are automatically updated when importing sign-in data.</p>


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
    <div class="row">
        {{-- Users Import --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Import Users</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('imports.users') }}" method="POST" enctype="multipart/form-data" id="users-import-form">
                        @csrf
                        <div class="mb-3">
                            <label for="userFile" class="form-label">Upload Users CSV</label>
                            <input type="file" class="form-control" id="userFile" name="import_file" accept=".csv,.txt" required>
                            <!-- <div class="form-text">
                                Accepted formats: CSV, TXT<br>
                                Maximum size: 250MB
                            </div> -->
                            <div class="mt-2" id="user-file-info" style="display:none;">
                                <small>Selected: <span id="user-file-name"></span> <span id="user-file-size" class="file-size-badge"></span></small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Import Users</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sign-Ins Import --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">Import Sign-Ins</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('imports.sign_ins') }}" method="POST" enctype="multipart/form-data" id="signin-import-form">
                        @csrf
                        <div class="mb-3">
                            <label for="signInFile" class="form-label">Upload Sign-Ins CSV</label>
                            <input type="file" class="form-control" id="signInFile" name="import_file" accept=".csv,.txt" required>
                            <!-- <div class="form-text">
                                Accepted formats: CSV, TXT<br>
                                Maximum size: 250MB
                            </div> -->
                            <div class="mt-2" id="signin-file-info" style="display:none;">
                                <small>Selected: <span id="signin-file-name"></span> <span id="signin-file-size" class="file-size-badge"></span></small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <span class="btn-text">Import Sign-Ins</span>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        
        // Warn if file is very large
        if (sizeMB > 10) {
            const sizeSpan = document.getElementById(sizeId);
            sizeSpan.style.background = '#fff3cd';
            sizeSpan.style.color = '#856404';
            sizeSpan.title = 'Large file - may take 40+ minutes';
        }
    }
}

// ============================================
// OPTIMIZED IMPORT PROGRESS TRACKER
// Supports large file imports (6MB+) with 60-minute timeout
// ============================================

// Handle form submissions
document.getElementById('users-import-form').addEventListener('submit', function(e) {
    handleImportSubmit(this, 'Users');
});

document.getElementById('signin-import-form').addEventListener('submit', function(e) {
    handleImportSubmit(this, 'Sign-Ins');
});

function handleImportSubmit(form, importType) {
    const btn = form.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('.btn-text');
    const fileInput = form.querySelector('input[type="file"]');
    
    // Validate file is selected
    if (!fileInput.files.length) {
        alert('Please select a file to import');
        return false;
    }
    
    // Get file size to estimate processing time
    const file = fileInput.files[0];
    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
    
    // Disable button
    btn.disabled = true;
    btnText.innerHTML = '⏳ Uploading...';
    
    // Show status modal with file size info
    showImportStatus(importType, fileSizeMB);
    
    // Start progress simulation based on file size
    startProgressSimulation(fileSizeMB);
}

function showImportStatus(importType, fileSizeMB) {
    const overlay = document.getElementById('import-overlay');
    const status = document.getElementById('import-status');
    const title = document.getElementById('status-title');
    const message = document.getElementById('status-message');
    const estimatedTime = document.getElementById('estimated-time');
    
    // Calculate estimated time based on file size
    // Rough estimate: ~5 minutes per 1MB
    let estimatedMinutes = Math.ceil(fileSizeMB * 5);
    estimatedMinutes = Math.max(2, Math.min(50, estimatedMinutes)); // Between 2-50 minutes
    
    title.innerHTML = `⏳ Importing ${importType}...`;
    message.innerHTML = `Processing ${fileSizeMB}MB of ${importType.toLowerCase()} data. Large files may take up to ${estimatedMinutes} minutes.`;
    estimatedTime.textContent = `${estimatedMinutes}`;
    
    overlay.classList.add('show');
    status.classList.add('show');
}

let timerInterval;
let elapsedSeconds = 0;
let currentStage = 1;
let lastMessageUpdate = 0;

function startProgressSimulation(fileSizeMB) {
    const timerSpan = document.getElementById('elapsed-time');
    const stageSpan = document.getElementById('current-stage');
    const statusMessage = document.getElementById('status-message');
    
    // Calculate dynamic stage durations based on file size
    const fileSize = parseFloat(fileSizeMB);
    const isLargeFile = fileSize > 5; // Files > 5MB are considered "large"
    
    // Stage 1: File Upload (1s)
    updateStage(1, 'running');
    setTimeout(() => updateStage(1, 'complete', '1s'), 1000);
    
    // Stage 2: File Validation (3s)
    setTimeout(() => {
        updateStage(2, 'running');
        currentStage = 2;
        stageSpan.textContent = currentStage;
    }, 1000);
    setTimeout(() => updateStage(2, 'complete', '3s'), 4000);
    
    // Stage 3: Processing Data (variable - main processing)
    setTimeout(() => {
        updateStage(3, 'running');
        currentStage = 3;
        stageSpan.textContent = currentStage;
        statusMessage.innerHTML = `📊 Processing ${fileSizeMB}MB file - this is the longest stage...`;
    }, 4000);
    
    // Start continuous timer
    timerInterval = setInterval(function() {
        elapsedSeconds++;
        timerSpan.textContent = elapsedSeconds;
        
        // Update progress messages based on elapsed time and file size
        updateProgressMessages(statusMessage, elapsedSeconds, fileSize);
        
        // Auto-advance stages based on time if they're stuck
        autoAdvanceStages(stageSpan);
        
    }, 1000);
}

function updateProgressMessages(statusMessage, elapsed, fileSize) {
    // Only update every 30 seconds to avoid flashing
    if (elapsed - lastMessageUpdate < 30) {
        return;
    }
    lastMessageUpdate = elapsed;
    
    // Dynamic messages based on file size and elapsed time
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
        // Small file messages (2MB or less)
        messages.push(
            { time: 30, text: '📊 Processing data...' },
            { time: 60, text: '💾 Saving to database...' },
            { time: 120, text: '🔄 Finalizing import...' },
            { time: 180, text: '⏳ Almost done...' }
        );
    } else if (fileSize <= 5) {
        // Medium file messages (2-5MB)
        messages.push(
            { time: 60, text: '📊 Processing medium-sized file...' },
            { time: 180, text: '💾 Saving batches to database...' },
            { time: 300, text: '🔄 Still processing - this is normal for larger files...' },
            { time: 600, text: '⏳ Processing continues - please be patient...' },
            { time: 900, text: '⏰ Large import in progress...' }
        );
    } else {
        // Large file messages (5MB+)
        messages.push(
            { time: 60, text: `📊 Processing large ${fileSize}MB file - Stage 1/4...` },
            { time: 180, text: '📈 Processing continues - Stage 2/4...' },
            { time: 300, text: '💾 Saving data batches - Stage 3/4...' },
            { time: 600, text: '🔄 Still saving - this is normal for large files...' },
            { time: 900, text: '⏳ Large import in progress - Stage 4/4...' },
            { time: 1200, text: '⏰ Processing large file - almost there...' },
            { time: 1800, text: '⚠️ Large file processing - please continue waiting...' },
            { time: 2400, text: '🔥 Very large file - final processing stages...' },
            { time: 3000, text: '⏳ Completing import - just a bit longer...' }
        );
    }
    
    return messages;
}

function autoAdvanceStages(stageSpan) {
    // Auto-advance stages if they seem stuck (based on elapsed time)
    
    // If we've been running for 5 minutes and still on Stage 3
    if (elapsedSeconds === 300 && currentStage === 3) {
        updateStage(3, 'complete', '~5 min');
        updateStage(4, 'running');
        currentStage = 4;
        stageSpan.textContent = currentStage;
    }
    
    // If we've been running for 15 minutes and still on Stage 4
    if (elapsedSeconds === 900 && currentStage === 4) {
        updateStage(4, 'complete', '~15 min');
        updateStage(5, 'running');
        currentStage = 5;
        stageSpan.textContent = currentStage;
    }
    
    // If we've been running for 20 minutes and still on Stage 5
    if (elapsedSeconds === 1200 && currentStage === 5) {
        updateStage(5, 'complete', '5s');
        updateStage(6, 'running');
        currentStage = 6;
        stageSpan.textContent = currentStage;
    }
    
    // Safety timeout: If running for 55 minutes, show warning
    if (elapsedSeconds === 3300) {
        const statusMessage = document.getElementById('status-message');
        statusMessage.innerHTML = '⚠️ Import is taking very long (55 min). It may timeout soon. Please wait or contact support if it fails.';
    }
    
    // If somehow we reach 58 minutes, prepare for potential timeout
    if (elapsedSeconds === 3480) {
        const statusMessage = document.getElementById('status-message');
        statusMessage.innerHTML = '🔴 Approaching timeout limit. If import fails, please split your file into smaller chunks.';
    }
}

function updateStage(stageNum, status, time) {
    const row = document.getElementById(`stage-${stageNum}`);
    if (!row) return; // Safety check
    
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
    
    const title = document.getElementById('status-title');
    const message = document.getElementById('status-message');
    const actionContainer = document.getElementById('action-container');
    const spinner = document.querySelector('.spinner-border');
    
    const minutes = Math.floor(elapsedSeconds / 60);
    const seconds = elapsedSeconds % 60;
    const timeString = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
    
    title.innerHTML = '✅ Import Complete!';
    message.innerHTML = `Your data has been successfully imported in ${timeString}. If the page doesn\'t redirect automatically, click the button below.`;
    
    if (spinner) spinner.style.display = 'none';
    if (actionContainer) actionContainer.style.display = 'block';
    
    // Mark all remaining stages as complete
    for (let i = currentStage; i <= 6; i++) {
        updateStage(i, 'complete', '✓');
    }
    
    // Try to redirect after 3 seconds
    setTimeout(() => {
        window.location.href = '{{ route("dashboard") }}';
    }, 3000);
}

// Clear timer if page unloads
window.addEventListener('beforeunload', function() {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

// Warn user if they try to close during import
window.addEventListener('beforeunload', function(e) {
    if (timerInterval && elapsedSeconds > 10) {
        const confirmationMessage = 'Import is still in progress. Are you sure you want to leave? The import will continue on the server.';
        e.returnValue = confirmationMessage;
        return confirmationMessage;
    }
});

console.log('🚀 Import progress tracker initialized - Ready for large file imports (up to 60 minutes)');
</script>
</body>
</html>
@extends('adminlte::page')

@section('title', 'Z-Report Submission')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="zr-page-title">Z-Report Submission</h1>
            <p class="zr-page-subtitle">Submit daily tax reports to the revenue authority</p>
        </div>
        <div class="zr-status-pill" id="statusPill">
            <span class="zr-status-dot"></span>
            <span class="zr-status-text">Ready</span>
        </div>
    </div>
@endsection

@section('content')
<style>
    :root {
        --fit-blue:    #1a3a5c;
        --fit-accent:  #1e88e5;
        --fit-success: #00897b;
        --fit-danger:  #c62828;
        --fit-bg:      #f0f4f8;
        --fit-card:    #ffffff;
        --fit-border:  #d0dce8;
        --fit-text:    #1a2b3c;
        --fit-muted:   #607080;
        --fit-tab-h:   #e8f0fe;
        --radius:      10px;
        --shadow:      0 2px 12px rgba(26,58,92,.10);
        --transition:  .22s cubic-bezier(.4,0,.2,1);
    }

    .zr-page-title    { font-size: 1.55rem; font-weight: 700; color: var(--fit-blue); margin: 0; }
    .zr-page-subtitle { font-size: .82rem; color: var(--fit-muted); margin: 2px 0 0; }

    /* Status pill */
    .zr-status-pill {
        display: inline-flex; align-items: center; gap: 7px;
        background: var(--fit-card); border: 1px solid var(--fit-border);
        padding: 6px 14px; border-radius: 999px;
        font-size: .78rem; font-weight: 600; color: var(--fit-muted);
        box-shadow: var(--shadow);
    }
    .zr-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #90a4ae; transition: background var(--transition); }
    .zr-status-pill.running .zr-status-dot  { background: var(--fit-accent); animation: pulse 1s infinite; }
    .zr-status-pill.running .zr-status-text { color: var(--fit-accent); }
    .zr-status-pill.success .zr-status-dot  { background: var(--fit-success); }
    .zr-status-pill.success .zr-status-text { color: var(--fit-success); }
    .zr-status-pill.error   .zr-status-dot  { background: var(--fit-danger); }
    .zr-status-pill.error   .zr-status-text { color: var(--fit-danger); }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }

    /* Wrapper */
    .zr-wrapper { background: var(--fit-card); border: 1px solid var(--fit-border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }

    /* Tabs */
    .zr-tabs { display: flex; border-bottom: 2px solid var(--fit-border); background: var(--fit-bg); }
    .zr-tab {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 9px;
        padding: 16px 20px; font-size: .9rem; font-weight: 600; color: var(--fit-muted);
        cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px;
        transition: color var(--transition), border-color var(--transition), background var(--transition);
        user-select: none;
    }
    .zr-tab:hover { background: var(--fit-tab-h); color: var(--fit-accent); }
    .zr-tab.active { color: var(--fit-accent); border-bottom-color: var(--fit-accent); background: var(--fit-card); }
    .zr-tab-flag { font-size: 1.1rem; }

    /* Panels */
    .zr-panel { display: none; padding: 30px; }
    .zr-panel.active { display: block; }

    /* Info banner */
    .zr-info-banner {
        display: flex; align-items: center; gap: 14px;
        background: #eaf3fb; border: 1px solid #b8d8f5; border-radius: 8px;
        padding: 14px 18px; margin-bottom: 28px;
        font-size: .84rem; color: var(--fit-blue);
    }
    .zr-info-banner i { font-size: 1.3rem; color: var(--fit-accent); flex-shrink: 0; }
    .zr-info-banner strong { display: block; font-size: .9rem; margin-bottom: 2px; }

    /* Section label */
    .zr-section-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--fit-muted); margin-bottom: 10px;
    }

    /* Option cards */
    .zr-option-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
    .zr-option-card {
        border: 2px solid var(--fit-border); border-radius: 8px; padding: 16px;
        cursor: pointer; transition: all var(--transition);
        display: flex; align-items: flex-start; gap: 12px;
    }
    .zr-option-card:hover { border-color: var(--fit-accent); background: var(--fit-tab-h); }
    .zr-option-card.selected { border-color: var(--fit-accent); background: #e3f0fd; box-shadow: 0 0 0 3px rgba(30,136,229,.12); }
    .zr-option-card input[type=radio] { display: none; }
    .zr-option-icon {
        width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0;
        background: var(--fit-bg); border: 1px solid var(--fit-border);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: var(--fit-accent);
        transition: all var(--transition);
    }
    .zr-option-card.selected .zr-option-icon { background: var(--fit-accent); color: #fff; border-color: var(--fit-accent); }
    .zr-option-title { font-size: .88rem; font-weight: 700; color: var(--fit-text); margin-bottom: 3px; }
    .zr-option-desc  { font-size: .76rem; color: var(--fit-muted); line-height: 1.45; }

    /* Date fields */
    .zr-date-section { display: none; margin-bottom: 22px; }
    .zr-date-section.visible { display: block; }
    .zr-date-section label { display: block; font-size: .8rem; font-weight: 700; color: var(--fit-blue); margin-bottom: 6px; }
    .zr-date-input {
        width: 100%; max-width: 240px;
        border: 1.5px solid var(--fit-border); border-radius: 7px;
        padding: 9px 13px; font-size: .88rem; color: var(--fit-text);
        background: var(--fit-bg); outline: none;
        transition: border-color var(--transition), box-shadow var(--transition);
    }
    .zr-date-input:focus { border-color: var(--fit-accent); box-shadow: 0 0 0 3px rgba(30,136,229,.12); background: #fff; }
    .zr-date-hint { font-size: .74rem; color: var(--fit-muted); margin-top: 5px; }

    /* Submit button */
    .zr-submit-btn {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, var(--fit-accent) 0%, var(--fit-blue) 100%);
        color: #fff; border: none; border-radius: 8px;
        padding: 12px 28px; font-size: .92rem; font-weight: 700;
        cursor: pointer; box-shadow: 0 3px 10px rgba(30,136,229,.3);
        transition: opacity var(--transition), transform var(--transition), box-shadow var(--transition);
    }
    .zr-submit-btn:hover:not(:disabled) { box-shadow: 0 5px 18px rgba(30,136,229,.4); transform: translateY(-1px); }
    .zr-submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .zr-spinner { display: none; }
    .zr-submit-btn.loading .zr-spinner { display: inline-block; }

    /* Result area */
    .zr-divider { border: none; border-top: 1px solid var(--fit-border); margin: 26px 0; }
    .zr-result-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .zr-result-label  { font-size: .78rem; font-weight: 700; color: var(--fit-blue); text-transform: uppercase; letter-spacing: .06em; }
    .zr-clear-btn { font-size: .73rem; color: var(--fit-muted); background: none; border: none; cursor: pointer; padding: 2px 8px; border-radius: 4px; transition: all var(--transition); }
    .zr-clear-btn:hover { color: var(--fit-danger); background: #fde8e8; }

    /* Result log — simplified messages only */
    .zr-result-log {
        background: #f8fafc; border: 1px solid var(--fit-border);
        border-radius: 8px; padding: 16px 18px;
        min-height: 120px; max-height: 320px; overflow-y: auto;
        font-size: .84rem; line-height: 1.7; color: var(--fit-text);
    }
    .zr-result-log:empty::after { content: 'Results will appear here after you submit.'; color: var(--fit-muted); font-style: italic; }
    .zr-log-row { display: flex; align-items: flex-start; gap: 10px; padding: 4px 0; border-bottom: 1px solid #eef2f7; }
    .zr-log-row:last-child { border-bottom: none; }
    .zr-log-icon { width: 20px; flex-shrink: 0; text-align: center; margin-top: 2px; }
    .zr-log-row.success .zr-log-icon { color: var(--fit-success); }
    .zr-log-row.error   .zr-log-icon { color: var(--fit-danger); }
    .zr-log-row.warn    .zr-log-icon { color: #f9a825; }
    .zr-log-row.info    .zr-log-icon { color: var(--fit-accent); }
    .zr-log-ts { font-size: .72rem; color: var(--fit-muted); flex-shrink: 0; margin-top: 3px; }
    .zr-log-msg { flex: 1; }

    .zr-result-log::-webkit-scrollbar { width: 5px; }
    .zr-result-log::-webkit-scrollbar-thumb { background: var(--fit-border); border-radius: 3px; }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="zr-wrapper">

                {{-- Tabs --}}
                <div class="zr-tabs">
                    <div class="zr-tab active" onclick="switchTab('tz', this)">
                        <span class="zr-tab-flag">🌍</span>
                        FIT ADL
                    </div>
                    <div class="zr-tab" onclick="switchTab('ar', this)">
                        <span class="zr-tab-flag">🇹🇿</span>
                        FIT Tanzania
                    </div>
                </div>

                {{-- ══ TZ Panel ══ --}}
                <div class="zr-panel active" id="panel-tz">

                    <div class="zr-info-banner">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>FIT ADL — Daily Tax Report</strong>
                            Use this page to submit the end-of-day sales report for the ADL office.
                            Choose one of the options below and click <em>Submit Report</em>.
                        </div>
                    </div>

                    <div class="zr-section-label">What would you like to do?</div>

                    <div class="zr-option-grid">
                        <label class="zr-option-card selected" onclick="setMode('tz','auto',this)">
                            <input type="radio" name="tz_mode" value="auto" checked>
                            <div class="zr-option-icon"><i class="fas fa-bolt"></i></div>
                            <div>
                                <div class="zr-option-title">Submit Today's Report</div>
                                <div class="zr-option-desc">Automatically finds and submits the report that is due today. Use this for your daily routine.</div>
                            </div>
                        </label>

                        <label class="zr-option-card" onclick="setMode('tz','single',this)">
                            <input type="radio" name="tz_mode" value="single">
                            <div class="zr-option-icon"><i class="fas fa-calendar-day"></i></div>
                            <div>
                                <div class="zr-option-title">Submit for a Specific Date</div>
                                <div class="zr-option-desc">Pick a particular day and submit the report for that date only.</div>
                            </div>
                        </label>

                        <label class="zr-option-card" onclick="setMode('tz','backfill',this)" style="grid-column:1/-1">
                            <input type="radio" name="tz_mode" value="backfill">
                            <div class="zr-option-icon"><i class="fas fa-history"></i></div>
                            <div>
                                <div class="zr-option-title">Catch Up on Missing Reports</div>
                                <div class="zr-option-desc">
                                    Were some past reports not submitted? Use this to go back to a specific date and resubmit everything up to today.
                                    <strong>Only use this when instructed — it may take a while to complete.</strong>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Single date --}}
                    <div class="zr-date-section" id="tz-single-field">
                        <label><i class="fas fa-calendar-alt" style="margin-right:5px;color:var(--fit-accent)"></i>Which date?</label>
                        <input type="date" class="zr-date-input" id="tz_date">
                        <div class="zr-date-hint">Select the date you want to submit the report for.</div>
                    </div>

                    {{-- Backfill date --}}
                    <div class="zr-date-section" id="tz-backfill-field">
                        <label><i class="fas fa-calendar-alt" style="margin-right:5px;color:var(--fit-accent)"></i>Start catching up from which date? <span style="font-weight:400;color:var(--fit-muted)">(optional)</span></label>
                        <input type="date" class="zr-date-input" id="tz_backfill">
                        <div class="zr-date-hint">Leave this blank to use the system default starting date. All reports from that date up to today will be submitted.</div>
                    </div>

                    <button class="zr-submit-btn" id="tz-run-btn" onclick="runReport('tz')">
                        <i class="fas fa-paper-plane"></i>
                        <span id="tz-btn-label">Submit Report</span>
                        <i class="fas fa-circle-notch fa-spin zr-spinner"></i>
                    </button>

                    <hr class="zr-divider">

                    <div class="zr-result-header">
                        <span class="zr-result-label"><i class="fas fa-list-alt" style="margin-right:5px"></i>Submission Results</span>
                        <button class="zr-clear-btn" onclick="clearOutput('tz')"><i class="fas fa-times"></i> Clear</button>
                    </div>
                    <div class="zr-result-log" id="tz-output"></div>
                </div>

                {{-- ══ AR Panel ══ --}}
                <div class="zr-panel" id="panel-ar">

                    <div class="zr-info-banner" style="background:#eaf7f0;border-color:#a8dfc5;">
                        <i class="fas fa-info-circle" style="color:#2d6a4f"></i>
                        <div>
                            <strong>FIT Tanzania — Daily Tax Report</strong>
                            Use this page to submit the end-of-day sales report to the Tanzania Revenue Authority (TRA).
                            Choose one of the options below and click <em>Submit Report</em>.
                        </div>
                    </div>

                    <div class="zr-section-label">What would you like to do?</div>

                    <div class="zr-option-grid">
                        <label class="zr-option-card selected" onclick="setMode('ar','auto',this)">
                            <input type="radio" name="ar_mode" value="auto" checked>
                            <div class="zr-option-icon" style="color:#2d6a4f"><i class="fas fa-bolt"></i></div>
                            <div>
                                <div class="zr-option-title">Submit Today's Report</div>
                                <div class="zr-option-desc">Automatically finds and submits the report that is due today. Use this for your daily routine.</div>
                            </div>
                        </label>

                        <label class="zr-option-card" onclick="setMode('ar','single',this)">
                            <input type="radio" name="ar_mode" value="single">
                            <div class="zr-option-icon" style="color:#2d6a4f"><i class="fas fa-calendar-day"></i></div>
                            <div>
                                <div class="zr-option-title">Submit for a Specific Date</div>
                                <div class="zr-option-desc">Pick a particular day and submit the report for that date only.</div>
                            </div>
                        </label>

                        <label class="zr-option-card" onclick="setMode('ar','backfill',this)" style="grid-column:1/-1">
                            <input type="radio" name="ar_mode" value="backfill">
                            <div class="zr-option-icon" style="color:#2d6a4f"><i class="fas fa-history"></i></div>
                            <div>
                                <div class="zr-option-title">Catch Up on Missing Reports</div>
                                <div class="zr-option-desc">
                                    Were some past reports not submitted? Use this to go back to a specific date and resubmit everything up to today.
                                    <strong>Only use this when instructed — it may take a while to complete.</strong>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Single date --}}
                    <div class="zr-date-section" id="ar-single-field">
                        <label><i class="fas fa-calendar-alt" style="margin-right:5px;color:#2d6a4f"></i>Which date?</label>
                        <input type="date" class="zr-date-input" id="ar_date">
                        <div class="zr-date-hint">Select the date you want to submit the report for.</div>
                    </div>

                    {{-- Backfill date --}}
                    <div class="zr-date-section" id="ar-backfill-field">
                        <label><i class="fas fa-calendar-alt" style="margin-right:5px;color:#2d6a4f"></i>Start catching up from which date? <span style="font-weight:400;color:var(--fit-muted)">(optional)</span></label>
                        <input type="date" class="zr-date-input" id="ar_backfill">
                        <div class="zr-date-hint">Leave this blank to use the system default starting date. All reports from that date up to today will be submitted.</div>
                    </div>

                    <button class="zr-submit-btn" id="ar-run-btn" onclick="runReport('ar')" style="background:linear-gradient(135deg,#2d6a4f 0%,#1b4332 100%);box-shadow:0 3px 10px rgba(45,106,79,.3)">
                        <i class="fas fa-paper-plane"></i>
                        <span id="ar-btn-label">Submit Report</span>
                        <i class="fas fa-circle-notch fa-spin zr-spinner"></i>
                    </button>

                    <hr class="zr-divider">

                    <div class="zr-result-header">
                        <span class="zr-result-label"><i class="fas fa-list-alt" style="margin-right:5px"></i>Submission Results</span>
                        <button class="zr-clear-btn" onclick="clearOutput('ar')"><i class="fas fa-times"></i> Clear</button>
                    </div>
                    <div class="zr-result-log" id="ar-output"></div>
                </div>

            </div>{{-- /zr-wrapper --}}
        </div>
    </div>
</div>

<script>
const state = { tz: { mode: 'auto' }, ar: { mode: 'auto' } };

/* Tab switching */
function switchTab(env, el) {
    document.querySelectorAll('.zr-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.zr-panel').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('panel-' + env).classList.add('active');
}

/* Mode selection */
function setMode(env, mode, card) {
    state[env].mode = mode;
    document.querySelectorAll(`#panel-${env} .zr-option-card`).forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    card.querySelector('input').checked = true;

    document.getElementById(`${env}-single-field`).classList.toggle('visible',   mode === 'single');
    document.getElementById(`${env}-backfill-field`).classList.toggle('visible', mode === 'backfill');
}

/* Friendly message translations */
function friendlyMessage(raw) {
    const r = raw.toLowerCase();
    if (r.includes('no zreportdate') || r.includes('skipped'))
        return { text: 'No report is due today. Nothing was submitted.', type: 'warn' };
    if (r.includes('processed successfully') || r.includes('finished'))
        return { text: 'Report submitted successfully ✔', type: 'success' };
    if (r.includes('all reports processed'))
        return { text: 'All reports have been submitted successfully ✔', type: 'success' };
    if (r.includes('backfill mode'))
        return { text: raw.replace('Backfill mode: processing from', 'Submitting reports from'), type: 'info' };
    if (r.includes('auto mode'))
        return { text: raw.replace('Auto mode: processing', 'Submitting report for'), type: 'info' };
    if (r.includes('manual mode'))
        return { text: raw.replace('Manual mode: processing', 'Submitting report for'), type: 'info' };
    if (r.includes('calling api') || r.includes('api response'))
        return null; // hide raw technical lines
    if (r.includes('error') || r.includes('failed') || r.includes('exception'))
        return { text: 'Something went wrong while submitting the report. Please try again or contact IT support.', type: 'error' };
    return { text: raw, type: 'info' };
}

/* Add a row to the result log */
function logRow(env, text, type) {
    const out = document.getElementById(`${env}-output`);
    const ts  = new Date().toLocaleTimeString('en-GB', { hour12: false });

    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warn: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const icon  = icons[type] || icons.info;

    const row = document.createElement('div');
    row.className = `zr-log-row ${type}`;
    row.innerHTML = `
        <span class="zr-log-icon"><i class="fas ${icon}"></i></span>
        <span class="zr-log-msg">${escHtml(text)}</span>
        <span class="zr-log-ts">${ts}</span>`;
    out.appendChild(row);
    out.scrollTop = out.scrollHeight;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function clearOutput(env) {
    document.getElementById(`${env}-output`).innerHTML = '';
}

/* Status pill */
function setStatus(cls, text) {
    const pill = document.getElementById('statusPill');
    pill.className = 'zr-status-pill ' + cls;
    pill.querySelector('.zr-status-text').textContent = text;
}

/* Submit */
function runReport(env) {
    const mode = state[env].mode;
    const btn  = document.getElementById(`${env}-run-btn`);
    const lbl  = document.getElementById(`${env}-btn-label`);

    let date     = null;
    let backfill = null;

    if (mode === 'single') {
        date = document.getElementById(`${env}_date`).value;
        if (!date) { alert('Please select a date before submitting.'); return; }
    } else if (mode === 'backfill') {
        backfill = document.getElementById(`${env}_backfill`).value || '';
    }

    btn.disabled = true;
    btn.classList.add('loading');
    lbl.textContent = 'Submitting…';
    setStatus('running', 'Submitting…');

    logRow(env, mode === 'auto'
        ? 'Checking for today\'s report and submitting…'
        : mode === 'single'
            ? `Submitting report for ${date}…`
            : `Starting catch-up submission${backfill ? ' from ' + backfill : ''}…`, 'info');

    const params = new URLSearchParams({ env, mode });
    if (date)            params.set('date',     date);
    if (backfill !== null) params.set('backfill', backfill);

    fetch(`{{ route('zreport.run') }}?` + params.toString(), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.lines && Array.isArray(data.lines)) {
            data.lines.forEach(l => {
                const mapped = friendlyMessage(l.text);
                if (mapped) logRow(env, mapped.text, mapped.type);
            });
        }

        if (data.success) {
            logRow(env, 'Done! The report has been submitted.', 'success');
            setStatus('success', 'Submitted');
        } else {
            logRow(env, data.error ?? 'Submission failed. Please contact IT support if this keeps happening.', 'error');
            setStatus('error', 'Failed');
        }
    })
    .catch(() => {
        logRow(env, 'Could not reach the server. Please check your connection and try again.', 'error');
        setStatus('error', 'Error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.classList.remove('loading');
        lbl.textContent = 'Submit Report';
    });
}
</script>
@endsection

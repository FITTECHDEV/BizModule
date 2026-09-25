@extends('adminlte::page')

@section('title', 'Create Contract')

@section('content')
    <div class="container-fluid" id="contractCreateContent">

        <div id="ctrListSection">
            {{-- Dashboard stats --}}
            <div class="ctr-stat-grid mb-3">
                <div class="ctr-stat-card ctr-stat-pending" data-status="Pending">
                    <div class="ctr-stat-value">{{ $stats['pending'] }}</div>
                    <div class="ctr-stat-label">Pending</div>
                </div>
                <div class="ctr-stat-card ctr-stat-pending-approval" data-status="Pending Approval">
                    <div class="ctr-stat-value">{{ $stats['pending_approval'] }}</div>
                    <div class="ctr-stat-label">Pending Approval</div>
                </div>
                <div class="ctr-stat-card ctr-stat-authorized" data-status="Authorized">
                    <div class="ctr-stat-value">{{ $stats['authorized'] }}</div>
                    <div class="ctr-stat-label">Authorized</div>
                </div>
                <div class="ctr-stat-card ctr-stat-renewal" data-status="RenewalDue">
                    <div class="ctr-stat-value">{{ $stats['renewal_due'] }}</div>
                    <div class="ctr-stat-label">Renewal Due</div>
                </div>
                <div class="ctr-stat-card ctr-stat-total" data-status="">
                    <div class="ctr-stat-value">{{ $stats['total'] }}</div>
                    <div class="ctr-stat-label">Total</div>
                </div>
            </div>

            <div class="ctr-toolbar d-flex justify-content-between align-items-center mb-3">
                <div class="ctr-page-size-wrap d-flex align-items-center">
                    <label class="ctr-lbl mb-0 mr-2 font-weight-bold" for="ctrPageSize">Show</label>
                    <select class="form-control form-control-sm" id="ctrPageSize" style="width:auto;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary mr-2" id="ctrCopyContractBtn">
                        <i class="fas fa-copy mr-1"></i> Copy
                    </button>
                    <button type="button" class="btn btn-primary" id="ctrNewContractBtn">+ New</button>
                </div>
            </div>
            <table class="table table-sm table-bordered" id="ctrContractsTable">
                <thead>
                    <tr>
                        <th>Contract No</th>
                        <th>Name</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ctrContractsTableBody"></tbody>
            </table>
        </div>{{-- /ctrListSection --}}

        <div class="modal fade" id="ctrContractInfoModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Contract Information</h5>
                        <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="ctr-box-title d-flex align-items-center">
                            <span>Contract Information</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="ctrSaveHeaderBtn">Save
                                Contract
                                Info</button>
                        </div>
                        <div class="ctr-grid">
                            <input type="hidden" id="ctrContractNo" value="">

                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Contract Type</label>
                                <select class="form-control form-control-sm" id="ctrContractType">
                                    <option value="">-- Select --</option>
                                    <option value="Tariff">Tariff</option>
                                    {{-- <option value="Long Term">Long Term</option> --}}
                                </select>
                            </div>

                            <div class="ctr-field ctr-field-span2">
                                <label class="ctr-lbl ctr-required">Contract Name</label>
                                <input type="text" class="form-control form-control-sm" id="ctrContractName">
                            </div>

                            <input type="hidden" id="ctrStatus" value="Draft">
                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Effective From</label>
                                <input type="date" class="form-control form-control-sm" id="ctrEffectiveFrom">
                            </div>
                            <div class="ctr-field">
                                <label class="ctr-lbl ctr-required">Effective To</label>
                                <input type="date" class="form-control form-control-sm" id="ctrEffectiveTo">
                            </div>
                            <div class="ctr-field ctr-field-wide">
                                <label class="ctr-lbl">Remark</label>
                                <input type="text" class="form-control form-control-sm" id="ctrRemark">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Copy Contract modal ─────────────────────────────────────── --}}
        <div class="modal fade" id="ctrCopyContractModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Copy Contract</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Pick a contract to copy. The header, all services, and all
                            charges will be duplicated into a new contract.
                        </p>
                        <div class="form-group mb-0">
                            <label class="ctr-lbl ctr-required">Source Contract</label>
                            <select class="form-control form-control-sm" id="ctrCopySourceSelect">
                                <option value="">-- Select Contract --</option>
                            </select>
                            <small class="form-text text-muted" id="ctrCopySourceHelp" style="display:none;">
                                <span class="text-danger">Failed to load contracts.</span>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="ctrCopyConfirmBtn">
                            <i class="fas fa-copy mr-1"></i> Copy Contract
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- /contractCreateContent --}}
@stop

@section('css')
    <style>
        .ctr-box {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 16px;
            background: #fff;
        }

        .ctr-box-title {
            font-size: .9rem;
            font-weight: 700;
            color: #4B49AC;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .ctr-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px 16px;
        }

        .ctr-field-wide {
            grid-column: span 3;
        }

        .ctr-field-span2 {
            grid-column: span 2;
        }

        .ctr-lbl {
            font-size: .78rem;
            color: #495057;
            margin-bottom: 2px;
            display: block;
        }

        .ctr-required::after {
            content: ' *';
            color: #dc3545;
        }

        .ctr-toolbar {
            background: #f4f4fb;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;

        }

        .ctr-stat-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }

        .ctr-stat-card {
            border-radius: 6px;
            padding: 20px;
            border: none;
            color: #fff;
        }

        .ctr-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: inherit;
        }

        .ctr-stat-label {
            font-size: .82rem;
            color: inherit;
            opacity: .9;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-top: 4px;
        }

        .ctr-stat-pending {
            background: #6c757d;
        }

        .ctr-stat-pending-approval {
            background: #ffc107;
            color: #212529;
            /* amber is too light for white text to stay legible */
        }

        .ctr-stat-authorized {
            background: #28a745;
        }

        .ctr-stat-renewal {
            background: #dc3545;
        }

        .ctr-stat-total {
            background: #4B49AC;
        }

        .ctr-stat-card {
            cursor: pointer;
            transition: box-shadow .15s ease;
        }

        .ctr-stat-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .ctr-stat-card.ctr-stat-active {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, .35) inset;
        }

        @media (max-width: 768px) {
            .ctr-stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@stop

@section('js')
    <script>
        (function() {
            // ── PHP‑passed variables ──────────────────────────────────────────────
            var CONTRACT_ID = @json($contractId);
            var STORE_URL = @json(route('contracts.store', $contractId ?? ''));
            var CONTRACTS_LIST_URL = @json(route('contracts.list'));

            var NEW_DRAFT_URL = @json(route('contracts.new-draft'));

            // ── State ──────────────────────────────────────────────────────────────
            var existing = @json($existing ?? null);
            var state = {
                contract: existing || {
                    contractNo: '',
                    contractType: '',
                    status: 'Draft',
                    effectiveFrom: '',
                    effectiveTo: '',
                    remark: ''
                }
            };
            state.contractSaved = !!existing;

            // ── Local draft autosave (prevents data loss on refresh) ─────────────
            var LOCAL_DRAFT_KEY = 'ctr_draft_' + CONTRACT_ID;

            function collectLocalDraftPayload() {
                return {
                    contractType: document.getElementById('ctrContractType').value,
                    contractName: document.getElementById('ctrContractName').value,
                    effectiveFrom: document.getElementById('ctrEffectiveFrom').value,
                    effectiveTo: document.getElementById('ctrEffectiveTo').value,
                    remark: document.getElementById('ctrRemark').value,
                    contractSaved: state.contractSaved
                };
            }

            function saveLocalDraft() {
                try {
                    localStorage.setItem(LOCAL_DRAFT_KEY, JSON.stringify(collectLocalDraftPayload()));
                } catch (e) {
                    console.error('[Contract] local draft save failed:', e);
                }
            }

            function clearLocalDraft() {
                try {
                    localStorage.removeItem(LOCAL_DRAFT_KEY);
                } catch (e) {
                    console.error('[Contract] local draft clear failed:', e);
                }
            }

            function loadLocalDraft() {
                try {
                    var raw = localStorage.getItem(LOCAL_DRAFT_KEY);
                    return raw ? JSON.parse(raw) : null;
                } catch (e) {
                    console.error('[Contract] local draft load failed:', e);
                    return null;
                }
            }

            function showSection(id) {
                document.getElementById('ctrListSection').style.display = (id === 'ctrListSection') ? 'block' : 'none';
            }

            state.allContracts = [];
            state.activeStatusFilter = 'Pending';
            state.pageSize = 25;

            function isRenewalDue(effectiveTo) {
                if (!effectiveTo) return false;
                var due = new Date(effectiveTo);
                if (isNaN(due.getTime())) return false;
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                due.setHours(0, 0, 0, 0);
                var days = Math.round((due - today) / 86400000);
                return days >= 0 && days <= 10;
            }

            function filterContracts(list, status) {
                if (!status) return list;
                if (status === 'RenewalDue') {
                    return list.filter(function(c) {
                        return isRenewalDue(c.effectiveTo);
                    });
                }
                return list.filter(function(c) {
                    return c.status === status;
                });
            }

            function renderContractsTable(list) {
                var $tbody = $('#ctrContractsTableBody').empty();
                if (!list || !list.length) {
                    $tbody.append(
                        '<tr><td colspan="6" class="text-center text-muted">No contracts found.</td></tr>'
                    );
                    return;
                }
                list.forEach(function(c) {
                    var contractId = c.id || c.contractId || c.Id || '';
                    $tbody.append('<tr data-contract-id="' + contractId + '"><td>' + c.contractNo +
                        '</td><td>' + c.contractName +
                        '</td><td>' +
                        c.effectiveFrom + '</td><td>' + c.effectiveTo + '</td><td>' + c.status +
                        '</td><td>' +
                        '<button type="button" class="btn btn-xs btn-outline-primary ctr-view-contract mr-1" data-id="' +
                        contractId +
                        '" title="View" aria-label="View"><i class="fas fa-eye"></i></button>' +
                        '<button type="button" class="btn btn-xs btn-outline-secondary ctr-edit-contract" data-id="' +
                        contractId +
                        '" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></button>' +
                        '</td></tr>');
                });
            }

            function applyPageSize(list) {
                if (state.pageSize === 'all') return list;
                return list.slice(0, parseInt(state.pageSize, 10));
            }

            function applyStatusFilterAndRender() {
                var filtered = filterContracts(state.allContracts, state.activeStatusFilter);
                renderContractsTable(applyPageSize(filtered));
                $('.ctr-stat-card').removeClass('ctr-stat-active');
                $('.ctr-stat-card[data-status="' + state.activeStatusFilter + '"]').addClass('ctr-stat-active');
            }

            function loadContractsList() {
                $.get(CONTRACTS_LIST_URL, function(res) {
                    if (!res.success) {
                        $('#ctrContractsTableBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Failed to load contracts.</td></tr>'
                        );
                        return;
                    }
                    state.allContracts = res.data || [];
                    applyStatusFilterAndRender();
                }).fail(function() {
                    $('#ctrContractsTableBody').html(
                        '<tr><td colspan="6" class="text-center text-danger">Failed to load contracts.</td></tr>'
                    );
                });
            }

            document.getElementById('ctrNewContractBtn').addEventListener('click', function() {
                var btn = this;
                btn.disabled = true;
                $.get(NEW_DRAFT_URL, function(res) {
                    if (!res.success) {
                        alert('Could not start a new contract. Please try again.');
                        return;
                    }
                    CONTRACT_ID = res.data.contract_id;
                    STORE_URL = res.data.store_url;
                    LOCAL_DRAFT_KEY = 'ctr_draft_' + CONTRACT_ID;

                    state.contract = {
                        contractNo: '',
                        contractType: '',
                        status: 'Draft',
                        effectiveFrom: '',
                        effectiveTo: '',
                        description: '',
                        remark: ''
                    };
                    state.contractSaved = false;

                    document.getElementById('ctrContractType').value = '';
                    document.getElementById('ctrContractName').value = '';
                    document.getElementById('ctrEffectiveFrom').value = '';
                    document.getElementById('ctrEffectiveTo').value = '';
                    document.getElementById('ctrRemark').value = '';

                    $('#ctrContractInfoModal').modal('show');
                }).fail(function() {
                    alert('Could not start a new contract. Please try again.');
                }).always(function() {
                    btn.disabled = false;
                });
            });

            // ── Header save ──────────────────────────────────────────────────────
            function collectHeaderPayload() {
                return {
                    contract_type: document.getElementById('ctrContractType').value,
                    contract_name: document.getElementById('ctrContractName').value.trim(),
                    status: document.getElementById('ctrStatus').value,
                    effective_from: document.getElementById('ctrEffectiveFrom').value,
                    effective_to: document.getElementById('ctrEffectiveTo').value,
                    remark: document.getElementById('ctrRemark').value.trim()
                };
            }

            function validateHeader(payload) {
                return ['contract_type', 'contract_name', 'effective_from', 'effective_to']
                    .filter(function(k) {
                        return !payload[k];
                    });
            }

            function saveDraft(btn, onSuccess) {
                var payload = collectHeaderPayload();
                var missing = validateHeader(payload);
                if (missing.length) {
                    alert('Please fill required Contract fields: ' + missing.join(', '));
                    return;
                }

                var formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                Object.keys(payload).forEach(function(k) {
                    formData.append(k, payload[k]);
                });

                var originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Saving...';

                fetch(STORE_URL, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(function(r) {
                        return r.json().then(function(body) {
                            return {
                                ok: r.ok,
                                status: r.status,
                                body: body
                            };
                        });
                    })
                    .then(function(result) {
                        if (!result.ok || !result.body.success) {
                            var msg = result.body.message || 'Save failed';
                            if (result.body.errors) {
                                var details = Object.values(result.body.errors).flat().join('\n');
                                msg += '\n\n' + details;
                            }
                            throw new Error(msg);
                        }
                        alert(result.body.message || 'Saved.');
                        if (onSuccess) onSuccess(result.body.data); // CHANGED: pass response data through
                    })
                    .catch(function(err) {
                        console.error('[Contract] save failed:', err);
                        alert('Failed to save: ' + err.message);
                    })
                    .finally(function() {
                        btn.disabled = false;
                        btn.textContent = originalText;
                    });
            }

            document.getElementById('ctrSaveHeaderBtn').addEventListener('click', function() {
                var btn = this;
                saveDraft(btn, function(data) { // CHANGED: accept data param
                    var realId = (data && data.contract_id) ? data.contract_id : CONTRACT_ID; // CHANGED
                    CONTRACT_ID = realId; // CHANGED: keep module state in sync
                    clearLocalDraft();
                    window.location.href = '/contracts/' + encodeURIComponent(realId) +
                        '/edit'; // CHANGED
                });
            });

            $('#ctrContractType, #ctrContractName, #ctrEffectiveFrom, #ctrEffectiveTo, #ctrRemark')
                .on('change input', saveLocalDraft);

            $('#ctrContractInfoModal .close').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });

            // ── Copy Contract flow ──────────────────────────────────────────────
            // Reuses the same `/contracts/{id}/duplicate` endpoint the edit page's
            // "Copy Contract" button already calls. On success we redirect straight
            // into the new contract's edit page, mirroring the edit page's flow.

            function loadCopySourceOptions() {
                var $sel = $('#ctrCopySourceSelect');
                var $help = $('#ctrCopySourceHelp');
                $sel.empty().append('<option value="">-- Select Contract --</option>');
                $help.hide();

                function fill(list) {
                    if (!list || !list.length) {
                        $sel.append('<option value="" disabled>No contracts available</option>');
                        return;
                    }
                    list.forEach(function(c) {
                        var id = c.id || c.contractId || c.Id || '';
                        var label = (c.contractNo || '') + ' — ' + (c.contractName || '');
                        $sel.append('<option value="' + id + '">' + label + '</option>');
                    });
                }

                // state.allContracts is already populated on page load — reuse it
                // instead of an extra round-trip. Fall back to a fetch only if the
                // list hasn't arrived yet (e.g. user clicks fast after page load).
                if (state.allContracts && state.allContracts.length) {
                    fill(state.allContracts);
                    return;
                }

                $.get(CONTRACTS_LIST_URL, function(res) {
                    if (!res.success) {
                        $help.show();
                        return;
                    }
                    state.allContracts = res.data || [];
                    fill(state.allContracts);
                }).fail(function() {
                    $help.show();
                });
            }

            $('#ctrCopyContractBtn').on('click', function() {
                loadCopySourceOptions();
                $('#ctrCopyContractModal').modal('show');
            });

            $('#ctrCopyConfirmBtn').on('click', function() {
                var sourceId = $('#ctrCopySourceSelect').val();
                if (!sourceId) {
                    alert('Please select a contract to copy.');
                    return;
                }

                var $btn = $(this).prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Copying...'
                );

                $.ajax({
                    url: '/contracts/' + encodeURIComponent(sourceId) + '/duplicate',
                    type: 'POST',
                    data: {
                        _token: document.querySelector('meta[name="csrf-token"]').content,
                        CopyContractID: sourceId,
                        Action: 'Copy'
                    },
                    dataType: 'json'
                }).done(function(res) {
                    if (!res.success) {
                        alert('Failed to copy contract: ' + (res.message || 'Unknown error'));
                        return;
                    }

                    $('#ctrCopyContractModal').modal('hide');

                    if (res.data && res.data.newContractId) {
                        window.location.href = '/contracts/' +
                            encodeURIComponent(res.data.newContractId) + '/edit';
                    } else {
                        // Fallback — refresh the list so the new contract appears
                        loadContractsList();
                    }
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Copy failed.';
                    alert('Failed to copy contract: ' + msg);
                }).always(function() {
                    $btn.prop('disabled', false).html(
                        '<i class="fas fa-copy mr-1"></i> Copy Contract'
                    );
                });
            });

            // Clear the dropdown when the modal closes so re-opening always
            // starts from a clean state (fresh contract list, nothing pre-selected).
            $('#ctrCopyContractModal').on('hidden.bs.modal', function() {
                $('#ctrCopySourceSelect').val('');
                $('#ctrCopySourceHelp').hide();
            });

            // ── View / Edit / Delete Contract Click Handlers ──────────────────────
            $(document).on('click', '.ctr-view-contract', function() {
                var contractId = $(this).data('id');
                if (contractId) {
                    window.location.href = '/contracts/' + contractId + '/view';
                }
            });

            $(document).on('click', '.ctr-edit-contract', function() {
                var contractId = $(this).data('id');
                if (!contractId) return;
                window.location.href = '/contracts/' + encodeURIComponent(contractId) + '/edit';
            });
            
            $(document).on('click', '.ctr-stat-card', function() {
                state.activeStatusFilter = $(this).data('status') || '';
                applyStatusFilterAndRender();
            });

            $(document).on('change', '#ctrPageSize', function() {
                state.pageSize = $(this).val();
                applyStatusFilterAndRender();
            });

            // ── Hydrate from existing draft ──────────────────────────────────────
            var localDraft = loadLocalDraft();

            if (existing) {
                document.getElementById('ctrContractNo').value = existing.contract_no || '';
                document.getElementById('ctrContractType').value = existing.contract_type || '';
                document.getElementById('ctrContractName').value = existing.contract_name || '';
                document.getElementById('ctrStatus').value = existing.status || 'Draft';
                document.getElementById('ctrEffectiveFrom').value = existing.effective_from || '';
                document.getElementById('ctrEffectiveTo').value = existing.effective_to || '';
                document.getElementById('ctrRemark').value = existing.remark || '';
            }

            if (localDraft) {
                document.getElementById('ctrContractType').value = localDraft.contractType || '';
                document.getElementById('ctrContractName').value = localDraft.contractName || '';
                document.getElementById('ctrEffectiveFrom').value = localDraft.effectiveFrom || '';
                document.getElementById('ctrEffectiveTo').value = localDraft.effectiveTo || '';
                document.getElementById('ctrRemark').value = localDraft.remark || '';
                state.contractSaved = !!localDraft.contractSaved;
            }

            showSection('ctrListSection');
            loadContractsList();

        })();
    </script>
@stop

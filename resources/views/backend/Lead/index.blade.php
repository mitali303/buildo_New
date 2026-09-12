@extends('backend.partials.master')

@section('title')
    Leads
@endsection

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
         {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h4 mb-0">Leads</h1>
                <p class="text-muted small mb-0">Track and manage every lead in one place</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('CreateLead.create') }}" class="btn btn-primary btn-sm">
                        <i class="align-middle" data-feather="plus" style="width:16px;height:16px;"></i> Add Lead
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="bulk-delete-btn">
                        <i class="align-middle" data-feather="trash-2" style="width:16px;height:16px;"></i> Delete
                    </button>
                <!-- @if(hasPermission('create_CreateLead'))
                    <a href="{{ route('CreateLead.create') }}" class="btn btn-primary btn-sm">
                        <i class="align-middle" data-feather="plus" style="width:16px;height:16px;"></i> Add Lead
                    </a>
                @endif
                @if(hasPermission('delete_CreateLead'))
                    <button type="button" class="btn btn-outline-danger btn-sm" id="bulk-delete-btn">
                        <i class="align-middle" data-feather="trash-2" style="width:16px;height:16px;"></i> Delete
                    </button>
                @endif -->
            </div>
        </div>
        {{-- Stage pipeline tabs --}}
            <!-- @php
                // Lightweight keyword → icon match so the pills get a sensible icon
                // without hardcoding against specific stage IDs.
                $iconFor = function ($label) {
                $label = strtolower(trim($label));

                return match (true) {

                    str_contains($label, 'new')             => 'plus-circle',
                    str_contains($label, 'save')            => 'save',
                    str_contains($label, 'quotation')       => 'file-text',
                    str_contains($label, 'quote')           => 'file-text',
                    str_contains($label, 'interested')      => 'thumbs-up',
                    str_contains($label, 'review')          => 'eye',

                    str_contains($label, 'convert')         => 'check-square',
                    str_contains($label, 'unqualified')     => 'user-x',

                    str_contains($label, 'contact')         => 'phone-call',
                    str_contains($label, 'visit')           => 'map-pin',
                    str_contains($label, 'reply')           => 'clock',
                    str_contains($label, 'wait')            => 'clock',
                    str_contains($label, 'negot')           => 'message-circle',
                    str_contains($label, 'demo')            => 'monitor',
                    str_contains($label, 'po')              => 'shopping-cart',
                    str_contains($label, 'requirement')     => 'check-circle',
                    str_contains($label, 'reject')          => 'x-circle',

                    default => 'circle',
                };
            };
            @endphp -->
        <div class="stage-tabs mb-3" id="stage-tabs">
            <button type="button" class="stage-tab active" data-stage="all" style="--tab-color:#0da5aa">
                <i class="align-middle" data-feather="layers"></i> All <span class="stage-count">{{ $counts['all'] }}</span>
            </button>
            @foreach($stageLabels as $value => $label)
                <button type="button" class="stage-tab" data-stage="{{ $value }}" style="--tab-color: {{ $stageColors[$value] ?? '#6c757d' }};">
                    <i class="align-middle" data-feather="{{ $iconFor($label) }}"></i>
                    {{ strtoupper($label) }}
                    <span class="stage-count" style="background: {{ $stageColors[$value] ?? '#6c757d' }};">{{ $counts[$value] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
        {{-- Toolbar: search, date filters, view toggle --}}
        <div class="toolbar mb-3">
            <div class="toolbar-left">
                <div class="search-box">
                    <i class="align-middle" data-feather="search"></i>
                    <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Search company, mobile, city...">
                </div>
                <input type="date" id="filter-from" class="form-control form-control-sm" title="From date">
                <input type="date" id="filter-to" class="form-control form-control-sm" title="To date">
                <button type="button" id="reset-filters-btn" class="btn btn-sm btn-light" title="Reset filters">
                    <i class="align-middle" data-feather="x" style="width:14px;height:14px;"></i>
                </button>
            </div>
            <div class="btn-group btn-group-sm view-toggle" role="group">
                <button class="btn btn-outline-secondary active" id="view-toggle-card" title="Card view">
                    <i class="align-middle" data-feather="grid" style="width:14px;height:14px;"></i>
                </button>
                <button class="btn btn-outline-secondary" id="view-toggle-table" title="Table view">
                    <i class="align-middle" data-feather="list" style="width:14px;height:14px;"></i>
                </button>
            </div>
        </div>
        {{-- Results --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div id="leads-wrap">
                    <div id="leads-card-view" class="leads-grid"></div>
                        <div id="leads-table-view" class="table-responsive d-none">
                            <table class="table table-sm align-middle mb-0 leads-table">
                                <thead>
                                    <tr>
                                        <th style="width:32px"><input type="checkbox" id="select-all-rows"></th>
                                        <th>Company</th>
                                        <th>Mobile</th>
                                        <th>City</th>
                                        <th>Type</th>
                                        <th>Stage</th>
                                        <th>Source</th>
                                        <th>Assigned To</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="leads-table-body"></tbody>
                            </table>
                     </div>
                    <div id="no-data-message" class="text-center text-muted py-5 d-none">
                        <i class="align-middle" data-feather="inbox" style="width:36px;height:36px;opacity:.4;"></i>
                        <p class="mt-2 mb-1">No leads found</p>
                        <p class="small">Try adjusting your search or filters.</p>
                    </div>
                    <div id="loading-message" class="text-center text-muted py-5">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="small mt-2 mb-0">Loading leads...</p>
                    </div>
                </div>
                <nav id="pagination-container" class="mt-3 d-none">
                    <ul class="pagination pagination-sm justify-content-center mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</main>
<form id="bulk-delete-form" action="{{ route('CreateLead.bulkDelete') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    <div id="bulk-delete-ids"></div>
</form>

<style>
/* ---------- stage tabs (pill style, matches reference screenshot) ---------- */
.stage-tabs {
    display: flex; flex-wrap: wrap; gap: .5rem;
    position: relative; padding-top: .6rem;
}
.stage-tabs::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 4px;
    background: linear-gradient(90deg, #fd7e14 0%, #e83e8c 33%, #8e44ad 66%, #20c997 100%);
}
.stage-tab {
    display: inline-flex; align-items: center; gap: .4rem;
    border: 1px solid #e9ecef; background: #fff; color: #495057;
    border-radius: 30px; padding: .4rem .9rem; font-size: .74rem; font-weight: 600;
    letter-spacing: .01em; line-height: 1.2; cursor: pointer; transition: all .15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
}
.stage-tab svg { width: 14px; height: 14px; color: var(--tab-color, #6c757d); stroke-width: 2.2; flex-shrink: 0; }
.stage-tab .stage-count {
    background: var(--tab-color, #6c757d); color: #fff; border-radius: 50%;
    min-width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center;
    font-size: .68rem; font-weight: 700; padding: 0 .3rem;
}
.stage-tab:hover { border-color: var(--tab-color, #0da5aa); transform: translateY(-1px); }
.stage-tab.active {
    background: var(--tab-color, #0da5aa); border-color: var(--tab-color, #0da5aa); color: #fff;
}
.stage-tab.active svg { color: #fff; }
.stage-tab.active .stage-count { background: rgba(255,255,255,.3); color: #fff; }

.toolbar { display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap; }
.toolbar-left { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
.search-box { position: relative; }
.search-box svg { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #adb5bd; }
.search-box input { padding-left: 30px; width: 220px; }
.toolbar input[type="date"] { width: 145px; }
.view-toggle .btn.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }

/* ---------- card view ---------- */
.leads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: .65rem;
}
.lead-card {
    border: 1px solid #e9ecef; border-radius: 8px; background: #fff;
    padding: .75rem .85rem; border-left: 3px solid var(--stage-color, #0d6efd);
    transition: box-shadow .15s ease, transform .15s ease;
}
.lead-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); transform: translateY(-2px); }
.lead-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; margin-bottom: .5rem; }
.lead-card-title { font-size: .9rem; font-weight: 600; color: #1a1a2e; margin: 0; line-height: 1.3; }
.lead-card-title a { color: inherit; text-decoration: none; }
.lead-card-title a:hover { color: #0d6efd; }
.lead-card-badge { font-size: .68rem; padding: .25rem .55rem; white-space: nowrap; }
.lead-card-meta { display: grid; grid-template-columns: 1fr 1fr; gap: .3rem .5rem; font-size: .76rem; color: #495057; margin-bottom: .5rem; }
.lead-card-meta span { color: #adb5bd; display: block; font-size: .65rem; text-transform: uppercase; letter-spacing: .03em; }
.lead-card-foot { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f3f5; padding-top: .5rem; }
.lead-card-foot .form-check-input { margin: 0; }
.lead-card-foot .actions a { margin-left: .55rem; color: #6c757d; }
.lead-card-foot .actions a:hover { color: #0d6efd; }

/* ---------- table view ---------- */
.leads-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #868e96; border-top: none; }
.leads-table td { font-size: .82rem; vertical-align: middle; }
.leads-table .actions a { margin-left: .6rem; color: #6c757d; }
.leads-table .actions a:hover { color: #0d6efd; }

@media (max-width: 575px) {
    .search-box input { width: 100%; }
    .toolbar-left { width: 100%; }
    .toolbar input[type="date"] { flex: 1; width: auto; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let currentStage = 'all';
    let currentPage = 1;
    let viewMode = 'card';
    let searchTimer = null;
    const perPage = 12;

    const stageColorMap = @json($stageColors);
    const typeColorMap = {1:'danger',2:'warning',3:'info'};

    const cardView = document.getElementById('leads-card-view');
    const tableBody = document.getElementById('leads-table-body');
    const tableView = document.getElementById('leads-table-view');
    const noData = document.getElementById('no-data-message');
    const loading = document.getElementById('loading-message');

    function loadLeads(page = 1) {
        loading.classList.remove('d-none');
        noData.classList.add('d-none');

        fetch(`{{ route('CreateLead') }}?` + new URLSearchParams({
            stage: currentStage,
            from: document.getElementById('filter-from').value,
            to: document.getElementById('filter-to').value,
            search: document.getElementById('filter-search').value,
            page: page,
            limit: perPage,
        }), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(renderLeads)
            .catch(() => { loading.classList.add('d-none'); });
    }

    function renderLeads(res) {
        loading.classList.add('d-none');
        const rows = res.data || [];

        if (rows.length === 0) {
            cardView.innerHTML = '';
            tableBody.innerHTML = '';
            noData.classList.remove('d-none');
            updatePagination(res);
            return;
        }

        cardView.innerHTML = rows.map(cardTemplate).join('');
        tableBody.innerHTML = rows.map(rowTemplate).join('');
        feather.replace();
        updatePagination(res);
    }

    function cardTemplate(l) {
        const color = stageColorMap[l.lead_stage_id] || '#6c757d';
        const typeColor = typeColorMap[l.lead_type_id] || 'secondary';
        return `
        <div class="lead-card" style="--stage-color:${color}">
            <div class="lead-card-top">
                <h6 class="lead-card-title"><a href="${l.view_url}">${l.company_name || 'N/A'}</a></h6>
                <span class="badge lead-card-badge" style="background:${color}">${l.lead_stage}</span>
            </div>
            <div class="lead-card-meta">
                <div><span>Mobile</span><a href="tel:${l.mobile_1}">${l.mobile_1 || '-'}</a></div>
                <div><span>City</span>${l.city}</div>
                <div><span>Type</span><span class="badge bg-${typeColor}-subtle text-${typeColor}-emphasis">${l.lead_type}</span></div>
                <div><span>Source</span>${l.lead_source_name}</div>
                <div><span>Assigned To</span>${l.assign_to_name}</div>
                <div><span>Created</span>${l.created_at || '-'}</div>
            </div>
            <div class="lead-card-foot">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input lead-checkbox" value="${l.id}">
                </div>
                <div class="actions">${l.actions}</div>
            </div>
        </div>`;
    }

    function rowTemplate(l) {
        const color = stageColorMap[l.lead_stage_id] || '#6c757d';
        const typeColor = typeColorMap[l.lead_type_id] || 'secondary';
        return `
        <tr>
            <td><input type="checkbox" class="form-check-input lead-checkbox" value="${l.id}"></td>
            <td><a href="${l.view_url}" class="text-body fw-semibold text-decoration-none">${l.company_name || 'N/A'}</a></td>
            <td><a href="tel:${l.mobile_1}">${l.mobile_1 || '-'}</a></td>
            <td>${l.city}</td>
            <td><span class="badge bg-${typeColor}-subtle text-${typeColor}-emphasis">${l.lead_type}</span></td>
            <td><span class="badge" style="background:${color}">${l.lead_stage}</span></td>
            <td>${l.lead_source_name}</td>
            <td>${l.assign_to_name}</td>
            <td class="text-nowrap">${l.created_at || '-'}</td>
            <td class="text-end actions">${l.actions}</td>
        </tr>`;
    }

    function updatePagination(res) {
        const container = document.getElementById('pagination-container');
        const pag = document.getElementById('pagination');
        const totalPages = res.last_page || 1;

        if (totalPages <= 1) { container.classList.add('d-none'); pag.innerHTML = ''; return; }

        container.classList.remove('d-none');
        let html = '';
        const cur = res.current_page || 1;

        html += pageBtn('&laquo;', Math.max(1, cur - 1), cur === 1);
        for (let i = Math.max(1, cur - 2); i <= Math.min(totalPages, cur + 2); i++) {
            html += `<li class="page-item ${i === cur ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += pageBtn('&raquo;', Math.min(totalPages, cur + 1), cur === totalPages);

        pag.innerHTML = html;
    }

    function pageBtn(label, page, disabled) {
        return `<li class="page-item ${disabled ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
    }

    document.getElementById('pagination').addEventListener('click', function (e) {
        const link = e.target.closest('a[data-page]');
        if (!link) return;
        e.preventDefault();
        currentPage = parseInt(link.dataset.page);
        loadLeads(currentPage);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Stage tabs
    document.querySelectorAll('.stage-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.stage-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentStage = btn.dataset.stage;
            currentPage = 1;
            loadLeads(1);
        });
    });

    // Search (debounced) + date filters
    document.getElementById('filter-search').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { currentPage = 1; loadLeads(1); }, 400);
    });
    ['filter-from', 'filter-to'].forEach(id => {
        document.getElementById(id).addEventListener('change', () => { currentPage = 1; loadLeads(1); });
    });
    document.getElementById('reset-filters-btn').addEventListener('click', function () {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-from').value = '';
        document.getElementById('filter-to').value = '';
        currentPage = 1;
        loadLeads(1);
    });

    // View toggle
    document.getElementById('view-toggle-card').addEventListener('click', function () {
        viewMode = 'card';
        this.classList.add('active');
        document.getElementById('view-toggle-table').classList.remove('active');
        cardView.classList.remove('d-none');
        tableView.classList.add('d-none');
    });
    document.getElementById('view-toggle-table').addEventListener('click', function () {
        viewMode = 'table';
        this.classList.add('active');
        document.getElementById('view-toggle-card').classList.remove('active');
        tableView.classList.remove('d-none');
        cardView.classList.add('d-none');
    });

    // Bulk delete (works across both views, checkboxes share the .lead-checkbox class)
    const bulkBtn = document.getElementById('bulk-delete-btn');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function () {
            const ids = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) { alert('Please select at least one lead to delete.'); return; }
            if (!confirm(`Delete ${ids.length} selected lead(s)? This cannot be undone.`)) return;

            const container = document.getElementById('bulk-delete-ids');
            container.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });
            document.getElementById('bulk-delete-form').submit();
        });
    }

    // Select-all (delegated, works for whichever view is active)
    document.addEventListener('change', function (e) {
        if (e.target.id === 'select-all-rows') {
            document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = e.target.checked);
        }
    });

    feather.replace();
    loadLeads(1);
});
</script>
@endsection
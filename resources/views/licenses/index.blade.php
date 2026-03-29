@extends('layouts.app')
@section('title', 'License Management')
@section('breadcrumb', 'Administration / License Management')



@section('content')
    <style>
        .hover-bg-light:hover {
            background-color: rgba(32, 107, 196, 0.03) !important;
            transition: background-color 0.1s ease-in-out;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0" style="color:#1a3c6b">License Management</h2>
            <small class="text-muted">Manage petrotechnical software licenses</small>
        </div>
        @can('create', \App\Models\License::class)
            <button class="btn" style="background:#1a3c6b;color:#fff" data-bs-toggle="modal"
                data-bs-target="#createVendorModal">+ Add Vendor</button>
        @endcan
    </div>

    @php
        // Fetch all vendors to calculate stats
        $allVendors = \App\Models\Vendor::with('server')->get();
        $totalVendors = $allVendors->count();

        // Reverting: UP = status 'enable', DOWN = status 'disable'
        $upVendors = $allVendors->where('status', 'enable')->count();
        $downVendors = $allVendors->where('status', 'disable')->count();

        $upPercentage = $totalVendors > 0 ? ($upVendors / $totalVendors) * 100 : 0;
        $downPercentage = $totalVendors > 0 ? ($downVendors / $totalVendors) * 100 : 0;
    @endphp

    {{-- Server Status Infographic Card --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="overflow: hidden; background: #fff;">
                <div class="row g-0">
                    <div class="col-md-3 border-end">
                        <div class="card-body text-center py-4">
                            <div class="text-muted mb-2 text-uppercase fw-bold"
                                style="font-size: 0.65rem; letter-spacing: 0.05em;">Server Status</div>
                            <div class="h1 mb-1 fw-bold" style="color: #1a3c6b;">{{ number_format($upPercentage, 0) }}%
                            </div>
                            <div class="badge bg-success-lt text-success px-2 py-1">OPERATIONAL</div>
                        </div>
                    </div>
                    <div class="col-md-6 border-end">
                        <div class="card-body py-4 h-100 d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-dark small"><i class="fas fa-heartbeat me-1 text-primary"></i>
                                    Infrastructure Health Distribution</span>
                                <span class="text-muted small">{{ $upVendors }} / {{ $totalVendors }} Vendors UP</span>
                            </div>
                            <div class="progress mb-3" style="height: 12px; border-radius: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-success" style="width: {{ $upPercentage }}%"
                                    data-bs-toggle="tooltip" title="UP: {{ $upVendors }} Vendors"></div>
                                <div class="progress-bar bg-danger" style="width: {{ $downPercentage }}%"
                                    data-bs-toggle="tooltip" title="DOWN: {{ $downVendors }} Vendors"></div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center small text-muted">
                                        <span
                                            class="status-dot status-dot-animated bg-success me-2 border-0 shadow-none"></span>
                                        <span>Online: {{ number_format($upPercentage, 1) }}%</span>
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="d-flex align-items-center justify-content-end small text-muted">
                                        <span class="status-dot bg-danger me-2 border-0 shadow-none"></span>
                                        <span>Offline: {{ number_format($downPercentage, 1) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 bg-light-lt">
                        <div class="card-body text-center py-4 h-100 d-flex flex-column justify-content-center">
                            <div class="text-muted mb-1 text-uppercase fw-bold"
                                style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Assets</div>
                            <div class="h2 mb-0 fw-bold text-dark">{{ $totalVendors }}</div>
                            <div class="small text-muted">Registered Vendors</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Vendor Modal --}}
    <div class="modal modal-blur fade" id="createVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.vendors.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Vendor Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. lgcx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">License Server</label>
                            <select class="form-select" name="license_server_id" required>
                                <option value="">— Select Server —</option>
                                @foreach(\App\Models\LicenseServer::all() as $server)
                                    <option value="{{ $server->id }}">{{ $server->server_name }} ({{ $server->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Port</label>
                            <input type="text" class="form-control" name="port" required placeholder="e.g. 27000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="enable">Active (Enable)</option>
                                <option value="disable">Disabled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Expiry Warnings --}}
    @if($expiring->isNotEmpty())
        <div class="alert alert-warning mb-4">
            <strong>⚠ Expiring within 30 days:</strong>
            @foreach($expiring as $l)
                <span class="badge bg-warning-lt text-warning ms-1">{{ $l->license_name }}
                    ({{ $l->expiry_date->format('d M Y') }})</span>
            @endforeach
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px"
                    placeholder="Search vendor / app..." value="{{ request('search') }}">
                <select name="status" class="form-select form-select-sm" style="max-width:150px">
                    <option value="">All Status</option>
                    <option value="enable" {{ request('status') == 'enable' ? 'selected' : '' }}>Active</option>
                    <option value="disable" {{ request('status') == 'disable' ? 'selected' : '' }}>Disabled</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('admin.licenses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead style="background:#f4f7fb">
                    <tr>
                        <th class="w-1">No.</th>
                        <th>Vendor Name</th>
                        <th>Server</th>
                        <th class="text-center">Total Feature</th>
                        <th>Status (UP/DOWN)</th>
                        <th>Last Update From Server</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $v)
                        @php
                            $isOnline = $v->status == 'enable' &&
                                $v->last_updated &&
                                $v->last_updated->diffInMinutes(now()) <= 10;
                            $detailUrl = route('admin.licenses.vendor', $v->id);
                        @endphp
                        <tr onclick="window.location='{{ $detailUrl }}'" style="cursor: pointer;" class="hover-bg-light">
                            <td class="text-muted">{{ $loop->iteration + ($vendors->firstItem() - 1) }}</td>
                            <td>
                                <div class="d-inline-flex align-items-center">
                                    @if($v->status == 'enable')
                                        <span class="status-dot status-dot-animated bg-success me-2 border-0"></span>
                                    @else
                                        <span class="status-dot bg-danger me-2 border-0"></span>
                                    @endif
                                    <strong class="text-primary">{{ $v->name }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <code
                                        id="server-{{ $v->id }}">{{ $v->port ?? '27000' }}&#64;{{ optional($v->server)->server_name }}</code>
                                    <button class="btn btn-icon btn-ghost-primary btn-sm ms-2 border-0"
                                        onclick="event.stopPropagation(); copyToClipboard('server-{{ $v->id }}', this)" title="Copy Server Info">
                                        <i class="fas fa-copy" style="font-size: 0.7rem;"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-blue-lt text-blue">{{ $v->features_count }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge {{ $v->status == 'enable' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }} border-0">
                                    @if($v->status != 'enable')
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                    @endif
                                    {{ $v->status == 'enable' ? 'UP' : 'DOWN' }}
                                </span>
                                @if($v->status == 'enable' && !$isOnline)
                                    <i class="fas fa-clock text-warning ms-1" data-bs-toggle="tooltip"
                                        title="Stale connection: last update was > 10 mins ago"></i>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">
                                    {{ $v->last_updated ? $v->last_updated->format('d M Y H:i') : 'Never' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#editVendorModal"
                                    onclick="event.stopPropagation(); openEditVendorModal({{ $v->id }}, '{{ addslashes($v->name) }}', '{{ $v->license_server_id }}', '{{ $v->port }}', '{{ $v->status }}', '{{ addslashes($v->description ?? '') }}')">
                                    Edit
                                </button>
                                <a href="{{ route('admin.licenses.vendor', $v->id) }}"
                                    class="btn btn-sm btn-outline-primary ms-1" onclick="event.stopPropagation()">
                                    View Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No vendors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div class="card-footer py-2">{{ $vendors->withQueryString()->links() }}</div>
        @endif
        <div class="card-footer bg-light-lt py-2">
            <div class="d-flex flex-wrap align-items-center gap-4 small text-dark">
                <span class="fw-bold"><i class="fas fa-info-circle me-1 opacity-50"></i> Status Legend:</span>
                <span class="d-flex align-items-center">
                    <span class="status-dot status-dot-animated bg-success me-2 border-0"
                        style="width: 10px; height: 10px;"></span>
                    <strong>UP</strong> = Vendor is enabled and server has been connected.
                </span>
                <span class="d-flex align-items-center text-muted">
                    <i class="fas fa-clock text-warning me-1"></i>
                    = Communicated > 10 mins ago (STALE).
                </span>
                <span class="d-flex align-items-center">
                    <span class="status-dot bg-danger me-2 border-0" style="width: 10px; height: 10px;"></span>
                    <strong>DOWN</strong> = <i class="fas fa-exclamation-triangle text-danger me-1"></i> Vendor is disabled
                    or manually turned off.
                </span>
            </div>
        </div>
    </div>

    {{-- Edit Vendor Modal --}}
    <div class="modal modal-blur fade" id="editVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="editVendorForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Vendor Name</label>
                            <input type="text" class="form-control" name="name" id="edit_vendor_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">License Server</label>
                            <select class="form-select" name="license_server_id" id="edit_vendor_server" required>
                                <option value="">— Select Server —</option>
                                @foreach(\App\Models\LicenseServer::all() as $server)
                                    <option value="{{ $server->id }}">{{ $server->server_name }} ({{ $server->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Port</label>
                            <input type="text" class="form-control" name="port" id="edit_vendor_port" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" name="status" id="edit_vendor_status" required>
                                <option value="enable">Active (Enable)</option>
                                <option value="disable">Disabled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" id="edit_vendor_description"
                                rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditVendorModal(id, name, serverId, port, status, description) {
            document.getElementById('editVendorForm').action = '/admin/vendors/' + id;
            document.getElementById('edit_vendor_name').value = name;
            document.getElementById('edit_vendor_server').value = serverId;
            document.getElementById('edit_vendor_port').value = port;
            document.getElementById('edit_vendor_status').value = status;
            document.getElementById('edit_vendor_description').value = description;
        }

        function copyToClipboard(id, btn) {
            const text = document.getElementById(id).innerText;
            navigator.clipboard.writeText(text).then(() => {
                const icon = btn.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check text-success';
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            });
        }
    </script>
@endsection
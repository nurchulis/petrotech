@extends('layouts.app')
@section('title', 'License Management')
@section('breadcrumb', 'Administration / License Management')



@section('content')
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
                        <tr>
                            <td class="text-muted">{{ $loop->iteration + ($vendors->firstItem() - 1) }}</td>
                            <td>
                                <div class="d-inline-flex align-items-center">
                                    @if($v->status == 'enable')
                                        <span class="status-dot status-dot-animated bg-success me-2"></span>
                                    @else
                                        <span class="status-dot bg-danger me-2"></span>
                                    @endif
                                    <strong class="text-primary">{{ $v->name }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <code
                                        id="server-{{ $v->id }}">{{ $v->port ?? '27000' }}&#64;{{ optional($v->server)->server_name }}</code>
                                    <button class="btn btn-icon btn-ghost-primary btn-sm ms-2 border-0"
                                        onclick="copyToClipboard('server-{{ $v->id }}', this)" title="Copy Server Info">
                                        <i class="fas fa-copy" style="font-size: 0.7rem;"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-blue-lt text-blue">{{ $v->features_count }}</span>
                            </td>
                            <td>
                                <span
                                    class="badge {{ $v->status == 'enable' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">{{ $v->status == 'enable' ? 'UP' : 'DOWN' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">
                                    {{ $v->last_updated ? $v->last_updated->format('d M Y H:i') : 'Never' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#editVendorModal"
                                    onclick="openEditVendorModal({{ $v->id }}, '{{ addslashes($v->name) }}', '{{ $v->license_server_id }}', '{{ $v->port }}', '{{ $v->status }}', '{{ addslashes($v->description ?? '') }}')">
                                    Edit
                                </button>
                                <a href="{{ route('admin.licenses.vendor', $v->id) }}"
                                    class="btn btn-sm btn-outline-primary ms-1">
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
            <div class="card-footer">{{ $vendors->withQueryString()->links() }}</div>
        @endif
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
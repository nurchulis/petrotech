@extends('layouts.app')
@section('title', 'Vendor Detail: ' . $vendor->name)
@section('breadcrumb', 'Administration / License Management / ' . $vendor->name)

@php
    $activeTab = session('active_tab', 'features');
@endphp

@push('styles')
    <style>
        @keyframes syncFlow {
            0% {
                left: -15px;
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            80% {
                opacity: 1;
            }

            100% {
                left: 100%;
                opacity: 0;
            }
        }

        .sync-packet {
            animation: syncFlow 1.5s infinite linear;
        }

        .modal-blur {
            backdrop-filter: blur(4px);
        }

        .hover-bg-light:hover {
            background-color: rgba(32, 107, 196, 0.03) !important;
            transition: background-color 0.2s ease-in-out;
        }

        .transition-transform {
            transition: transform 0.3s ease-in-out;
        }

        [aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }

        .bg-light-lt {
            background-color: #f8fafc !important;
        }

        .border-dashed {
            border-style: dashed !important;
        }
    </style>
@endpush

@section('content')
    <div class="row row-cards">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center w-full">
                        <div>
                            <h2 class="card-title h3 mb-1 text-primary fw-bold">Vendor: {{ $vendor->name }}</h2>
                            <p class="text-dark small mb-0">Server: {{ $server?->hostname }} ({{ $server?->ip_address }})
                                <span class="badge bg-success-lt ms-1">UP</span>
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @can('create', \App\Models\License::class)
                            <a href="{{ route('admin.licenses.create', ['vendor_id' => $vendor->id, 'server_id' => $server?->id]) }}" class="btn btn-primary btn-sm">
                                + Add License
                            </a>
                            @endcan
                            <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chevron-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-header border-bottom p-0">
                    <ul class="nav nav-tabs" data-bs-toggle="tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a href="#tabs-features" class="nav-link {{ $activeTab == 'features' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'features' ? 'true' : 'false' }}"
                                role="tab">Feature List (Licenses)</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tabs-logs" class="nav-link {{ $activeTab == 'logs' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'logs' ? 'true' : 'false' }}"
                                role="tab">User Usage (Logs)</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="#tabs-access" class="nav-link {{ $activeTab == 'access' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'access' ? 'true' : 'false' }}"
                                role="tab">User Access Management</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    {{-- Feature List Tab --}}
                    <div class="tab-pane {{ $activeTab == 'features' ? 'active show' : '' }}" id="tabs-features"
                        role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover card-table">
                                <thead>
                                    <tr class="text-dark">
                                        <th class="fw-bold">Feature Name</th>
                                        <th class="fw-bold">Capacity</th>
                                        <th class="fw-bold">In Use</th>
                                        <th class="fw-bold">Expiry</th>
                                        <th class="fw-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($features as $f)
                                        <tr class="cursor-pointer hover-bg-light" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#details-{{ $f->id }}"
                                            aria-expanded="false"
                                            title="Click to view active users">
                                            <td class=" text-dark">{{ $f->license_name }}</td>
                                            <!-- <td><span class="text-muted small">{{ $f->application_name }}</span></td> -->
                                            <td>{{ $f->total_seats }} seats</td>
                                            <td style="width: 250px;">
                                                @php
                                                    $usage = $f->used_seats;
                                                    $percent = $f->total_seats > 0 ? ($usage / $f->total_seats) * 100 : 0;
                                                    $colorClass = $percent > 90 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success');
                                                    $barWidth = min($percent, 100);
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress progress-xs w-full me-2" data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        title="<strong>{{ $usage }} / {{ $f->total_seats }}</strong> seats in use<br><small class='text-dark opacity-75'>{{ count($f->active_checkouts ?? []) }} active checkouts</small>">
                                                        <div class="progress-bar {{ $colorClass }}"
                                                            style="width: {{ $barWidth }}%"></div>
                                                    </div>
                                                    <span class="fw-bold text-dark">{{ $usage }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $expiry = $f->expiry_date;
                                                    $displayDate = $expiry->format('d M Y');

                                                    $now = now();
                                                    $diffInDays = $now->diffInDays($expiry, false);

                                                    $dotColor = 'bg-success'; // Long expiry (Green)
                                                    if ($expiry->isPast()) {
                                                        $dotColor = 'bg-danger'; // Expired (Red)
                                                    } elseif ($diffInDays <= 30) {
                                                        $dotColor = 'bg-warning'; // Coming Soon (Yellow)
                                                    }
                                                @endphp
                                                <div class="d-flex align-items-center small text-dark">
                                                    <span class="badge {{ $dotColor }} badge-empty me-1 shadow-sm"></span>
                                                    {{ $displayDate }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $f->status == 'enable' ? 'bg-success' : 'bg-secondary' }} badge-empty me-1"></span>
                                                <span class="small text-capitalize">{{ $f->status }}d</span>
                                                <i class="fas fa-chevron-down ms-2 opacity-50 transition-transform"></i>
                                            </td>
                                        </tr>
                                        <tr class="collapse border-0" id="details-{{ $f->id }}">
                                            <td colspan="5" class="p-0 border-0">
                                                <div class="p-3 bg-light-lt border-bottom">
                                                    <div class="card shadow-none border-dashed mb-0">
                                                        <div class="card-header py-2 bg-transparent">
                                                            <h4 class="card-title text-muted small fw-bold">Active User Details ({{ $f->license_name }})</h4>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            @if(count($f->active_checkouts ?? []) > 0)
                                                                <div class="table-responsive">
                                                                    <table class="table table-vcenter table-sm card-table">
                                                                        <thead>
                                                                            <tr class="text-dark bg-light-lt">
                                                                                <th class="fw-bold py-2">Seat</th>
                                                                                <th class="fw-bold py-2">Username</th>
                                                                                <th class="fw-bold py-2">IP Location</th>
                                                                                <th class="fw-bold py-2">Checkin At</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($f->active_checkouts as $index => $checkout)
                                                                                @php
                                                                                    $location = 'Remote';
                                                                                    if (str_starts_with($checkout->ip_address, '10.10')) $location = 'Jakarta';
                                                                                    elseif (str_starts_with($checkout->ip_address, '10.20')) $location = 'Balikpapan';
                                                                                    elseif (str_starts_with($checkout->ip_address, '10.30')) $location = 'Surabaya';
                                                                                @endphp
                                                                                <tr>
                                                                                    <td><span class="badge bg-blue-lt">Seat {{ $index + 1 }}</span></td>
                                                                                    <td class="fw-bold text-dark">{{ $checkout->username }}</td>
                                                                                    <td>
                                                                                        <div class="text-dark small">{{ $checkout->ip_address }}</div>
                                                                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $location }}</div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="text-dark small fw-bold">
                                                                                            {{ $checkout->recorded_at->format('H:i') }}
                                                                                        </div>
                                                                                        <div class="text-dark opacity-50" style="font-size: 0.7rem;">
                                                                                            {{ $checkout->recorded_at->diffForHumans() }}
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="p-3 text-center text-muted small">
                                                                    No active checkouts found or usage data unavailable.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No features found for this
                                                vendor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light-lt py-1">
                            <div class="d-flex flex-wrap align-items-center gap-3 small text-dark">
                                <span class="fw-bold"><i class="fas fa-info-circle me-1 opacity-50"></i> Expiry Status
                                    Legend:</span>
                                <span class="d-flex align-items-center"><span
                                        class="badge bg-danger badge-empty me-1 shadow-sm"></span> Expired</span>
                                <span class="d-flex align-items-center"><span
                                        class="badge bg-warning badge-empty me-1 shadow-sm"></span> Coming soon (&le; 30
                                    days)</span>
                                <span class="d-flex align-items-center"><span
                                        class="badge bg-success badge-empty me-1 shadow-sm"></span> Long-term (2030 or &gt;
                                    30 days)</span>
                            </div>
                        </div>
                    </div>

                    {{-- User Usage Logs Tab --}}
                    <div class="tab-pane {{ $activeTab == 'logs' ? 'active show' : '' }}" id="tabs-logs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Feature</th>
                                        <th>Timestamp</th>
                                        <th>Event</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="avatar avatar-xs me-2 rounded-circle bg-blue-lt text-blue">{{ substr($log->username, 0, 1) }}</span>
                                                    <span class="small fw-bold">{{ $log->username }}</span>
                                                </div>
                                            </td>
                                            <td><span class="small text-dark">{{ $log->license_name ?? 'Unknown' }}</span></td>
                                            <td><span
                                                    class="small text-dark">{{ $log->timestamp ? $log->timestamp->format('d M Y H:i') : 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $log->event_type == 'OUT' ? 'bg-warning-lt text-warning' : 'bg-success-lt text-success' }} small">
                                                    {{ $log->event_type == 'OUT' ? 'CHECKOUT' : 'CHECKIN' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small italic">No usage logs
                                                available for this vendor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- User Access Management Tab --}}
                    <div class="tab-pane {{ $activeTab == 'access' ? 'active show' : '' }}" id="tabs-access"
                        role="tabpanel">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Authorized Checkouts</h3>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                data-bs-target="#addAccessForm">
                                + Grant Access
                            </button>
                        </div>

                        <div class="collapse p-3 border-bottom bg-light-lt" id="addAccessForm">
                            <form action="{{ route('admin.licenses.access.grant') }}" method="POST">
                                @csrf
                                <input type="hidden" name="server_id" value="{{ $server?->id }}">
                                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Username</label>
                                        <input type="text" name="username" class="form-control"
                                            placeholder="Enter username (e.g. nurchulis)" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Allow Features</label>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle w-full text-start"
                                                type="button" id="featureDropdown" data-bs-toggle="dropdown"
                                                data-bs-auto-close="outside" aria-expanded="false">
                                                <span class="selected-count">0 features selected</span>
                                            </button>
                                            <div class="dropdown-menu w-full p-2" aria-labelledby="featureDropdown"
                                                style="max-height: 250px; overflow-y: auto;">
                                                @foreach($features as $f)
                                                    <label
                                                        class="dropdown-item d-flex align-items-center py-1 px-2 cursor-pointer">
                                                        <input class="form-check-input m-0 me-2 feature-checkbox"
                                                            type="checkbox" name="license_ids[]" value="{{ $f->id }}"
                                                            data-name="{{ $f->license_name }}">
                                                        <span class="small">{{ $f->license_name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="submit" class="btn btn-primary w-full">Grant</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Authorized Username</th>
                                        <th>Allowed Features</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($authorizedUsers as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="avatar avatar-xs me-2 rounded-circle bg-blue-lt text-blue">{{ substr($user->username, 0, 1) }}</span>
                                                    <span class="fw-bold">{{ $user->username }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($user->accessibleLicenses as $al)
                                                    <div class="badge bg-blue-lt text-blue me-1 mb-1">
                                                        {{ $al->license_name }}
                                                        <form action="{{ route('admin.licenses.access.revoke') }}" method="POST"
                                                            class="d-inline ms-1" onsubmit="return confirm('Revoke access?')">
                                                            @csrf
                                                            <input type="hidden" name="username" value="{{ $user->username }}">
                                                            <input type="hidden" name="license_id" value="{{ $al->id }}">
                                                            <button type="submit" class="text-danger border-0 bg-transparent p-0"
                                                                title="Revoke">
                                                                &times;
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-list justify-content-end">
                                                    <button class="btn btn-icon btn-sm btn-outline-primary"
                                                        onclick="openEditModal('{{ $user->username }}', {{ json_encode($user->accessibleLicenses->pluck('id')) }})"
                                                        title="Edit Access">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.licenses.access.revoke_all') }}" method="POST"
                                                        class="d-inline-flex m-0">
                                                        @csrf
                                                        <input type="hidden" name="username" value="{{ $user->username }}">
                                                        <input type="hidden" name="server_id" value="{{ $server?->id }}">
                                                        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                                        <button type="button" class="btn btn-icon btn-sm btn-outline-danger"
                                                            onclick="triggerDeleteConfirm(this, '{{ $user->username }}')"
                                                            title="Delete All Access">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small italic">No specific user
                                                level access configured.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Access Modal --}}
    <div class="modal modal-blur fade" id="editAccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.licenses.access.grant') }}" method="POST">
                    @csrf
                    <input type="hidden" name="server_id" value="{{ $server?->id }}">
                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Access: <span id="editUsernameLabel"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="username" id="editUsernameInput">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Allowed Features</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-full text-start" type="button"
                                    id="editFeatureDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <span class="selected-count" id="editSelectedCount">0 features selected</span>
                                </button>
                                <div class="dropdown-menu w-full p-2" aria-labelledby="editFeatureDropdown"
                                    style="max-height: 350px; overflow-y: auto;">
                                    @foreach($features as $f)
                                        <label class="dropdown-item d-flex align-items-center py-1 px-2 cursor-pointer">
                                            <input class="form-check-input m-0 me-2 feature-checkbox edit-feature-checkbox"
                                                type="checkbox" name="license_ids[]" value="{{ $f->id }}"
                                                data-name="{{ $f->license_name }}">
                                            <span class="small">{{ $f->license_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-muted small mt-1">Uncheck features to revoke access during update.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Access</button>
                    </div>
                </form>
            </div>
        </div>
        {{-- Sync Feedback Overlay --}}
        <div id="syncOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
            style="z-index: 9999; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px);">
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-5">
                <h3 class="mb-4 text-primary" style="font-size: 1.5rem; font-weight: 600;">Syncing Data...</h3>

                <div class="d-flex align-items-center justify-content-center mb-4"
                    style="height: 120px; width: 100%; max-width: 500px;">
                    <div class="text-center" style="width: 100px;">
                        <div class="bg-light p-3 rounded-3 mb-2 shadow-sm">
                            <i class="fas fa-desktop fa-2x text-muted"></i>
                        </div>
                        <div class="small fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Client
                        </div>
                    </div>

                    <div class="sync-path position-relative mx-4 flex-grow-1"
                        style="height: 4px; background: #e9ecef; border-radius: 2px; overflow: hidden; min-width: 100px;">
                        <div class="sync-packet position-absolute h-100 bg-primary shadow-sm" style="width: 40px;"></div>
                    </div>

                    <div class="text-center" style="width: 100px;">
                        <div class="bg-primary-lt p-3 rounded-3 mb-2 shadow-sm">
                            <i class="fas fa-cloud fa-2x text-primary"></i>
                        </div>
                        <div class="small fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">Server
                        </div>
                    </div>
                </div>

                <p class="text-muted mb-0" style="max-width: 400px;">Please wait while we synchronize your changes with the
                    license server. This ensures all session data is updated correctly.</p>
                <div class="mt-4">
                    <div class="spinner-border text-primary" style="width: 2rem; height: 2rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
@endsection

    {{-- Delete Confirmation Modal --}}
    <div class="modal modal-blur fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h3>Are you sure?</h3>
                    <div class="text-muted">Do you really want to remove all access for <strong
                            id="deleteUsernameLabel"></strong> on this vendor? This action cannot be undone.</div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">Cancel</a>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100" id="confirmDeleteBtn">Delete
                                    Access</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                // Feature Multi-select Dropdown (Create Form)
                setupDropdownHandlers('.feature-checkbox', '.selected-count');
                setupDropdownHandlers('.edit-feature-checkbox', '#editSelectedCount');

                window.openEditModal = function (username, licenseIds) {
                    document.getElementById('editUsernameLabel').textContent = username;
                    document.getElementById('editUsernameInput').value = username;

                    // Uncheck all first
                    const checkboxes = document.querySelectorAll('.edit-feature-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = licenseIds.includes(parseInt(cb.value));
                    });

                    updateDropdownCount('.edit-feature-checkbox', '#editSelectedCount');

                    var myModal = new bootstrap.Modal(document.getElementById('editAccessModal'));
                    myModal.show();
                };

                function setupDropdownHandlers(checkboxClass, countSelector) {
                    const checkboxes = document.querySelectorAll(checkboxClass);
                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', () => {
                            updateDropdownCount(checkboxClass, countSelector);
                        });
                    });
                }

                function updateDropdownCount(checkboxClass, countSelector) {
                    const checked = document.querySelectorAll(checkboxClass + ':checked');
                    const count = checked.length;
                    const countContainer = document.querySelector(countSelector);
                    const countSpan = countContainer.tagName === 'SPAN' ? countContainer : countContainer.querySelector('.selected-count') || countContainer;

                    if (count === 0) {
                        countSpan.textContent = '0 features selected';
                    } else if (count === 1) {
                        countSpan.textContent = checked[0].dataset.name;
                    } else {
                        countSpan.textContent = count + ' features selected';
                    }
                }
                let formToSubmit = null;

                window.showSyncAndSubmit = function (form) {
                    console.log("showSyncAndSubmit triggered for form:", form.action);

                    // Move overlay to body if not already there to ensure it's not trapped in a container
                    const overlay = document.getElementById('syncOverlay');
                    if (overlay && overlay.parentElement !== document.body) {
                        document.body.appendChild(overlay);
                    }

                    // Hide any open Bootstrap modals
                    const editModalEl = document.getElementById('editAccessModal');
                    const deleteModalEl = document.getElementById('confirmDeleteModal');

                    if (editModalEl) {
                        const editInstance = bootstrap.Modal.getInstance(editModalEl);
                        if (editInstance) editInstance.hide();
                    }
                    if (deleteModalEl) {
                        const deleteInstance = bootstrap.Modal.getInstance(deleteModalEl);
                        if (deleteInstance) deleteInstance.hide();
                    }

                    // Show sync overlay aggressively
                    if (overlay) {
                        console.log("Showing sync overlay...");
                        overlay.style.setProperty('display', 'flex', 'important');
                        overlay.classList.remove('d-none');
                    } else {
                        console.error("syncOverlay element not found!");
                    }

                    // Wait 2.5s then submit to ensure user sees the feedback
                    setTimeout(() => {
                        console.log("Submitting form now...");
                        form.submit();
                    }, 2500);
                };

                // Reliable Form Interception via Event Delegation
                const interceptForm = function (e) {
                    // If it's a form and NOT already handled by showSyncAndSubmit (to avoid recursion)
                    if (e.target.tagName === 'FORM' && !e.target.dataset.syncing) {
                        // Only intercept forms related to license access
                        const action = e.target.getAttribute('action') || '';
                        if (action.includes('licenses/access')) {
                            console.log("License form submission intercepted:", action);
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            e.target.dataset.syncing = "true";
                            showSyncAndSubmit(e.target);
                        }
                    }
                };

                const tabsAccess = document.getElementById('tabs-access');
                if (tabsAccess) tabsAccess.addEventListener('submit', interceptForm, true);

                const editAccessModal = document.getElementById('editAccessModal');
                if (editAccessModal) editAccessModal.addEventListener('submit', interceptForm, true);

                // Fallback for any other dynamically added license forms
                document.body.addEventListener('submit', interceptForm, true);

                window.triggerDeleteConfirm = function (el, username) {
                    document.getElementById('deleteUsernameLabel').textContent = username;
                    formToSubmit = el.closest('form');
                    var deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    deleteModal.show();
                };

                document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
                    if (formToSubmit) {
                        console.log("Delete confirmed, triggering sync...");
                        showSyncAndSubmit(formToSubmit);
                    }
                });
            });
        </script>
    @endpush
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

        @keyframes blink {
            0%, 49% {
                opacity: 1;
            }
            50%, 100% {
                opacity: 0.3;
            }
        }

        .status-indicator {
            animation: blink 1.5s infinite;
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

        @keyframes progressSlide {
            0% {
                width: 0%;
            }
            100% {
                width: var(--target-width, 100%);
            }
        }

        .animated-progress {
            animation: progressSlide 1.2s ease-in-out forwards;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        .feature-row-selected {
            background-color: rgba(32, 107, 196, 0.1) !important;
            border-left: 4px solid #206bc4 !important;
        }

        .feature-row-selected .fa-chevron-down {
            color: #206bc4 !important;
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
                            <h2 class="card-title h2 mb-1 text-primary fw-bold d-flex align-items-center">
                                @if($vendor->status == 'enable')
                                    <span class="status-dot status-dot-animated bg-success me-2"></span>
                                @else
                                    <span class="status-dot bg-danger me-2"></span>
                                @endif
                                VENDOR: {{ $vendor->name }}
                            </h2>
                            <div class="d-flex align-items-center flex-wrap gap-2 text-dark small">
                                <span>Server: 
                                    <code id="server-address">{{ $vendor->name_server ?? ($vendor->port ? $vendor->port . '@' . $server?->hostname : 'N/A') }}</code>
                                    <button class="btn btn-icon btn-ghost-primary btn-sm ms-1 border-0" 
                                        onclick="copyToClipboard('server-address', this)"
                                        title="Copy Server Info">
                                        <i class="fas fa-copy" style="font-size: 0.65rem;"></i>
                                    </button>
                                </span>
                                ({{ $server?->ip_address }})
                                <span class="badge {{ $vendor->status == 'enable' ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                    {{ $vendor->status == 'enable' ? 'UP' : 'DOWN' }}
                                </span>
                                <span class="text-muted ms-2">
                                    <i class="fas fa-history me-1"></i> Last Update From Server: 
                                    <span class="fw-bold">{{ $vendor->last_updated ? $vendor->last_updated->format('d M Y H:i') : 'Never' }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('create', \App\Models\License::class)
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editVendorModal"
                                    onclick="openEditVendorModal({{ $vendor->id }}, '{{ addslashes($vendor->name) }}', '{{ addslashes($vendor->name_server ?? '') }}', '{{ $vendor->license_server_id }}', '{{ $vendor->port }}', '{{ $vendor->status }}', '{{ addslashes($vendor->description ?? '') }}')">
                                    <i class="fas fa-edit me-1"></i> Edit Vendor
                                </button>
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

                {{-- Feature Usage Summary (Collapsible Infographic) --}}
                @php
                    $totalSeats = $features->sum('total_seats') ?? 0;
                    $totalUsedSeats = $features->sum('used_seats') ?? 0;
                    $usagePercentage = $totalSeats > 0 ? ($totalUsedSeats / $totalSeats) * 100 : 0;
                    $usageColor = $usagePercentage > 85 ? 'bg-danger' : ($usagePercentage >= 60 ? 'bg-warning' : 'bg-success');
                    $usageColorClass = $usagePercentage > 85 ? 'danger' : ($usagePercentage >= 60 ? 'warning' : 'success');
                @endphp

                <div class="card-body border-bottom p-3 bg-white">
                    {{-- Title and Chevron --}}
                    <button class="btn btn-link text-start p-0 w-100 text-decoration-none mb-3 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#featureUsageSummary" aria-expanded="false" aria-controls="featureUsageSummary">
                        <h4 class="text-dark fw-bold m-0 d-flex align-items-center">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                            Feature Usage Summary
                        </h4>
                        <i class="fas fa-chevron-down transition-transform" 
                           style="font-size: 0.85rem; cursor: pointer;" 
                           id="featureUsageSummaryTooltip"
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           data-bs-title="Click to view feature usage summary"
                           role="img"
                           aria-label="Expand feature usage summary"></i>
                    </button>

                    <div class="row g-4">
                        {{-- Summary Stats and Progress --}}
                        <div class="col-lg-9">
                            {{-- Overall Progress Bar with Animation --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted fw-bold">Overall Usage</small>
                                    <span class="badge bg-{{ $usageColorClass }}-lt text-{{ $usageColorClass }} fw-bold">
                                        {{ number_format($usagePercentage, 1) }}%
                                    </span>
                                </div>
                                <div class="progress progress-lg" style="height: 24px;">
                                    <div class="progress-bar {{ $usageColor }} animated-progress" 
                                         role="progressbar" 
                                         style="width: 0%;"
                                         data-target-width="{{ min($usagePercentage, 100) }}"
                                         aria-valuenow="{{ $usagePercentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            {{-- Summary Stats Row --}}
                            <div class="row g-3 small">
                                <div class="col-6 col-md-3">
                                    <div class="bg-light-lt p-3 rounded-2 text-center">
                                        <div class="text-muted small mb-1">Total Features</div>
                                        <div class="text-dark fw-bold" style="font-size: 1.3rem;">{{ $features->count() }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light-lt p-3 rounded-2 text-center">
                                        <div class="text-muted small mb-1">Total Seats</div>
                                        <div class="text-dark fw-bold" style="font-size: 1.3rem;">{{ $totalSeats }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light-lt p-3 rounded-2 text-center">
                                        <div class="text-muted small mb-1">Used Seats</div>
                                        <div class="text-dark fw-bold text-{{ $usageColorClass }}" style="font-size: 1.3rem;">{{ $totalUsedSeats }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light-lt p-3 rounded-2 text-center">
                                        <div class="text-muted small mb-1">Free Seats</div>
                                        <div class="text-dark fw-bold text-success" style="font-size: 1.3rem;">{{ $totalSeats - $totalUsedSeats }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Donut Chart --}}
                        <div class="col-lg-3 d-flex justify-content-center align-items-center">
                            <div class="text-center">
                                <div style="position: relative; width: 140px; height: 140px; margin: 0 auto;">
                                    <svg viewBox="0 0 100 100" style="transform: rotate(-90deg);">
                                        {{-- Background circle --}}
                                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                        {{-- Progress circle --}}
                                        <circle cx="50" cy="50" r="40" fill="none" 
                                                stroke-dasharray="{{ (252 * $usagePercentage / 100) }} 252"
                                                stroke="currentColor" 
                                                stroke-width="8"
                                                class="text-{{ $usageColorClass }}"
                                                style="transition: stroke-dasharray 1s ease-in-out;"/>
                                    </svg>
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                        <div class="fw-bold text-dark" style="font-size: 1.8rem;">{{ number_format($usagePercentage, 0) }}%</div>
                                        <small class="text-muted d-block" style="font-size: 0.8rem;">Used</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-link text-start p-0 w-100 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#featureUsageSummary" aria-expanded="false" aria-controls="featureUsageSummary">
                    </button>

                    {{-- Collapsible Detail Section --}}
                    <div class="collapse mt-3" id="featureUsageSummary">
                        <div class="card card-flush border-0 bg-light-lt">
                            <div class="card-header bg-transparent border-0 py-2">
                                <h5 class="card-title text-dark small fw-bold mb-0">Feature Breakdown</h5>
                            </div>
                            <div class="card-body p-2">
                                @forelse($features as $feature)
                                    @php
                                        $featureUsed = $feature->used_seats;
                                        $featureTotal = $feature->total_seats;
                                        $featurePercent = $featureTotal > 0 ? ($featureUsed / $featureTotal) * 100 : 0;
                                        $featureColor = $featurePercent > 85 ? 'bg-danger' : ($featurePercent >= 60 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="row g-2 align-items-center py-2 border-bottom small">
                                        <div class="col-12 col-sm-5">
                                            <div class="fw-bold text-dark text-truncate">{{ $feature->license_name }}</div>
                                            <div class="text-muted" style="font-size: 0.85rem;">{{ $featureUsed }} / {{ $featureTotal }} seats</div>
                                        </div>
                                        <div class="col-12 col-sm-7">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 16px;">
                                                    <div class="progress-bar {{ $featureColor }}"
                                                         style="width: {{ min($featurePercent, 100) }}%;"
                                                         role="progressbar"
                                                         aria-valuenow="{{ $featurePercent }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="text-dark fw-bold" style="min-width: 40px; text-align: right;">
                                                    {{ number_format($featurePercent, 0) }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-muted small">
                                        No features available.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-header border-bottom p-0">
                    <ul class="nav nav-tabs" data-bs-toggle="tabs" role="tablist">
                        <li class="nav-item fw-bold" role="presentation">
                            <a href="#tabs-features" class="nav-link {{ $activeTab == 'features' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'features' ? 'true' : 'false' }}"
                                role="tab">
                                <i class="fas fa-cubes me-2 opacity-75"></i> Feature List
                            </a>
                        </li>
                        <li class="nav-item fw-bold" role="presentation">
                            <a href="#tabs-logs" class="nav-link {{ $activeTab == 'logs' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'logs' ? 'true' : 'false' }}"
                                role="tab">
                                <i class="fas fa-history me-2 opacity-75"></i> Usage Logs
                            </a>
                        </li>
                        <li class="nav-item fw-bold" role="presentation">
                            <a href="#tabs-access" class="nav-link {{ $activeTab == 'access' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'access' ? 'true' : 'false' }}"
                                role="tab">
                                <i class="fas fa-user-shield me-2 opacity-75"></i> Access Management
                            </a>
                        </li>
                        <li class="nav-item fw-bold" role="presentation">
                            <a href="#tabs-trends" class="nav-link {{ $activeTab == 'trends' ? 'active' : '' }}"
                                data-bs-toggle="tab" aria-selected="{{ $activeTab == 'trends' ? 'true' : 'false' }}"
                                role="tab">
                                <i class="fas fa-chart-line me-2 opacity-75"></i> Usage Trends
                            </a>
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
                                        <th class="fw-bold" style="width: 50px;">No</th>
                                        <th class="fw-bold">Feature Name</th>
                                        <th class="fw-bold">Capacity</th>
                                        <th class="fw-bold">In Use</th>
                                        <th class="fw-bold">Expiry</th>
                                        <th class="fw-bold">Current users</th>
                                        <!-- <th class="fw-bold text-end">Action</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($features as $f)
                                        <tr class="cursor-pointer hover-bg-light" title="Click to view active users">
                                            <td class="text-center text-muted fw-bold small" style="width: 50px;" data-bs-toggle="collapse" data-bs-target="#details-{{ $f->id }}">{{ $loop->iteration }}</td>
                                            <td class="text-dark" data-bs-toggle="collapse" data-bs-target="#details-{{ $f->id }}">
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-bold">{{ $f->license_name }}</span>
                                                    @if($f->version)
                                                        <span class="badge bg-blue-lt ms-2 border-0 fw-normal" style="font-size: 0.65rem;">
                                                            v{{ $f->version }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td data-bs-toggle="collapse" data-bs-target="#details-{{ $f->id }}">{{ $f->total_seats }} seats</td>
                                            <td style="width: 250px;" data-bs-toggle="collapse" data-bs-target="#details-{{ $f->id }}">
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
                                            <td data-bs-toggle="collapse" data-bs-target="#details-{{ $f->id }}">
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
                                            <td data-bs-toggle="collapse" data-bs-target="#details-{{ $f->id }}">
                                                <!-- <span class="badge {{ $f->status == 'enable' ? 'bg-success' : 'bg-secondary' }} badge-empty me-1"></span>
                                                <span class="small text-capitalize">{{ $f->status }}d</span> -->
                                                <i class="fas fa-chevron-down ms-2 opacity-50 transition-transform {{ $loop->first ? 'feature-list-tooltip' : '' }}"
                                                   @if($loop->first) 
                                                     id="featureListChevronTooltip"
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-placement="top" 
                                                     data-bs-title="Click row to view active users"
                                                     role="img"
                                                     aria-label="View active users"
                                                     style="cursor: pointer;"
                                                   @endif>
                                                </i>
                                            </td>
                                            <!-- <td class="text-end">
                                                @can('update', $f)
                                                <a href="{{ route('admin.licenses.edit', $f) }}" class="btn btn-sm btn-primary" title="Edit License">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                                @endcan
                                            </td> -->
                                        </tr>
                                        <tr class="collapse border-0" id="details-{{ $f->id }}">
                                            <td colspan="8" class="p-0 border-0">
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
                                                                                <th class="fw-bold py-2">
                                                                                    <div class="d-flex align-items-center">
                                                                                        Seat
                                                                                    </div>
                                                                                </th>
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
                                                                                    <td>
                                                                                        <div class="d-flex align-items-center">
                                                                                            <span class="status-dot status-dot-animated bg-success me-2"></span>
                                                                                            <span class="badge bg-blue-lt">Seat {{ $index + 1 }}</span>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="fw-bold text-dark"><div class="d-flex align-items-center"><i class="fas fa-user-circle text-muted me-2" style="font-size: 0.85rem;"></i>{{ $checkout->username }}</div></td>
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
                                            <td colspan="7" class="py-5 border-0">
                                                <div class="d-flex justify-content-center">
                                                    <div class="card shadow-sm border-dashed p-4 p-md-5 text-center bg-white" style="max-width: 700px;">
                                                        {{-- Illustration --}}
                                                        <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
                                                            <div class="bg-light p-3 rounded-3 border">
                                                                <i class="fas fa-server fa-2x text-muted"></i>
                                                            </div>
                                                            <div class="d-flex flex-column align-items-center position-relative" style="width: 80px;">
                                                                <div class="sync-animation position-relative w-100 mb-2" style="height: 2px; background: #e9ecef;">
                                                                    <div class="position-absolute bg-primary sync-packet" style="width: 20px; height: 100%;"></div>
                                                                </div>
                                                                <i class="fas fa-sync fa-sm text-primary opacity-50"></i>
                                                            </div>
                                                            <div class="bg-primary-lt p-3 rounded-3 border border-primary-subtle position-relative">
                                                                <i class="fas fa-robot fa-2x text-primary"></i>
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info shadow-sm" style="font-size: 0.55rem; padding: 0.35em 0.65em;">
                                                                    AGENT
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {{-- Main Message --}}
                                                        <h3 class="fw-bold text-dark mb-2">No features detected yet</h3>
                                                        <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                                                            Install the agent on your server to automatically detect and sync available features into this dashboard.
                                                        </p>

                                                        {{-- Sync Hint --}}
                                                        <div class="alert alert-info border-0 bg-info-lt py-2 px-3 mb-4 d-inline-block mx-auto small">
                                                            <i class="fas fa-info-circle me-2"></i> Once the agent is running, data will appear here automatically.
                                                        </div>

                                                        {{-- Install Instructions --}}
                                                        <div class="row g-3 text-start mb-4">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-uppercase text-muted mb-1"> 
                                                                    <i class="fab fa-linux me-1"></i> Linux
                                                                </label>
                                                                <div class="bg-light p-2 rounded border">
                                                                    <code class="small text-dark fw-bold">curl -sSL https://agent-install.sh | bash</code>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-uppercase text-muted mb-1"> 
                                                                    <i class="fab fa-windows me-1"></i> Windows
                                                                </label>
                                                                <div class="bg-light p-2 rounded border">
                                                                    <code class="small text-dark fw-bold text-break">Invoke-WebRequest -Uri https://agent-install.ps1 -OutFile install.ps1; ./install.ps1</code>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- CTA and Status --}}
                                                        <div class="d-flex flex-column align-items-center gap-3">
                                                            <a href="#" class="btn btn-primary btn-sm px-4 shadow-sm">
                                                                <i class="fas fa-book-open me-2"></i> View Full Installation Guide
                                                            </a>

                                                            <div class="d-flex justify-content-center align-items-center gap-2 mt-2 text-muted">
                                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                                    <span class="visually-hidden">Loading...</span>
                                                                </div>
                                                                <small class="fw-medium">Waiting for agent connection...</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
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
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Usage Logs</h3>
                            @if($logs->count() > 0)
                            <form action="{{ route('admin.licenses.logs.export', ['vendor_id' => $vendor->id]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-download me-1"></i> Download Excel
                                </button>
                            </form>
                            @endif
                        </div>
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
                                                @php
                                                    $badgeClass = match($log->event_type) {
                                                        'checkout' => 'bg-warning-lt text-warning',
                                                        'checkin' => 'bg-success-lt text-success',
                                                        'failed_checkout', 'failed_checkin' => 'bg-danger-lt text-danger',
                                                        'denied' => 'bg-red-lt text-red',
                                                        default => 'bg-info-lt text-info'
                                                    };
                                                    
                                                    $icon = match($log->event_type) {
                                                        'checkout' => 'fa-sign-out-alt',
                                                        'checkin' => 'fa-sign-in-alt',
                                                        'failed_checkout', 'failed_checkin' => 'fa-exclamation-triangle',
                                                        'denied' => 'fa-times-circle',
                                                        default => 'fa-info-circle'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} small border-0">
                                                    <i class="fas {{ $icon }} me-1"></i>
                                                    {{ str_replace('_', ' ', strtoupper($log->event_type)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-5 border-0">
                                                <div class="text-center p-4">
                                                    <div class="bg-light-lt d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                                                        <i class="fas fa-history fa-2x text-muted opacity-50"></i>
                                                    </div>
                                                    <h4 class="fw-bold text-dark mb-1">No usage history detected</h4>
                                                    <p class="text-muted small mx-auto" style="max-width: 400px;">
                                                        Detailed logs of user activity (checkout/checkin) will appear here once the agent starts reporting usage data.
                                                    </p>
                                                </div>
                                            </td>
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
                                            <td colspan="3" class="py-5 border-0">
                                                <div class="text-center p-4">
                                                    <div class="bg-light-lt d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                                                        <i class="fas fa-user-shield fa-2x text-muted opacity-50"></i>
                                                    </div>
                                                    <h4 class="fw-bold text-dark mb-1">No specific access rules configured</h4>
                                                    <p class="text-muted small mx-auto" style="max-width: 400px;">
                                                        By default, all authenticated users may have access depending on server policy. You can grant specific feature access to individual users here.
                                                    </p>
                                                    <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="collapse" data-bs-target="#addAccessForm">
                                                        <i class="fas fa-plus me-1"></i> Grant First Access
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Usage Trends Tab --}}
                    <div class="tab-pane {{ $activeTab == 'trends' ? 'active show' : '' }}" id="tabs-trends"
                        role="tabpanel">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h3 class="card-title mb-1">Historical Feature Usage</h3>
                                    <p class="text-muted small mb-0">Monitor seat usage fluctuations and capacity limits over time.</p>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary range-btn" data-range="hourly">Hourly</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary range-btn active" data-range="daily">Daily</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary range-btn" data-range="weekly">Weekly</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary range-btn" data-range="monthly">Monthly</button>
                                </div>
                            </div>

                            <div class="row g-3" id="trends-charts-container">
                                @forelse($features as $f)
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <span class="badge bg-blue-lt mb-1">{{ $f->license_name }}</span>
                                                        <div class="small text-muted">Limit: {{ $f->total_seats }} seats</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="h3 mb-0 fw-bold">{{ $f->used_seats }}</div>
                                                        <div class="small text-muted">Current</div>
                                                    </div>
                                                </div>
                                                <div id="chart-{{ $f->id }}" style="min-height: 200px;">
                                                    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                                                        <div class="spinner-border text-muted opacity-25" role="status"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5 text-muted">
                                        No features available to visualize.
                                    </div>
                                @endforelse
                            </div>
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
        <div id="syncOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none d-flex align-items-center justify-content-center"
            style="z-index: 9999; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px);">
            <div class="text-center p-5" style="max-width: 500px;">
                <h3 class="mb-4 text-primary" style="font-size: 1.5rem; font-weight: 600;">Syncing Data...</h3>

                <div class="d-flex align-items-center justify-content-center mb-4"
                    style="height: 120px; width: 100%;">
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

                <p class="text-muted mb-0">Please wait while we synchronize your changes with the
                    license server. This ensures all session data is updated correctly.</p>
                <div class="mt-4">
                    <div class="spinner-border text-primary" style="width: 2rem; height: 2rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
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
                        <h5 class="modal-title">Edit Vendor: {{ $vendor->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Vendor Name</label>
                            <input type="text" class="form-control" name="name" id="edit_vendor_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name Server (e.g. 2094@LLJOSAJ1)</label>
                            <input type="text" class="form-control" name="name_server" id="edit_name_server" placeholder="e.g. 2094@LLJOSAJ1">
                        </div>
                        @if(auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin'))
                        <div class="mb-3">
                            <label class="form-label required">License Server</label>
                            <select class="form-select" name="license_server_id" id="edit_vendor_server" required>
                                <option value="">— Select Server —</option>
                                @foreach (\App\Models\LicenseServer::all() as $srv)
                                    <option value="{{ $srv->id }}">{{ $srv->server_name }} ({{ $srv->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Port</label>
                            <input type="text" class="form-control" name="port" id="edit_vendor_port" required>
                        </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" name="status" id="edit_vendor_status" required>
                                <option value="enable">Active (Enable)</option>
                                <option value="disable">Disabled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" id="edit_vendor_description" rows="2"></textarea>
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
                window.openEditVendorModal = function (id, name, nameServer, serverId, port, status, description) {
                    document.getElementById('editVendorForm').action = '/admin/vendors/' + id;
                    document.getElementById('edit_vendor_name').value = name;
                    document.getElementById('edit_name_server').value = nameServer;
                    if (document.getElementById('edit_vendor_server')) {
                        document.getElementById('edit_vendor_server').value = serverId;
                    }
                    if (document.getElementById('edit_vendor_port')) {
                        document.getElementById('edit_vendor_port').value = port;
                    }
                    document.getElementById('edit_vendor_status').value = status;
                    document.getElementById('edit_vendor_description').value = description;
                };

                // Tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

                // Auto-show Feature Usage Summary tooltip on page load
                const featureUsageSummaryTooltip = document.getElementById('featureUsageSummaryTooltip');
                if (featureUsageSummaryTooltip) {
                    const tooltipInstance = bootstrap.Tooltip.getInstance(featureUsageSummaryTooltip) || 
                                           new bootstrap.Tooltip(featureUsageSummaryTooltip);
                    
                    // Show tooltip after a brief delay
                    setTimeout(() => {
                        tooltipInstance.show();
                    }, 500);
                    
                    // Auto-hide tooltip after 4 seconds
                    setTimeout(() => {
                        tooltipInstance.hide();
                    }, 4500);

                    // Re-show tooltip on page refresh (if user navigates away and comes back)
                    // This fires whenever tab becomes visible again
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden && featureUsageSummaryTooltip) {
                            setTimeout(() => {
                                tooltipInstance.show();
                            }, 300);
                            
                            setTimeout(() => {
                                tooltipInstance.hide();
                            }, 4300);
                        }
                    });
                }

                // Auto-show Feature List Chevron tooltip on first row only
                const featureListChevronTooltip = document.getElementById('featureListChevronTooltip');
                if (featureListChevronTooltip) {
                    const chevronTooltipInstance = bootstrap.Tooltip.getInstance(featureListChevronTooltip) || 
                                                   new bootstrap.Tooltip(featureListChevronTooltip);
                    
                    // Show tooltip after a brief delay
                    setTimeout(() => {
                        chevronTooltipInstance.show();
                    }, 1500);
                    
                    // Auto-hide tooltip after 4 seconds
                    setTimeout(() => {
                        chevronTooltipInstance.hide();
                    }, 5500);

                    // Re-show tooltip on page refresh
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden && featureListChevronTooltip) {
                            setTimeout(() => {
                                chevronTooltipInstance.show();
                            }, 1000);
                            
                            setTimeout(() => {
                                chevronTooltipInstance.hide();
                            }, 5000);
                        }
                    });
                }

                // Handle row selection and visual feedback
                const featureRows = document.querySelectorAll('tr.cursor-pointer.hover-bg-light');
                featureRows.forEach(row => {
                    row.addEventListener('click', function() {
                        // Remove selected class from all rows
                        featureRows.forEach(r => r.classList.remove('feature-row-selected'));
                        
                        // Add selected class to clicked row
                        this.classList.add('feature-row-selected');
                        
                        // Update aria-expanded
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        this.setAttribute('aria-expanded', !isExpanded);
                    });
                });

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

                // Animate progress bars on page load
                function animateProgressBars() {
                    const progressBars = document.querySelectorAll('.animated-progress');
                    progressBars.forEach(bar => {
                        const targetWidth = parseFloat(bar.getAttribute('data-target-width')) || 0;
                        bar.style.setProperty('--target-width', targetWidth + '%');
                    });
                }

                window.copyToClipboard = function(id, btn) {
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


                // Usage Trends Chart Logic
                const charts = {};
                const rangeButtons = document.querySelectorAll('.range-btn');
                let currentRange = 'daily';

                function initCharts() {
                    const featureIds = @json($features->pluck('id'));
                    featureIds.forEach(id => {
                        fetchUsageData(id, currentRange);
                    });
                }

                async function fetchUsageData(licenseId, range) {
                    try {
                        const response = await fetch(`/admin/licenses/${licenseId}/usage-metrics?range=${range}`);
                        const result = await response.json();
                        renderChart(licenseId, result);
                    } catch (error) {
                        console.log("Error loading chart data for " + licenseId, error);
                    }
                }

                function renderChart(licenseId, result) {
                    const container = document.querySelector(`#chart-${licenseId}`);
                    if (!container) return;
                    
                    container.innerHTML = '';

                    const options = {
                        series: [{
                            name: 'Peak Usage',
                            data: result.data
                        }],
                        chart: {
                            type: 'area',
                            height: 200,
                            sparkline: { enabled: false },
                            toolbar: { show: false },
                            animations: { enabled: true },
                            fontFamily: 'inherit',
                            foreColor: '#6e7582'
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        grid: {
                            strokeDashArray: 4,
                            padding: { left: 0, right: 0, bottom: 0 }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.05,
                                stops: [20, 100, 100, 100]
                            }
                        },
                        xaxis: {
                            type: 'datetime',
                            labels: {
                                datetimeUTC: false,
                                style: { fontSize: '10px' }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            min: 0,
                            max: Math.max(result.total_seats, ...result.data.map(d => d.y)) + 1,
                            tickAmount: 4,
                            labels: {
                                style: { fontSize: '10px' },
                                formatter: (val) => Math.floor(val)
                            }
                        },
                        annotations: {
                            yaxis: [{
                                y: result.total_seats,
                                borderColor: '#d63939',
                                strokeDashArray: 4,
                                borderWeight: 2,
                                label: {
                                    borderColor: '#d63939',
                                    offsetY: 0,
                                    style: { 
                                        color: '#fff', 
                                        background: '#d63939', 
                                        fontSize: '10px',
                                        fontWeight: 600,
                                        padding: { left: 5, right: 5, top: 2, bottom: 2 }
                                    },
                                    text: 'Limit: ' + result.total_seats
                                }
                            }]
                        },
                        colors: ['#206bc4'],
                        tooltip: { 
                            x: { format: 'dd MMM yyyy HH:mm' },
                            theme: 'dark'
                        }
                    };

                    if (charts[licenseId]) {
                        charts[licenseId].destroy();
                    }
                    
                    const chart = new ApexCharts(container, options);
                    chart.render();
                    charts[licenseId] = chart;
                }

                rangeButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        rangeButtons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        currentRange = this.getAttribute('data-range');
                        initCharts();
                    });
                });

                // Initialize charts when tab is shown
                const trendsTabAnchor = document.querySelector('a[href="#tabs-trends"]');
                if (trendsTabAnchor) {
                    trendsTabAnchor.addEventListener('shown.bs.tab', function () {
                        initCharts();
                        window.dispatchEvent(new Event('resize'));
                    });
                }

                // Call animation on load
                animateProgressBars();

                // Re-animate when summary collapse is expanded
                const featureUsageSummary = document.getElementById('featureUsageSummary');
                if (featureUsageSummary) {
                    featureUsageSummary.addEventListener('show.bs.collapse', function () {
                        animateProgressBars();
                    });
                }
            });
        </script>
    @endpush
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Petrotechnical Platform</title>
    <meta name="description" content="Pertamina UC2 — Unified Petrotechnical Platform">

    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <!-- ApexCharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.css">

    <style>
        :root {
            --petrotech-primary: #1a3c6b;
            --petrotech-accent: #e8731a;
            --petrotech-sidebar: #0f2540;
        }

        .navbar-vertical.navbar-expand-lg {
            background: var(--petrotech-sidebar) !important;
        }

        .navbar-vertical .nav-link-title {
            color: #c8d8ee;
        }

        .navbar-vertical .nav-link:hover .nav-link-title,
        .navbar-vertical .nav-link.active .nav-link-title {
            color: #fff;
        }

        .navbar-vertical .nav-link.active {
            background: rgba(255, 255, 255, .08);
            border-radius: 6px;
        }

        .metric-card {
            transition: transform .15s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .12);
        }

        .sidebar-logo-text {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.2;
        }

        .sidebar-logo-sub {
            color: #7fa3c8;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .nav-section-title {
            font-size: .65rem;
            color: #4a7fa5;
            text-transform: uppercase;
            letter-spacing: .1em;
            padding: .75rem 1rem .25rem;
            display: block;
        }

        @keyframes pulse-green {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(47, 179, 68, .4);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(47, 179, 68, 0);
            }
        }
    </style>
    @stack('styles')
</head>

<body class="antialiased">
    <div class="wrapper">

        {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 py-3">
                    <span style="font-size:1.5rem">🛢️</span>
                    <div>
                        <div class="sidebar-logo-text">Petrotechnical</div>
                        <div class="sidebar-logo-sub">Platform UC2</div>
                    </div>
                </a>

                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7" />
                                        <rect x="14" y="3" width="7" height="7" />
                                        <rect x="3" y="14" width="7" height="7" />
                                        <rect x="14" y="14" width="7" height="7" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item"><span class="nav-section-title">Operations</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vdi.*') ? 'active' : '' }}"
                                href="{{ route('vdi.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="3" width="20" height="14" rx="2" />
                                        <path d="M8 21h8M12 17v4" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">VDI Access</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}"
                                href="{{ route('tickets.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M15 5v2M15 11v2M15 17v2M5 5h14a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 000-4V7a2 2 0 012-2z" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">Ticketing</span>
                            </a>
                        </li>

                        @role(['admin', 'super_admin'])
                        <li class="nav-item"><span class="nav-section-title">Administration</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.licenses.*') ? 'active' : '' }}"
                                href="{{ route('admin.licenses.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="3" width="15" height="13" rx="1" />
                                        <path d="M16 8h5a1 1 0 011 1v9a1 1 0 01-1 1H10a1 1 0 01-1-1v-4" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">License Management</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.vm-monitoring.*') ? 'active' : '' }}"
                                href="{{ route('admin.vm-monitoring.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">VM Monitoring</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}"
                                href="{{ route('admin.storage.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">Storage Monitor</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}"
                                href="{{ route('admin.analytics.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="20" x2="18" y2="10" />
                                        <line x1="12" y1="20" x2="12" y2="4" />
                                        <line x1="6" y1="20" x2="6" y2="14" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">Analytics &amp; Reports</span>
                            </a>
                        </li>


                        @endrole

                        @role(['admin','super_admin'])
                        <li class="nav-item"><span class="nav-section-title">System</span></li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                                href="{{ route('admin.users.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">User Management</span>
                            </a>
                        </li>
                        @endrole

                        @role('super_admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                                href="{{ route('admin.roles.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">Role Management</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                                href="{{ route('settings.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">Settings</span>
                            </a>
                        </li>
                        @endrole

                    </ul>
                </div>
            </div>
        </aside>

        {{-- ── Main content ─────────────────────────────────────────────────── --}}
        <div class="page-wrapper">

            {{-- Topbar --}}
            <div style="background:#fff;border-bottom:1px solid #e6edf3;">
                <div class="container-xl d-flex align-items-center py-2">
                    <small class="text-muted">@yield('breadcrumb', 'Dashboard')</small>
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <span class="badge bg-success-lt">
                            <span class="me-1">●</span> System Online
                        </span>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none"
                                data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="32"
                                    height="32" alt="">
                                <div class="d-none d-md-block lh-sm">
                                    <div style="font-size:.85rem;font-weight:600">{{ auth()->user()->name }}</div>
                                    <div style="font-size:.7rem;color:#6c757d">
                                        {{ auth()->user()->getRoleNames()->first() }}</div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                <span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="container-xl pt-3">
                <div class="alert alert-success alert-dismissible">
                    ✓ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            @endif
            @if(session('error'))
            <div class="container-xl pt-3">
                <div class="alert alert-danger alert-dismissible">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            @endif

            <div class="page-body">
                <div class="container-xl py-3">
                    @yield('content')
                </div>
            </div>

            <footer class="footer footer-transparent d-print-none"
                style="border-top:1px solid #e6edf3;padding:.75rem 0">
                <div class="container-xl text-center text-muted" style="font-size:.8rem">
                    © {{ date('Y') }} Petrotechnical Platform · Pertamina UC2 Cloud Infrastructure
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
    @stack('scripts')
</body>

</html>
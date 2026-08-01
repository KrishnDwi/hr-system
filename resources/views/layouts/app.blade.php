<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ETMS') — Employee Training Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #111827;
            --sidebar-width: 260px;
            --sidebar-text: #9ca3af;
            --sidebar-text-active: #ffffff;
            --sidebar-active-bg: #1f2937;
            --content-bg: #f3f4f6;
            --accent-blue: #2563eb;
            --accent-green: #16a34a;
            --accent-red: #dc2626;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background-color: var(--content-bg);
            margin: 0;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ===== Sidebar ===== */
        .app-sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            padding: 28px 18px;
            position: sticky;
            top: 0;
            flex-shrink: 0;
        }

        .sidebar-brand {
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 36px;
            padding: 0 10px;
            line-height: 1.3;
        }

        .sidebar-section-label {
            color: #6b7280;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin: 22px 10px 10px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--sidebar-text);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 11px 14px;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: background-color .15s ease, color .15s ease;
        }

        .sidebar-nav .nav-link:hover {
            background-color: var(--sidebar-active-bg);
            color: #fff;
        }

        .sidebar-nav .nav-link.active {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: 600;
        }

        hr.sidebar-divider {
            border-color: #374151;
            margin: 18px 0 0;
        }

        /* ===== Main area ===== */
        .app-main {
            flex: 1;
            min-width: 0;
        }

        .app-topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            padding: 36px 40px 8px;
        }

        .app-topbar h1 {
            font-size: 1.9rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }

        .app-topbar p {
            color: #6b7280;
            margin-bottom: 0;
        }

        .topbar-actions .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 18px;
        }

        .app-content {
            padding: 8px 40px 48px;
        }

        /* ===== Stat cards (mirip referensi) ===== */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #eef0f2;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            padding: 22px 24px;
            height: 100%;
        }

        .stat-card .stat-title {
            font-weight: 700;
            color: #111827;
            font-size: 1rem;
            margin-bottom: 14px;
        }

        .stat-card .stat-value {
            font-size: 2.1rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
            margin-bottom: 10px;
        }

        .stat-card .stat-caption {
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .stat-card.accent-red .stat-value { color: var(--accent-red); }
        .stat-card.accent-blue .stat-value { color: var(--accent-blue); }
        .stat-card.accent-green .stat-value { color: var(--accent-green); }

        /* ===== General content cards ===== */
        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #eef0f2;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .content-card .content-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f2f4;
            font-weight: 700;
            color: #111827;
        }

        .content-card .content-card-body {
            padding: 24px;
        }

        .btn-primary { background-color: var(--accent-blue); border-color: var(--accent-blue); }
        .btn-success { background-color: var(--accent-green); border-color: var(--accent-green); }
    </style>

    @stack('styles')
</head>
<body>

<div class="app-wrapper">
    <aside class="app-sidebar">
        <div class="sidebar-brand">Harris Hotel Seminyak<br><small style="font-weight:500; color:#9ca3af; font-size:0.75rem;">ETMS</small></div>

        {{-- Route::has() dipakai supaya nav tidak error sebelum semua modul selesai dibuat --}}
        <nav class="sidebar-nav">
            @if(Route::has('dashboard'))
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            @endif
            <a class="nav-link {{ request()->routeIs('training-modules.*') ? 'active' : '' }}" href="{{ route('training-modules.index') }}">
                <i class="bi bi-mortarboard"></i> Master Training
            </a>
            @if(Route::has('employees.index'))
                <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                    <i class="bi bi-people"></i> Data Karyawan
                </a>
            @endif
            <a class="nav-link {{ request()->routeIs('training-sessions.*') ? 'active' : '' }}" href="{{ route('training-sessions.index') }}">
                <i class="bi bi-calendar-check"></i> Training Session
            </a>
            @if(Route::has('reports.index'))
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Report
                </a>
            @endif
        </nav>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div>
                <h1>@yield('page-title', 'ETMS')</h1>
                <p>@yield('page-subtitle')</p>
            </div>
            <div class="topbar-actions d-flex gap-2">
                @yield('page-actions')
            </div>
        </header>

        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
@stack('scripts')
</body>
</html>

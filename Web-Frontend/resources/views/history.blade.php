<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sensor – Monitoring Pasien IoT</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ===== DARK MODE (default) ===== */
        :root {
            --bg:          #0a0e1a;
            --bg-panel:    #0f1525;
            --bg-card:     rgba(255,255,255,0.04);
            --border:      rgba(255,255,255,0.08);
            --accent:      #3b82f6;
            --accent2:     #8b5cf6;
            --accent3:     #06b6d4;
            --success:     #10b981;
            --warning:     #f59e0b;
            --danger:      #ef4444;
            --text:        #f1f5f9;
            --muted:       #64748b;
            --sidebar-w:   260px;
            --shadow-card: 0 4px 24px rgba(0,0,0,0.4);
        }

        /* ===== LIGHT MODE ===== */
        :root.light-mode {
            --bg:          #e8edf3;
            --bg-panel:    #ffffff;
            --bg-card:     rgba(255,255,255,0.95);
            --border:      rgba(0,0,0,0.13);
            --text:        #1e293b;
            --muted:       #64748b;
            --shadow-card: 0 6px 28px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);
        }

        /* ===== THEME TOGGLE BUTTON ===== */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 5px 14px 5px 8px;
            cursor: pointer;
            transition: all 0.25s ease;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            font-family: inherit;
        }
        .theme-toggle:hover {
            border-color: var(--accent);
            background: rgba(59,130,246,0.1);
        }
        .theme-toggle-track {
            width: 38px;
            height: 20px;
            background: var(--muted);
            border-radius: 99px;
            position: relative;
            transition: background 0.3s ease;
            flex-shrink: 0;
        }
        :root.light-mode .theme-toggle-track {
            background: var(--accent);
        }
        .theme-toggle-thumb {
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            line-height: 1;
        }
        :root.light-mode .theme-toggle-thumb {
            transform: translateX(18px);
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ===================== SIDEBAR ===================== */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(59,130,246,0.4);
        }

        .brand-icon svg { width: 20px; height: 20px; fill: white; }

        .brand-text { font-weight: 700; font-size: 0.9rem; line-height: 1.3; }
        .brand-text span { display: block; font-weight: 400; font-size: 0.75rem; color: var(--muted); }

        .sidebar-nav { padding: 1rem 0; flex: 1; }

        .nav-section-label {
            padding: 0.5rem 1.5rem;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            text-decoration: none;
        }

        .nav-item:hover, .nav-item.active {
            color: var(--text);
            background: rgba(255,255,255,0.04);
            border-left-color: var(--accent);
        }

        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
            flex-shrink: 0;
        }

        .user-name { font-size: 0.85rem; font-weight: 600; }
        .user-role { font-size: 0.72rem; color: var(--muted); }

        .btn-logout {
            width: 100%;
            padding: 8px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 8px;
            color: #ef4444;
            font-size: 0.82rem;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(239,68,68,0.2); }

        /* ===================== MAIN ===================== */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title { font-size: 1.1rem; font-weight: 700; }
        .topbar-title span { color: var(--muted); font-weight: 400; font-size: 0.85rem; margin-left: 8px; }

        .status-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.8rem;
            color: var(--muted);
        }

        .content { padding: 2rem; flex: 1; }

        /* ===================== TABS ===================== */
        .tab-container {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }

        .tabs {
            display: flex;
            gap: 8px;
        }

        .tab-btn {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: var(--text);
            background: rgba(255,255,255,0.06);
        }

        .tab-btn.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        select {
            background: var(--bg-panel);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.82rem;
            outline: none;
            cursor: pointer;
        }

        .btn-refresh {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-refresh:hover { background: rgba(255,255,255,0.1); }

        /* ===================== PANELS & TABLES ===================== */
        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            backdrop-filter: blur(12px);
        }

        .panel-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-title .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
        }

        .panel-body { padding: 0; }

        .table-wrap { overflow-x: auto; max-height: 600px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left; }
        th { padding: 14px 16px; color: var(--muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); position: sticky; top: 0; background: var(--bg-panel); z-index: 10; }
        td { padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); color: #e2e8f0; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .badge-gerakan {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-gerakan.aman { background: rgba(16,185,129,0.15); color: #34d399; }
        .badge-gerakan.jatuh { background: rgba(239,68,68,0.15); color: #f87171; }



        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--muted);
            font-size: 0.9rem;
        }
        .no-data .icon { font-size: 2.5rem; margin-bottom: 10px; }

        /* ===================== MENU TOGGLE ===================== */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            cursor: pointer;
            padding: 4px;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .menu-toggle svg {
            width: 24px;
            height: 24px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(5, 7, 15, 0.75);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 99;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 768px) {
            .menu-toggle { display: flex; }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 100;
                box-shadow: 20px 0 40px rgba(0,0,0,0.5);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: block;
                pointer-events: none;
            }
            .sidebar-overlay.active {
                pointer-events: auto;
            }
            .main { margin-left: 0; width: 100%; }
            .topbar { padding: 1rem 1.25rem; }
            .content { padding: 1.25rem; }
            .tab-container { flex-direction: column; gap: 15px; align-items: stretch; }
            .tabs {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 4px;
            }
            .tab-btn {
                white-space: nowrap;
            }
            .filter-controls {
                width: 100%;
                flex-wrap: wrap;
                justify-content: space-between;
            }
        }

        @media (max-width: 580px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .status-bar {
                width: 100%;
                justify-content: space-between;
            }
        }

        /* ===== LIGHT MODE OVERRIDES ===== */
        :root.light-mode body { background: var(--bg); }
        :root.light-mode .sidebar {
            box-shadow: 4px 0 24px rgba(0,0,0,0.18), 2px 0 8px rgba(0,0,0,0.10);
        }
        :root.light-mode .panel {
            box-shadow: var(--shadow-card);
            background: #ffffff;
            border-color: rgba(0,0,0,0.10);
        }
        :root.light-mode .nav-item:hover,
        :root.light-mode .nav-item.active {
            background: rgba(59,130,246,0.08);
        }
        :root.light-mode th {
            background: var(--bg-panel);
            color: var(--muted);
        }
        :root.light-mode td {
            border-bottom-color: rgba(0,0,0,0.06);
            color: var(--text);
        }
        :root.light-mode tr:hover td {
            background: rgba(59,130,246,0.04);
        }
        :root.light-mode select {
            background: var(--bg-panel);
            color: var(--text);
        }
        :root.light-mode .tab-btn {
            background: rgba(0,0,0,0.04);
            color: var(--muted);
        }
        :root.light-mode .tab-btn:hover {
            background: rgba(0,0,0,0.08);
            color: var(--text);
        }
        :root.light-mode .btn-refresh {
            background: rgba(0,0,0,0.05);
            color: var(--text);
        }
        :root.light-mode .btn-refresh:hover {
            background: rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- ==================== SIDEBAR ==================== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="brand">
                                    <div class="brand-icon" style="overflow: hidden; display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 8px; background: transparent;">
                <img src="data:image/png;base64,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" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="brand-text">Monitoring Pasien<span>IoT Realtime System</span></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu</div>
        <a class="nav-item" href="{{ route('dashboard') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a class="nav-item active" href="{{ route('history') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            History
        </a>
        <div class="nav-section-label" style="margin-top:10px;"></div>
        <!-- <a class="nav-item" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            GPS BN-220
        </a>
        <a class="nav-item" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
            Suhu MLX90614
        </a>
        <a class="nav-item" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            MPU6050
        </a> -->
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="user-role">{{ auth()->user()->email ?? '' }}</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Logout</button>
        </form>
    </div>
</aside>

<!-- ==================== MAIN ==================== -->
<main class="main">
    <div class="topbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle Menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="topbar-title">History <span>/ Riwayat Sensor Pasien</span></div>
        </div>
        <div class="status-bar">
            <span>Server Time: <span id="server-time">--:--:--</span></span>
            &nbsp;
            <button class="theme-toggle" id="theme-toggle-btn" aria-label="Toggle Dark/Light Mode">
                <div class="theme-toggle-track">
                    <div class="theme-toggle-thumb" id="theme-thumb">🌙</div>
                </div>
                <span id="theme-label">Dark</span>
            </button>
        </div>
    </div>

    <div class="content">

        <!-- ==================== TAB BAR & CONTROLS ==================== -->
        <div class="tab-container">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('gps')" id="tab-gps">🛰️ GPS BN-220</button>
                <button class="tab-btn" onclick="switchTab('mpu')" id="tab-mpu">🌀 MPU6050</button>
            </div>
            <div class="filter-controls" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div id="status-filter-container" style="display: none; align-items: center; gap: 6px;">
                    <label for="status-select" style="font-size:0.8rem;color:var(--muted);font-weight:600">Status:</label>
                    <select id="status-select" onchange="loadHistoryData()" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text); font-size: 0.85rem; font-weight: 500;">
                        <option value="ALL" selected>Semua Status</option>
                        <option value="JATUH">Hanya Jatuh</option>
                        <option value="AMAN">Hanya Aman</option>
                    </select>
                </div>
                <label for="limit-select" style="font-size:0.8rem;color:var(--muted);font-weight:600">Limit data:</label>
                <select id="limit-select" onchange="loadHistoryData()">
                    <option value="20">20 Data</option>
                    <option value="50" selected>50 Data</option>
                    <option value="100">100 Data</option>
                    <option value="200">200 Data</option>
                </select>
                <button class="btn-refresh" onclick="loadHistoryData()">
                    🔄 Refresh Data
                </button>
            </div>
        </div>

        <!-- ==================== GPS PANEL ==================== -->
        <div id="panel-gps" class="panel">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div class="panel-title">
                    <div class="dot" style="background:var(--accent)"></div>
                    Riwayat GPS BN-220
                </div>
                <div class="export-controls" style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size:0.8rem; color:var(--muted); font-weight:600;">📥 Unduh Laporan (Excel):</span>
                    <a href="{{ route('history.export') }}?type=gps&range=day" class="btn-refresh" style="text-decoration:none; display:inline-flex; align-items:center; font-size:0.75rem; padding:6px 10px; border-radius: 6px; font-weight: 500;">📅 Hari ini</a>
                    <a href="{{ route('history.export') }}?type=gps&range=week" class="btn-refresh" style="text-decoration:none; display:inline-flex; align-items:center; font-size:0.75rem; padding:6px 10px; border-radius: 6px; font-weight: 500;">📅 Minggu ini</a>
                    <a href="{{ route('history.export') }}?type=gps&range=month" class="btn-refresh" style="text-decoration:none; display:inline-flex; align-items:center; font-size:0.75rem; padding:6px 10px; border-radius: 6px; font-weight: 500;">📅 Bulan ini</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table id="table-gps">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Satelit</th>
                                <th>HDOP</th>
                                <th>Google Maps</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-gps">
                            <tr>
                                <td colspan="7" class="no-data"><div class="icon">🔄</div>Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <!-- ==================== MPU PANEL ==================== -->
        <div id="panel-mpu" class="panel" style="display:none">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div class="panel-title">
                    <div class="dot" style="background:var(--warning)"></div>
                    Riwayat Akselerometer MPU6050
                </div>
                <div class="export-controls" style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size:0.8rem; color:var(--muted); font-weight:600;">📥 Unduh Laporan (Excel):</span>
                    <a href="{{ route('history.export') }}?type=mpu&range=day" class="btn-refresh" style="text-decoration:none; display:inline-flex; align-items:center; font-size:0.75rem; padding:6px 10px; border-radius: 6px; font-weight: 500;">📅 Hari ini</a>
                    <a href="{{ route('history.export') }}?type=mpu&range=week" class="btn-refresh" style="text-decoration:none; display:inline-flex; align-items:center; font-size:0.75rem; padding:6px 10px; border-radius: 6px; font-weight: 500;">📅 Minggu ini</a>
                    <a href="{{ route('history.export') }}?type=mpu&range=month" class="btn-refresh" style="text-decoration:none; display:inline-flex; align-items:center; font-size:0.75rem; padding:6px 10px; border-radius: 6px; font-weight: 500;">📅 Bulan ini</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table id="table-mpu">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Acc X</th>
                                <th>Acc Y</th>
                                <th>Acc Z</th>
                                <th>Total Acc</th>
                                <th>Gerakan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-mpu">
                            <tr>
                                <td colspan="7" class="no-data"><div class="icon">🔄</div>Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    // Live Server Clock
    setInterval(() => {
        document.getElementById('server-time').textContent = new Date().toLocaleTimeString('id-ID');
    }, 1000);
    document.getElementById('server-time').textContent = new Date().toLocaleTimeString('id-ID');

    // Current active tab
    let activeTab = 'gps';

    // Switch between panels/tabs
    function switchTab(tabName) {
        activeTab = tabName;
        
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');

        // Show/hide panels
        document.getElementById('panel-gps').style.display  = tabName === 'gps' ? 'block' : 'none';
        document.getElementById('panel-mpu').style.display  = tabName === 'mpu' ? 'block' : 'none';

        // Show/hide status filter
        const statusFilter = document.getElementById('status-filter-container');
        if (statusFilter) {
            statusFilter.style.display = tabName === 'mpu' ? 'flex' : 'none';
        }

        loadHistoryData();
    }

    // Format timestamps to local ID timezone formatting
    function formatTime(isoString) {
        if (!isoString) return '–';
        const date = new Date(isoString);
        return date.toLocaleString('id-ID');
    }

    // Load History Data via AJAX
    async function loadHistoryData() {
        const limit = document.getElementById('limit-select').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        if (activeTab === 'gps') {
            const tbody = document.getElementById('tbody-gps');
            tbody.innerHTML = `<tr><td colspan="7" class="no-data"><div class="icon">🔄</div>Loading data GPS...</td></tr>`;
            try {
                const response = await fetch(`/gps-history/${limit}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (!response.ok) throw new Error('Status ' + response.status);
                const data = await response.json();
                
                if (!data || data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="no-data"><div class="icon">📭</div>Tidak ada data GPS dalam database.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.map((row, index) => {
                    const mapsLink = row.mapsUrl 
                        ? `<a href="${row.mapsUrl}" target="_blank" style="color:var(--accent);text-decoration:none;font-weight:600">🗺️ Buka Maps</a>`
                        : '–';
                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${row.latitude?.toFixed(6) ?? '–'}</td>
                            <td>${row.longitude?.toFixed(6) ?? '–'}</td>
                            <td>${row.satelit ?? '–'}</td>
                            <td>${row.hdop?.toFixed(2) ?? '–'}</td>
                            <td>${mapsLink}</td>
                            <td>${formatTime(row.timestamp)}</td>
                        </tr>
                    `;
                }).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="7" class="no-data" style="color:var(--danger)"><div class="icon">⚠️</div>Gagal memuat data: ${e.message}</td></tr>`;
            }
        } 
        else if (activeTab === 'mpu') {
            const tbody = document.getElementById('tbody-mpu');
            tbody.innerHTML = `<tr><td colspan="7" class="no-data"><div class="icon">🔄</div>Loading data MPU...</td></tr>`;
            try {
                const response = await fetch(`/mpu-history/${limit}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (!response.ok) throw new Error('Status ' + response.status);
                let data = await response.json();

                // Client-side filtering by gerakan status
                const statusVal = document.getElementById('status-select').value;
                if (statusVal !== 'ALL') {
                    data = data.filter(row => {
                        const ax = parseFloat(row.accX ?? 0);
                        const ay = parseFloat(row.accY ?? 0);
                        const az = parseFloat(row.accZ ?? 0);
                        const total = Math.abs(ax) + Math.abs(ay) + Math.abs(az);
                        
                        // Menentukan apakah JATUH
                        const isJatuh = row.gerakan === 'JATUH' || row.gerakan === 'BERGERAK' || total > 2.50;
                        return statusVal === 'JATUH' ? isJatuh : !isJatuh;
                    });
                }

                if (!data || data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="no-data"><div class="icon">📭</div>Tidak ada data MPU dalam database yang cocok.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.map((row, index) => {
                    const ax = parseFloat(row.accX ?? 0);
                    const ay = parseFloat(row.accY ?? 0);
                    const az = parseFloat(row.accZ ?? 0);
                    const total = Math.abs(ax) + Math.abs(ay) + Math.abs(az);
                    
                    const isJatuh = row.gerakan === 'JATUH' || row.gerakan === 'BERGERAK' || total > 2.50;
                    const badgeClass = isJatuh ? 'jatuh' : 'aman';
                    const label = isJatuh ? '🚨 JATUH' : '✅ AMAN';

                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${ax.toFixed(4)}</td>
                            <td>${ay.toFixed(4)}</td>
                            <td>${az.toFixed(4)}</td>
                            <td style="font-weight:600">${total.toFixed(4)}</td>
                            <td><span class="badge-gerakan ${badgeClass}">${label}</span></td>
                            <td>${formatTime(row.timestamp)}</td>
                        </tr>
                    `;
                }).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="7" class="no-data" style="color:var(--danger)"><div class="icon">⚠️</div>Gagal memuat data: ${e.message}</td></tr>`;
            }
        }
    }

    // Initial load on page show
    document.addEventListener('DOMContentLoaded', () => {
        loadHistoryData();
    });

    // Menu Toggle JS for sliding sidebar drawer
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (menuToggle && sidebar && sidebarOverlay) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('active');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });
    }

    // ============================================================
    // DARK / LIGHT MODE TOGGLE
    // ============================================================
    (function() {
        const root  = document.documentElement;
        const btn   = document.getElementById('theme-toggle-btn');
        const thumb = document.getElementById('theme-thumb');
        const label = document.getElementById('theme-label');

        function applyTheme(mode) {
            if (mode === 'light') {
                root.classList.add('light-mode');
                thumb.textContent = '☀️';
                label.textContent = 'Light';
            } else {
                root.classList.remove('light-mode');
                thumb.textContent = '🌙';
                label.textContent = 'Dark';
            }
            localStorage.setItem('theme', mode);
        }

        // Muat tema tersimpan atau deteksi sistem
        const saved = localStorage.getItem('theme');
        const preferLight = window.matchMedia('(prefers-color-scheme: light)').matches;
        applyTheme(saved ?? (preferLight ? 'light' : 'dark'));

        btn.addEventListener('click', () => {
            const isLight = root.classList.contains('light-mode');
            applyTheme(isLight ? 'dark' : 'light');
        });
    })();
</script>
</body>
</html>

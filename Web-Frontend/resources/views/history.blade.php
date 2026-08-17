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
                <img src="/logo-pasien.png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
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
            <div class="filter-controls">
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
            <div class="panel-header">
                <div class="panel-title">
                    <div class="dot" style="background:var(--accent)"></div>
                    Riwayat GPS BN-220
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
            <div class="panel-header">
                <div class="panel-title">
                    <div class="dot" style="background:var(--warning)"></div>
                    Riwayat Akselerometer MPU6050
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
                const data = await response.json();

                if (!data || data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="no-data"><div class="icon">📭</div>Tidak ada data MPU dalam database.</td></tr>`;
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

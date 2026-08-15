<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Monitoring Pasien IoT</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- MapLibre GL JS -->
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

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

        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--success);
            display: inline-block;
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        .content { padding: 2rem; flex: 1; }

        /* ===================== CARDS ===================== */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.25rem;
            backdrop-filter: blur(12px);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--card-accent, var(--accent));
            border-radius: 14px 14px 0 0;
        }

        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }

        .stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 8px; font-weight: 600; }

        .stat-value { font-size: 1.6rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 4px; }

        .stat-sub { font-size: 0.75rem; color: var(--muted); }

        .stat-icon {
            position: absolute;
            right: 1rem; top: 1rem;
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            opacity: 0.2;
        }
        .stat-icon svg { width: 22px; height: 22px; }

        /* ===================== PANELS ===================== */
        .panel-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .panel-row-3 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-title .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
        }

        .panel-badge {
            font-size: 0.72rem;
            padding: 3px 8px;
            border-radius: 20px;
            background: rgba(59,130,246,0.15);
            color: var(--accent);
            font-weight: 600;
        }

        .panel-body { padding: 1.25rem; }

        /* ===================== MAP ===================== */
        #map {
            height: 320px;
            border-radius: 10px;
            overflow: hidden;
            width: 100%;
        }

        .btn-map-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-map-control:hover {
            background: rgba(255,255,255,0.1);
        }
        .btn-map-control.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .panel-maximized {
            position: fixed !important;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            z-index: 1000;
            background: var(--bg-panel) !important;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
            border: 1px solid var(--border);
        }
        .panel-maximized #map {
            height: calc(100vh - 120px) !important;
        }

        .custom-marker {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 32px;
            height: 32px;
            background-color: white;
            border: 2px solid #ef4444;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            cursor: pointer;
        }
        .custom-marker span {
            font-size: 16px;
        }

        /* ===================== GPS INFO ===================== */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .info-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
        }

        .info-item-label { font-size: 0.72rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-item-value { font-size: 0.95rem; font-weight: 700; }

        /* ===================== TEMPERATURE GAUGE ===================== */
        .temp-gauge-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0;
        }

        .gauge-svg { width: 200px; height: 120px; }

        .temp-display { text-align: center; margin-top: -10px; }
        .temp-val { font-size: 3rem; font-weight: 800; letter-spacing: -2px; }
        .temp-unit { font-size: 1rem; color: var(--muted); }
        .temp-status {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* ===================== MPU BARS ===================== */
        .mpu-bars { display: flex; flex-direction: column; gap: 12px; }

        .mpu-bar-item {}
        .mpu-bar-label {
            display: flex; justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 6px;
        }
        .mpu-bar-label span:first-child { font-weight: 600; }
        .mpu-bar-label span:last-child { color: var(--muted); }

        .bar-track {
            height: 8px;
            background: rgba(255,255,255,0.05);
            border-radius: 99px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.5s cubic-bezier(0.34,1.56,0.64,1);
        }

        /* ===================== STATUS PASIEN ===================== */
        .status-pasien {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            gap: 1rem;
        }

        .status-ring {
            width: 100px; height: 100px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem;
            border: 3px solid;
            position: relative;
        }

        .status-ring.bergerak {
            border-color: var(--danger);
            box-shadow: 0 0 30px rgba(239,68,68,0.3);
            animation: shake 0.5s ease-in-out infinite;
        }

        .status-ring.diam {
            border-color: var(--success);
            box-shadow: 0 0 30px rgba(16,185,129,0.3);
        }

        @keyframes shake {
            0%, 100% { transform: rotate(-3deg); }
            50%       { transform: rotate(3deg); }
        }

        .status-text { font-size: 1.4rem; font-weight: 800; }
        .status-text.bergerak { color: var(--danger); }
        .status-text.diam     { color: var(--success); }
        .status-desc { font-size: 0.8rem; color: var(--muted); text-align: center; }

        /* ===================== TIMESTAMP ===================== */
        .last-update {
            font-size: 0.75rem;
            color: var(--muted);
            text-align: center;
            margin-top: 8px;
        }

        /* ===================== TABLE ===================== */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        th { padding: 10px 12px; text-align: left; color: var(--muted); font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        td { padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        /* ===================== ALERT ===================== */
        .no-data {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-size: 0.85rem;
        }
        .no-data .icon { font-size: 2rem; margin-bottom: 8px; }

        /* ===================== PANEL THREE COLUMNS ===================== */
        .panel-row-three-cols {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

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
        @media (max-width: 1100px) {
            .stat-row { grid-template-columns: repeat(2, 1fr); }
            .panel-row, .panel-row-3, .panel-row-three-cols { grid-template-columns: 1fr; }
        }

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
        }

        @media (max-width: 600px) {
            .btn-map-control .btn-text { display: none; }
            .btn-map-control { padding: 6px 8px; font-size: 1rem; }
            #map-badge { display: none; }
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

        @media (max-width: 480px) {
            .stat-row { grid-template-columns: 1fr; }
            .stat-card { padding: 1rem; }
            .stat-value { font-size: 1.4rem; }
        }

        /* ===== LIGHT MODE OVERRIDES ===== */
        :root.light-mode body {
            background: var(--bg);
        }
        :root.light-mode .sidebar {
            box-shadow: 4px 0 24px rgba(0,0,0,0.18), 2px 0 8px rgba(0,0,0,0.10);
        }
        :root.light-mode .stat-card {
            box-shadow: var(--shadow-card);
            background: #ffffff;
            border-color: rgba(0,0,0,0.10);
        }
        :root.light-mode .stat-card:hover {
            box-shadow: 0 12px 40px rgba(0,0,0,0.22), 0 4px 12px rgba(0,0,0,0.12);
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
        :root.light-mode .bar-track {
            background: rgba(0,0,0,0.07);
        }
        :root.light-mode .info-item {
            background: rgba(0,0,0,0.03);
        }
        :root.light-mode .btn-map-control {
            background: rgba(0,0,0,0.04);
            color: var(--text);
        }
        :root.light-mode .btn-map-control:hover {
            background: rgba(0,0,0,0.08);
        }

        /* ===================== MAPBOX/MAPLIBRE BACK ===================== */
        .mapboxgl-map, .maplibregl-map { background: #0f1525; }

        /* ===================== TOAST NOTIFIKASI ===================== */
        #toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 18px;
            min-width: 300px;
            max-width: 380px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            pointer-events: auto;
            animation: toastIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;
            position: relative;
            overflow: hidden;
        }
        .toast::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 14px 0 0 14px;
        }
        .toast.toast-danger::before  { background: var(--danger); }
        .toast.toast-success::before { background: var(--success); }
        .toast.toast-info::before    { background: var(--accent); }

        .toast-icon {
            font-size: 1.4rem;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .toast-body { flex: 1; }
        .toast-title {
            font-weight: 700;
            font-size: 0.88rem;
            margin-bottom: 4px;
        }
        .toast-msg {
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.4;
        }
        .toast-close {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            line-height: 1;
            flex-shrink: 0;
            align-self: flex-start;
        }
        .toast-close:hover { color: var(--text); }

        .toast.toast-hide {
            animation: toastOut 0.3s ease forwards;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(50px) scale(0.95); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0); max-height: 200px; margin-bottom: 0; }
            to   { opacity: 0; transform: translateX(60px); max-height: 0; margin-bottom: -10px; }
        }

        /* Notif badge di topbar */
        .notif-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(239,68,68,0.15);
            color: var(--danger);
            border: 1px solid rgba(239,68,68,0.3);
            animation: pulseBadge 1.5s ease-in-out infinite;
        }
        .notif-badge .dot-red {
            width: 7px; height: 7px;
            background: var(--danger);
            border-radius: 50%;
            animation: blink 1s ease-in-out infinite;
        }
        @keyframes pulseBadge {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.3); }
            50%       { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- ==================== TOAST CONTAINER ==================== -->
    <div id="toast-container"></div>

<!-- ==================== SIDEBAR ==================== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="brand">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7zm0 9.5A2.5 2.5 0 1 0 12 6.5a2.5 2.5 0 0 0 0 5z"/></svg>
            </div>
            <div class="brand-text">Monitoring Pasien<span>IoT Realtime System</span></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu</div>
        <a class="nav-item active" href="{{ route('dashboard') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a class="nav-item" href="{{ route('history') }}">
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
            <div class="topbar-title">Dashboard <span>/ Monitoring Real-Time</span></div>
        </div>
        <div class="status-bar">
            <span class="status-dot"></span>
            <span id="live-clock">--:--:--</span>
            &nbsp;|&nbsp;
            Auto-refresh: <span id="countdown">2s</span>
            &nbsp;
            <span id="fall-alert-badge" style="display:none" class="notif-badge">
                <span class="dot-red"></span> JATUH TERDETEKSI
            </span>
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

        <!-- ===== STAT CARDS ===== -->
        <div class="stat-row">
            <div class="stat-card" style="--card-accent:#3b82f6">
                <div class="stat-icon" style="background:#3b82f6">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/></svg>
                </div>
                <div class="stat-label">Latitude</div>
                <div class="stat-value" id="stat-lat" style="color:#60a5fa;font-size:1.1rem">–</div>
                <div class="stat-sub" id="stat-lng">Longitude: –</div>
            </div>

            <div class="stat-card" style="--card-accent:#f59e0b">
                <div class="stat-icon" style="background:#f59e0b">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="stat-label">Status Jatuh</div>
                <div class="stat-value" id="stat-gerakan" style="font-size:1rem">–</div>
                <div class="stat-sub" id="stat-acc-total">Total Acc: –</div>
            </div>
            <div class="stat-card" style="--card-accent:#8b5cf6">
                <div class="stat-icon" style="background:#8b5cf6">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div class="stat-label">Satelit GPS</div>
                <div class="stat-value" id="stat-satelit" style="color:#a78bfa">–</div>
                <div class="stat-sub" id="stat-hdop">HDOP: –</div>
            </div>
        </div>

        <!-- ===== GPS MAP + INFO ===== -->
        <div class="panel-row-3">
            <div class="panel" id="map-panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <div class="dot" style="background:#3b82f6"></div>
                        Peta Lokasi GPS (BN-220)
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button id="btn-focus-patient" class="btn-map-control" title="Pusatkan ke Lokasi Pasien">
                            <span>🎯</span><span class="btn-text"> Pusatkan</span>
                        </button>
                        <button id="btn-toggle-center" class="btn-map-control" title="Ikuti GPS Otomatis">
                            <span>📍</span><span class="btn-text"> Ikuti GPS</span>
                        </button>
                        <button id="btn-toggle-3d" class="btn-map-control active" title="Ubah ke 2D/3D">
                            <span>🌐</span><span class="btn-text"> 3D Mode</span>
                        </button>
                        <button id="btn-toggle-size" class="btn-map-control" title="Perbesar Peta">
                            <span>🔍</span><span class="btn-text"> Perbesar</span>
                        </button>
                        <div class="panel-badge" id="map-badge">Menunggu GPS...</div>
                    </div>
                </div>
                <div class="panel-body" style="padding:0.75rem">
                    <div id="map"></div>
                    <div class="last-update" id="gps-update">Belum ada data GPS</div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <div class="dot" style="background:#3b82f6"></div>
                        Detail GPS
                    </div>
                </div>
                <div class="panel-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-item-label">Latitude</div>
                            <div class="info-item-value" id="info-lat" style="color:#60a5fa">–</div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Longitude</div>
                            <div class="info-item-value" id="info-lng" style="color:#60a5fa">–</div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">Satelit</div>
                            <div class="info-item-value" id="info-satelit" style="color:#a78bfa">–</div>
                        </div>
                        <div class="info-item">
                            <div class="info-item-label">HDOP</div>
                            <div class="info-item-value" id="info-hdop" style="color:#a78bfa">–</div>
                        </div>
                    </div>
                    <div style="margin-top:12px">
                        <div class="info-item" style="text-align:center">
                            <div class="info-item-label">Google Maps</div>
                            <div id="info-maps">
                                <a id="maps-link" href="#" target="_blank"
                                   style="color:#60a5fa;font-size:0.82rem;text-decoration:none;font-weight:600">
                                    🗺️ Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== SUHU + MPU + STATUS ===== -->
        <div class="panel-row-three-cols">


            <!-- MPU Bars -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <div class="dot" style="background:#f59e0b"></div>
                        Akselerometer (MPU6050)
                    </div>
                </div>
                <div class="panel-body">
                    <div class="mpu-bars">
                        <div class="mpu-bar-item">
                            <div class="mpu-bar-label">
                                <span>Acc X</span>
                                <span id="acc-x-val">0.0000</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" id="bar-x" style="width:50%;background:linear-gradient(90deg,#3b82f6,#60a5fa)"></div>
                            </div>
                        </div>
                        <div class="mpu-bar-item">
                            <div class="mpu-bar-label">
                                <span>Acc Y</span>
                                <span id="acc-y-val">0.0000</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" id="bar-y" style="width:50%;background:linear-gradient(90deg,#8b5cf6,#a78bfa)"></div>
                            </div>
                        </div>
                        <div class="mpu-bar-item">
                            <div class="mpu-bar-label">
                                <span>Acc Z</span>
                                <span id="acc-z-val">0.0000</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" id="bar-z" style="width:50%;background:linear-gradient(90deg,#06b6d4,#67e8f9)"></div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border)">
                        <div class="mpu-bar-label" style="margin-bottom:6px">
                            <span>Total Akselerasi</span>
                            <span id="acc-total-val">0.0000</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" id="bar-total" style="width:30%;background:linear-gradient(90deg,#f59e0b,#fcd34d)"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--muted);margin-top:4px">
                            <span>0</span><span>Threshold: 2.50</span><span>3.0</span>
                        </div>
                    </div>
                    <div class="last-update" id="mpu-update">Belum ada data MPU</div>
                </div>
            </div>

            <!-- Status Pasien -->
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">
                        <div class="dot" style="background:#f59e0b"></div>
                        Status Pasien
                    </div>
                </div>
                <div class="panel-body">
                    <div class="status-pasien">
                        <div class="status-ring diam" id="status-ring">✅</div>
                        <div class="status-text diam" id="status-text">AMAN</div>
                        <div class="status-desc" id="status-desc">Pasien dalam kondisi aman</div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- end .content -->
</main>

<script>
// ============================================================
// PETA MAPLIBRE (3D & PITCH FIXED)
// ============================================================
// Ambil API Key MapTiler jika ada di .env
const MAPTILER_KEY = "{{ env('MAPTILER_API_KEY', '') }}";

// Gunakan style CartoDB Positron gratis (tanpa key) sebagai default
// Jika user memasukkan key MapTiler, gunakan style MapTiler Streets
let mapStyle = 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
if (MAPTILER_KEY) {
    mapStyle = `https://api.maptiler.com/maps/streets-v2/style.json?key=${MAPTILER_KEY}`;
}

const defaultCoords = [100.427054, -0.932273]; // [lng, lat]
let currentCoords = defaultCoords;

const map = new maplibregl.Map({
    container: 'map',
    style: mapStyle,
    center: defaultCoords,
    zoom: 16,
    pitch: 45, // Kemiringan awal 3D
    bearing: -17,
    antialias: true
});

// Tambah kontrol navigasi (Zoom & Kompas/Tilt)
map.addControl(new maplibregl.NavigationControl(), 'top-right');

// Custom marker mobil
const markerEl = document.createElement('div');
markerEl.className = 'custom-marker';
markerEl.innerHTML = '<span>🚗</span>';

let marker = new maplibregl.Marker(markerEl)
    .setLngLat(defaultCoords)
    .addTo(map);

// Popup marker
const popup = new maplibregl.Popup({ offset: 25 })
    .setHTML('<b>Posisi Pasien</b>');
marker.setPopup(popup);

// Tambah layer gedung 3D ketika map dimuat
map.on('load', () => {
    // Deteksi source vector yang tersedia secara dinamis
    const sources = map.getStyle().sources;
    let sourceName = 'openmaptiles';
    
    if (sources.cartodb) {
        sourceName = 'cartodb';
    } else if (sources.composite) {
        sourceName = 'composite';
    } else if (sources.maptiler_planet) {
        sourceName = 'maptiler_planet';
    } else {
        // Cari source type 'vector' pertama
        for (let key in sources) {
            if (sources[key].type === 'vector') {
                sourceName = key;
                break;
            }
        }
    }

    map.addLayer(
        {
            'id': '3d-buildings',
            'source': sourceName,
            'source-layer': 'building',
            'filter': ['==', 'extrude', 'true'],
            'type': 'fill-extrusion',
            'minzoom': 15,
            'paint': {
                'fill-extrusion-color': '#aaa',
                'fill-extrusion-height': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    15,
                    0,
                    15.05,
                    ['coalesce', ['get', 'render_height'], ['get', 'height'], 10]
                ],
                'fill-extrusion-base': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    15,
                    0,
                    15.05,
                    ['coalesce', ['get', 'render_min_height'], ['get', 'min_height'], 0]
                ],
                'fill-extrusion-opacity': 0.6
            }
        }
    );
});

// State kontrol peta
let isAutoCenter = false; // Bawaan mati agar pengguna bisa menjelajahi peta tanpa digeser otomatis
let is3D = true;          // Bawaan 3D aktif
let isMaximized = false;
let firstGpsLoad = true;

// Tombol Pusatkan ke Pasien
document.getElementById('btn-focus-patient').addEventListener('click', function() {
    map.flyTo({
        center: currentCoords,
        zoom: 17,
        essential: true
    });
});

// Toggle Auto Center
document.getElementById('btn-toggle-center').addEventListener('click', function() {
    isAutoCenter = !isAutoCenter;
    this.classList.toggle('active', isAutoCenter);
});

// Toggle 2D / 3D Mode
document.getElementById('btn-toggle-3d').addEventListener('click', function() {
    is3D = !is3D;
    this.classList.toggle('active', is3D);
    const btnText = this.querySelector('.btn-text');
    if (btnText) {
        btnText.textContent = is3D ? ' 3D Mode' : ' 2D Mode';
    } else {
        this.innerHTML = is3D ? '<span>🌐</span> 3D Mode' : '<span>🗺️</span> 2D Mode';
    }
    
    // Sesuaikan kemiringan peta
    map.easeTo({
        pitch: is3D ? 45 : 0,
        bearing: is3D ? -17 : 0,
        duration: 800
    });
    
    // Tampilkan / sembunyikan gedung 3D
    if (map.getLayer('3d-buildings')) {
        map.setLayoutProperty('3d-buildings', 'visibility', is3D ? 'visible' : 'none');
    }
});

// Toggle Perbesar Map
document.getElementById('btn-toggle-size').addEventListener('click', function() {
    isMaximized = !isMaximized;
    const panel = document.getElementById('map-panel');
    panel.classList.toggle('panel-maximized', isMaximized);
    this.classList.toggle('active', isMaximized);
    const btnText = this.querySelector('.btn-text');
    if (btnText) {
        btnText.textContent = isMaximized ? ' Perkecil' : ' Perbesar';
    } else {
        this.innerHTML = isMaximized ? '<span>🗗</span> Perkecil' : '<span>🔍</span> Perbesar';
    }
    
    setTimeout(() => {
        map.resize();
    }, 200);
});

// ============================================================
// CLOCK
// ============================================================
function updateClock() {
    document.getElementById('live-clock').textContent =
        new Date().toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000);
updateClock();



// ============================================================
// STATE NOTIFIKASI
// ============================================================
let lastGpsData       = null;   // Menyimpan data GPS terbaru untuk notif
let fallNotifSent     = false;  // Apakah notif jatuh sudah dikirim (cooldown lokal)
let fallNotifCooldown = null;   // Timer cooldown (60 detik)
let lastStatusJatuh   = false;  // Status jatuh sebelumnya (untuk deteksi perubahan)

// ============================================================
// UPDATE UI
// ============================================================
function updateGps(d) {
    if (!d) return;
    // Simpan data GPS terbaru untuk digunakan oleh notifikasi jatuh
    lastGpsData = d;

    const lat = d.latitude?.toFixed(6) ?? '–';
    const lng = d.longitude?.toFixed(6) ?? '–';
    const sat = d.satelit ?? '–';
    const hdop = d.hdop?.toFixed(2) ?? '–';
    const url  = d.mapsUrl ?? '#';

    document.getElementById('stat-lat').textContent     = lat;
    document.getElementById('stat-lng').textContent     = 'Longitude: ' + lng;
    document.getElementById('stat-satelit').textContent = sat;
    document.getElementById('stat-hdop').textContent    = 'HDOP: ' + hdop;

    document.getElementById('info-lat').textContent     = lat;
    document.getElementById('info-lng').textContent     = lng;
    document.getElementById('info-satelit').textContent = sat;
    document.getElementById('info-hdop').textContent    = hdop;
    document.getElementById('maps-link').href           = url;
    document.getElementById('map-badge').textContent    = `${sat} satelit`;

    if (d.latitude && d.longitude) {
        currentCoords = [d.longitude, d.latitude]; // [lng, lat]
        
        marker.setLngLat(currentCoords);
        marker.getPopup().setHTML(`<b>Posisi Pasien</b><br>Lat: ${lat}<br>Lng: ${lng}`);

        if (isAutoCenter || firstGpsLoad) {
            map.easeTo({
                center: currentCoords
            });
            firstGpsLoad = false;
        }
    }

    const ts = d.timestamp ? new Date(d.timestamp).toLocaleString('id-ID') : '–';
    document.getElementById('gps-update').textContent = 'Update: ' + ts;
}



function updateMpu(d) {
    if (!d) return;
    const ax = parseFloat(d.accX ?? 0);
    const ay = parseFloat(d.accY ?? 0);
    const az = parseFloat(d.accZ ?? 0);
    const total = Math.abs(ax) + Math.abs(ay) + Math.abs(az);
    const rawGerakan = d.gerakan ?? (total > 2.50 ? 'JATUH' : 'AMAN');
    const isJatuh = (rawGerakan === 'JATUH' || rawGerakan === 'BERGERAK');
    const statusText = isJatuh ? 'JATUH' : 'AMAN';

    document.getElementById('acc-x-val').textContent = ax.toFixed(4);
    document.getElementById('acc-y-val').textContent = ay.toFixed(4);
    document.getElementById('acc-z-val').textContent = az.toFixed(4);
    document.getElementById('acc-total-val').textContent = total.toFixed(4);

    // Bar widths: map -2..2 → 0..100%
    const toBar = v => ((v + 2) / 4 * 100).toFixed(1) + '%';
    document.getElementById('bar-x').style.width = toBar(ax);
    document.getElementById('bar-y').style.width = toBar(ay);
    document.getElementById('bar-z').style.width = toBar(az);

    // Total bar: 0..3 → 0..100%
    document.getElementById('bar-total').style.width = Math.min((total / 3) * 100, 100).toFixed(1) + '%';

    // Stat card
    document.getElementById('stat-gerakan').textContent    = statusText;
    document.getElementById('stat-acc-total').textContent  = 'Total Acc: ' + total.toFixed(4);

    // Status ring
    const ring = document.getElementById('status-ring');
    const txt  = document.getElementById('status-text');
    const desc = document.getElementById('status-desc');
    const badge = document.getElementById('fall-alert-badge');

    if (isJatuh) {
        ring.className    = 'status-ring bergerak';
        ring.textContent  = '🚨';
        txt.className     = 'status-text bergerak';
        txt.textContent   = 'JATUH';
        desc.textContent  = 'Pasien terdeteksi JATUH!';
        badge.style.display = 'inline-flex';

        // Kirim notifikasi Telegram hanya saat status BARU berubah jadi JATUH
        // dan cooldown lokal belum aktif
        if (!lastStatusJatuh && !fallNotifSent) {
            triggerFallNotification(total);
        }
        lastStatusJatuh = true;
    } else {
        ring.className    = 'status-ring diam';
        ring.textContent  = '✅';
        txt.className     = 'status-text diam';
        txt.textContent   = 'AMAN';
        desc.textContent  = 'Pasien dalam kondisi aman';
        badge.style.display = 'none';
        lastStatusJatuh = false;
    }

    const ts = d?.timestamp ? new Date(d.timestamp).toLocaleString('id-ID') : '–';
    document.getElementById('mpu-update').textContent = 'Update: ' + ts;
}

// ============================================================
// TRIGGER NOTIFIKASI JATUH
// ============================================================
function triggerFallNotification(totalAcc) {
    // Set cooldown lokal 60 detik agar tidak spam
    fallNotifSent = true;
    clearTimeout(fallNotifCooldown);
    fallNotifCooldown = setTimeout(() => { fallNotifSent = false; }, 60000);

    // Ambil data GPS saat ini
    const lat     = lastGpsData?.latitude  ?? null;
    const lng     = lastGpsData?.longitude ?? null;
    const mapsUrl = lastGpsData?.mapsUrl   ?? '';

    // Tampilkan toast di UI dulu (instant)
    const locStr = (lat && lng)
        ? `${lat.toFixed(6)}, ${lng.toFixed(6)}`
        : 'GPS belum terkunci';
    showToast('danger', '🚨 JATUH TERDETEKSI!',
        `Lokasi: ${locStr}. Mengirim notifikasi ke Telegram...`, 8000);

    // Kirim ke server → Telegram
    sendTelegramFallAlert(lat, lng, mapsUrl, totalAcc);
}

// ============================================================
// KIRIM NOTIFIKASI KE TELEGRAM (via Laravel proxy -> NotifTele-Service)
// ============================================================
async function sendTelegramFallAlert(lat, lng, mapsUrl, totalAcc) {
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res  = await fetch('{{ route("notify.fall") }}', {
            method:  'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ lat, lng, mapsUrl, totalAcc }),
        });
        const data = await res.json();

        if (data.success) {
            showToast('success', '✅ Notifikasi Terkirim!',
                'Pesan JATUH berhasil dikirim ke Telegram bot.', 6000);
        } else {
            console.warn('[NotifTele] Tidak terkirim:', data.message);
            if (!data.message?.includes('Cooldown')) {
                showToast('info', '⚠️ Notifikasi Gagal',
                    data.message ?? 'Gagal mengirim notifikasi Telegram.', 5000);
            }
        }
    } catch (e) {
        console.error('[NotifTele] Fetch error:', e);
        showToast('info', '⚠️ Koneksi Error',
            'Gagal menghubungi NotifTele-Service untuk notifikasi.', 4000);
    }
}

// ============================================================
// TOAST NOTIFICATION HELPER
// ============================================================
function showToast(type, title, message, duration = 5000) {
    const container = document.getElementById('toast-container');

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${
            type === 'danger'  ? '🚨' :
            type === 'success' ? '✅' : 'ℹ️'
        }</div>
        <div class="toast-body">
            <div class="toast-title">${title}</div>
            <div class="toast-msg">${message}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.parentElement)">✕</button>
    `;

    container.appendChild(toast);

    // Auto dismiss
    setTimeout(() => dismissToast(toast), duration);
}

function dismissToast(toast) {
    if (!toast || toast.classList.contains('toast-hide')) return;
    toast.classList.add('toast-hide');
    setTimeout(() => toast.remove(), 350);
}

// ============================================================
// POLLING REAL-TIME
// ============================================================
let countdown = 1;

async function fetchData() {
    try {
        const res = await fetch('{{ route("sensor.data") }}?t=' + Date.now(), {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        updateGps(data.gps);
        updateMpu(data.mpu);
    } catch (e) {
        console.warn('Fetch error:', e.message);
    }
}

// Countdown display
setInterval(() => {
    countdown--;
    if (countdown <= 0) {
        countdown = 1;
        fetchData();
    }
    document.getElementById('countdown').textContent = countdown + 's';
}, 1000);

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
    const root    = document.documentElement;
    const btn     = document.getElementById('theme-toggle-btn');
    const thumb   = document.getElementById('theme-thumb');
    const label   = document.getElementById('theme-label');

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

// Initial load
fetchData();
</script>
</body>
</html>

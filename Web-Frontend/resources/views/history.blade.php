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
                <img src="data:image/png;base64,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SdN+Ft57OeauSqeZWvPKj/DWjJfMApI1q/CsMZ/+9mw/bnqajRftfpmWlidv+yo2bg3XHWS9d9eXsdTe51sXlE//oWf59D2/wMrpP8PLj//TkvJRVJDAW3+9Fo9Yn1ee+QfGThqFTVF7Iw9gzt1fxqL1ZqQ/5jzwb1i4LPgxf6TWY8HDtmere+yW67A1loYfex0v32bX1PxP//0nSCJ8RDDz7qsx7fdfwdYaYN3TdgDyr6afVNjw2k/tvrwMj95yLVatXILInJvwhN2Xj/zucrz1+jNhE2kREAEREAEREIEcIaADgBy50NqmCIjAzgl0euSgr+HqX7+FC781Def95yu45JuX4KVfn+V/jL7T51LDXkhgLE780XRcdePzOOur03DVr99ASewVzJ09sxeuVUsSAREQAREQARHIJQI6AMilq629ioAItEag832zb8ZdN16Al/7+Lbx2x+W46/snYemWROfPo469lMAavPTfJ+OhP1yLfz3wLTz48zNx760/RCrVS5erZYmACIiACIiACOQMAR0A5Myl1kZFQARaJ9AV3nrURdeicssqk9Woq63qiknUsxcTSNZvQWwrr/8qVEU2Ipls+qH9XrxsLU0EREAEREAERGAPJ6ADgD38Amt7IiACH0JAYREQAREQAREQAREQARHIEQI6AMiRC61tioAItE5AXhEQAREQAREQAREQARHIFQI6AMiVK619ioAItEZAPhEQAREQAREQAREQARHIGQI6AMiZS62NioAI7EhAHhEQAREQAREQAREQARHIHQI6AMida62dioAItCSgsQiIgAiIgAiIgAiIgAjkEAEdAOTQxdZWRUAEmhPQSAREQAREQAREQAREQARyiYAOAHLpamuvIiAC2QRki4AIiIAIiIAIiIAIiEBOEdABQE5dbm1WBESgiYAsERABERABERABERABEcgtAjoAyK3rrd2KgAiEBKSNujKTAAAQAElEQVRFQAREQAREQAREQAREIMcI6AAgxy64tisCIhAQ0LMIiIAIiIAIiIAIiIAI5BoBHQDk2hXXfkVABEhAIgIiIAIiIAIiIAIiIAI5R0AHADl3ybVhERABQAxEQAREQAREQAREQAREIPcI6AAg9665diwCIiACIiACIiACIiACIiACIpCDBHQAkIMXXVsWgVwnoP2LgAiIgAiIgAiIgAiIQC4S0AFA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n3Gqr1swdy7C96JzZryI+F4Tkdxeg3nzlvs4n158+TWUjt7xu/qoXILbbnsWhxx3qu/5iaMOQM1WoHr+67j9/k045axPe3/ewufx7GtL2Qq1sVVYvq7a23x698GnMGv2PFTZ4UC8OoKFS9eCfsqWBQ9h1jvvIhKrteE2rFgR9f24/rOO3898wJO334LqssMC/2mfREF9+JMNwII5C9Hgs+ypxnovWW0G0LjiHdx693Icf+rZvm7Ihhl47Nk5PtZQtwaLVld5m0/zn3oe78yZh2g9Ry2lAosXr0s7t+GNf76EqYccDGxahPj4T/reXOu4EXkonXqoH59++sl4/Kabsa0hgfpNK9Ewel/vP+vcczBlWLpV9SosXdviNCIdkhIBERABERABERABEcgNAjoAyI3rrF2KQJcRWPLW83j6ice8PPfsC36eV03XhO+SI2vw/EtvIr6tEi+88HbmN+W3LXsXr8+pwIQpUzAplEmT4Pw3+Cvw6lNBz6efeA72/t/3TcQX4tn0XPM2lnsfnyq3vIe3F2T/ScE1eOmJp1Fu7/G3l6/D6zM+YFpG3nj+CazbwjfkFXg93c/v4Y2mP/73wfTn/J7of29x+Hv8wKvPvI7M2+jK9Xj1zdmZvsAyPJ/u996K9Rl/XfU8TJ+9JTMGNuKVJ57Cxrr+GB/uPa2LCtZi+hvzM7lV5bPx0vQPrPUbmfVwTesqIpienuvpJ59EJJnyNeTKeCBPws43vB9bZ2PGfO45GOpZBERABERABERABEQg9wjoACD3rrl2LAK9h0BDNdasWIFVoaxahVTwPraT19hL28XrsDbce1o3NP+PBHrpwrUsERABERABERABERCBvkhABwB98appzSIgAu0joGwREAEREAEREAEREAEREAHoAEA3gQiIwB5PQBsUAREQAREQAREQAREQARGADgB0E4iACOzxBLRBERABERABERABERABERABI6CfADAI+hABEdiTCWhvIiACIiACIiACIiACIiACJKADAFKQiIAI7LkEtDMREAEREAEREAEREAEREAFPQAcAHoOeREAE9lQC2pcIiIAIiIAIiIAIiIAIiEBAQAcAAQc9i4AI7JkEtCsREAEREAEREAEREAEREIE0AR0ApEFIiYAI7IkEtCcREAEREAEREAEREAEREIGQgA4AQhLSIiACex4B7UgEREAEREAEREAEREAERCBDQAcAGRQyREAE9jQC2o8IiIAIiIAIiIAIiIAIiEATAR0ANLGQJQIisGcR0G5EQAREQAREQAREQAREQASyCOgAIAuGTBEQgT2JgPYiAiIgAiIgAiIgAiIgAiKQTUAHANk0ZIuACOw5BLQTERABERABERABERABERCBZgR0ANAMhwYiIAJ7CgHtQwREQAREQAREQAREQAREoDkBHQA056GRCIjAnkFAuxABERABERABERABERABEWhBQAcALYBoKAIisCcQ0B5EQAREQAREQAREQAREQARaEtABQEsiGouACPR9AtqBCIiACIiACIiACIiACIjADgR0ALADEjlEQAT6OgGtXwREQAREQAREQAREQAREYEcCOgDYkYk8IiACfZuAVi8CIiACIiACIiACIiACItAKAR0AtAJFLhEQgb5MQGsXAREQAREQAREQAREQARFojYAOAFqjIp8IiEDfJaCVi4AIiIAIiIAIiIAIiIAItEpABwCtYpFTBESgrxLQukVABERABERABERABERABFonoAOA1rnIKwIi0DcJaNUiIAIiIAIiIAIiIAIiIAI7IaADgJ2AkVsERKAvEtCaRUAEREAEREAEREAEREAEdkZABwA7IyO/CIhA3yOgFYuACIiACIiACIiACIiACOyUgA4AdopGAREQgb5GQOsVAREQAREQAREQAREQARHYOQEdAOycjSIiIAJ9i4BWKwIiIAIiIAIiIAIiIAIisAsCOgDYBRyFREAE+hIBrVUEREAEREAEREAEREAERGBXBHQAsCs6iomACPQdAlqpCIiACIiACIiACIiACIjALgnoAGCXeBQUARHoKwS0ThEQAREQAREQAREQAREQgV0T0AHArvkoKgIi0DcIaJUiIAIiIAIiIAIiIAIiIAIfQkAHAB8CSGEREIG+QEBrFAEREAEREAEREAEREAER+DACOgD4MEKKi4AI9H4CWqEIiIAIiIAIiIAIiIAIiMCHEtABwIciUoIIiEBvJ6D1iYAIiIAIiIAIiIAIiIAIfDgBHQB8OCNliIAI9G4CWp0IiIAIiIAIiIAIiIAIiEAbCOgAoA2QlCICItCbCWhtIiACIiACIiACIiACIiACbSGgA4C2UFKOCIhA7yWglYmACIiACIiACIiACIiACLSJgA4A2oRJSSIgAr2VgNYlAiIgAiIgAiIg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E9ABQGcTVT8REIGOE1AHERABERABERABERABERCBTiegA4BOR6qGIiACHSWgehEQAREQAREQAREQAREQgc4noAOAzmeqjiIgAh0joGoREAEREAEREAEREAEREIEuIKADgC6AqpYiIAIdIaBaERABERABERABERABERCBriCgA4CuoKqeIiACu09AlSIgAiIgAiIgAiIgAiIgAl1CQAcAXYJVTUVABHaXgOpEQAREQAREQAREQAREQAS6hoAOALqGq7qKgAjsHgFViYAIiIAIiIAIiIAIiIAIdBEBHQB0EVi1FQER2B0CqhEBERABERABERABERABEegqAjoA6Cqy6isCItB+AqoQAREQAREQAREQAREQARHoMgI6AOgytGosAiLQXgLKFwEREAEREAEREAEREAER6DoCOgDoOrbqLAIi0D4CyhYBERABERABERABERABEehCAjoA6EK4ai0CItAeAsoVAREQAREQAREQAREQARHoSgI6AOhKuuotAiLQdgLKFAEREAEREAEREAEREAER6FICOgDoUrxqLgIi0FYCyhMBERABERABERABERABEehaAjoA6Fq+6i4CItA2AsoSAREQAREQAREQAREQARHoYgI6AOhiwGovAiLQFgLKEQEREAEREAEREAEREAER6GoCOgDoasLqLwIi8OEElCECIiACIiACIiACIiACItDlBHQA0OWINYEIiMCHEVBcBERABERABERABERABESg6wnoAKDrGWsGERCBXRNQVAREQAREQAREQAREQAREoBsI6ACgGyBrChEQgV0RUEwEREAEREAEREAEREAERKA7COgAoDsoaw4REIGdE1BEBERABERABERABERABESgWwjoAKBbMGsSERCBnRGQXwREQAREQAREQAREQAREoHsI6ACgezhrFhEQgdYJyCsCIiACIiACIiACIiACItBNBHQA0E2gNY0IiEBrBOQTAREQAREQAREQAREQARHoLgI6AOgu0ppHBERgRwLyiIAIiIAIiIAIiIAIiIAIdBsBHQB0G2pNJAIi0JKAxiIgAiIgAiIgAiIgAiIgAt1HQAcA3cdaM4mACDQnoJEIiIAIiIAIiIAIiIAIiEA3EtABQDfC1lQiIALZBGSLgAiIgAiIgAiIgAiIgAh0JwEdAHQnbc0lAiLQRECWCIiACIiACIiACIiACIhAtxLQAUC34tZkIiACIQFpERABERABERABERABERCB7iWgA4Du5a3ZREAEAgJ6FgEREAEREAEREAEREAER6GYCOgDoZuCaTgREgAQkIiACIiACIiACIiACIiAC3U1ABwDdTVzziYAIAGIgAiIgAiIgAiIgAiIgAiLQ7QR0ANDtyDWhCIiACIiACIiACIiACIiACIiACHQ/AR0AdD9zzSgCuU5A+xcBERABERABERABERABEegBAjoA6AHomlIEcpuAdi8CIiACIiACIiACIiACItATBHQA0BPUNacI5DIB7V0EREAEREAEREAEREAERKBHCOgAoEewa1IRyF0C2rkIiIAIiIAIiIAIiIAIiEDPENABQM9w16wikKsEtG8REAEREAEREAEREAEREIEeIqADgB4Cr2lFIDcJaNciIAIiIAIiIAIiIAIiIAI9RUAHAD1FXvOKQC4S0J5FQAREQAREQAREQAREQAR6jIAOAHoMvSYWgdwjoB2LgAiIgAiIgAiIgAiIgAj0HAEdAPQce80sArlGQPsVAREQAREQAREQAREQARHoQQI6AOhB+JpaBHKLgHYrAiIgAiIgAiIgAiIgAiLQkwR0ANCT9DW3COQSAe1VBERABERABERABERABESgRwnoAKBH8WtyEcgdAtqpCIiACIiACIiACIiACIhAzxLQAUDP8tfsIpArBLRPERABERABERABERABERCBHiagA4AevgCaXgRyg4B2KQIiIAIiIAIiIAIiIAIi0NMEdADQ01dA84tALhDQHkVABERABERABERABERABHqcgA4AevwSaAEisOcT0A5FQAREQAREQAREQAREQAR6noAOAHr+GmgFIrCnE9D+REAEREAEREAEREAEREAEegEBHQD0gougJYjAnk1AuxMBERABERABERABERABEegNBHQA0BuugtYgAnsyAe1NBERABERABERABERABESgVxDQAUCvuAxahAjsuQS0MxEQAREQAREQAREQAREQgd5BQAcAveM6aBUisKcS0L5EQAREQAREQAREQAREQAR6CQEdAPSSC6FliMCeSUC7EgEREAEREAEREAEREAER6C0E8kpKRqBkpAk1ZeRwBD7TtBmjDmO0m/mslj7GQ+G4WU66F+OMeW111KGk/aXhONTsQzsd92vjOBTGw5jpTL3Zre6LdYxRU1jvdXqN2bHQH2rGvNjavbaa7BhtCmNeM4+SlUc/hTkU2qGEawnHrensnNBmn1Ba1rT0c2w5nhNtk5Em5Op9FqPt2aX9fhz6qUM/NSX0ZdaT3jNjlDBOHQpzGaPQR00J7WztbetJbdJsnTb262O/0A51y370W16mPozTT8kem808Sqa/+UpCYb5JaclIBD5bXxizOQKfXXf6LK+EQr9p39P8ZVbrbfMxn3Zpixz6S5r5RsDnscaE+dnjYJ7hyNbMyYzTvfzY6jO6pd/Wx74lpn2OaT9O17S0OS6zGLWvYT57muaYa6B4OysvzKdmPJDhCPQI07RNWGO9SrOkzOzWhDllNnfZTuKhf1R2fMQw0E9fkwzDKPOPtjwK/WXhmJoychgYG03NcbbQZzLGZPRwy7PYmIwMxZgRJsOHYqz5xpo9tqUePgTjzB/IEIy1McWPzR7PmOlxWTJ+xBCMtzH1BLMp44cPxgTzBUJ7MMYPG2Q+E6/pM3v4h8iIQRhPsTyvzZ4wwnqFY9MTbM4gZn6LN7fpC2WQ7W2wCXUogzF+5GDvG8++oZ3W4+ijcExh/2Y6XZv2+Xyz2TOUcema8ek+Gb8xom8c44ztVIYYA5ORQ22dQ7KEY17LUIYhuJ6m7fqPsWvL+8Br2iOGY3QzGWFjCv3UJiNH2P0XyOiRIy0+0sYj7X4LZNSIkRhl/jIT6lDKvL/E7mfKSHBcNpL2zqQ0nUvdJKUjmG/jtC4dWWqvx0DK0jY1JYyFdskI5pVZvonZJZZP8Xklo1Ay0vwlZaZLTcpQarYX8zO2S0nX7zyH/U0yeWb7vqG2eZvF6G+SkcxlnDKyhd+Pw3qLWY7Pt5pQZ9ZlsYxtcW+br/U86xXmZHQpSoyLl4zP5s7YFvc2tQlzM2PLS48DrhYPr4H3l6LUchkrSftLbEyhr9R8Xpewrgylpukrod+EuqWUpvu29Dcfl6D5mOsoNR/9JnbvlmbmKvHzlti9S/F+xkw4DmRkU629DgKf9UnX7DAuSceoQ/F17GNCX2bMXPP5XqYZozBObVJqUtJMLI9xCv2mfY7pEsqIEbbekbavEpTauFnMxsyhnzpbmEfJ9oU2/aGEvp3plr1Zx9wyW2toc8w8irctRp0tYS41hTHm06ZwvIPY1zveF2rul/aHieUFPY2d5Y60z4ulpkvaIlbr58zooEfgS9vsYz1LSmzs9Ui7RmaHfu+zcaiz/fRR6GO9183rS73f6rO1rzFfqLNi3J9fS7qXr2ceJSuvxL4u8Xkt/cyhr6WYv5Q9W/GXeN/w9L5tXZZbEoqPjUApazO+4TvmpvMyvcJ8+jN17G18GAv9LfeR8TPXJByHPUJNf1ZtwMnWxbj5/Zh2a2LxktBPO+zlbetBzXios236KPSFEo4zmuu2PozTx/60s4X+7HFoh/6Mtl5hLNRhzMaZfdJHMZ/fW/aczfxN68pLxBuRSDQimYgjsLM0fRZLUPu8OOJesybua+LxBiSTCcQbG4J6H48jYXVx2hTWU8xu8jXCz2c+ry3OHpl4xt/oe2fPkbRcX2M5ySTnivu1JG0djeE6mGNrCPPCPfhaxqyW8yXSOaylhHmsYzxcD3UYT6bnzORaP8ZZ48XGgW6E729j9mJ+Msvm2Oe1WEvoY25ohzpuvH0v1pjt/dbT96I2v/eFmj5K9tjsuElYE87jffRTyCVdF8YzfdN+5ieNuY/Tx5rsWvMxzjpqrpvCcYK5FvdrsBrGQztbJ5lj8QTzTbOe8/oeNs7odDwcJ60uzPO9w1zLC/2ch/3CGo5ps5ZCuym30d/nvpf1ZozS2LjdrjHvwUb/2kglkzYOcn0/y83MYXMn7P5k76Rxa7Trx/5JWxt9zItbDse0vY8x3m+mOV/S4tQU5iWyxr53Os/bNjdzwp5Jji2fvh0ky+97W26ofW44tv4cc30+bmOv03HaKbMpXGuysRGpRMJen40BF4uxnpKibZKK2+vX+jA/YUwSVkObOaxPMm7cYLn0sS5l+QkT2vRli4/ZdaEOhXFf37AdKevlxertkwu8WG+Yn3bScmgzh+OU9YKty3lpBGPO1zYADZTtcFbrLM9ZTl6c40Dy2Gt7PfLNl2dxjvOpTfKtxtsN9cgzyW+sR77l52+vQ0GDCWtM55sUWLzQ4hTGqPO316Kw0fJMFzNmusiksKEWRVZTaDbHxWYXe18NCrfXoNj8xQ1Z2uwi+htrQX+Rz621HoEUm5++jI4HfvoKrTbjt7zChm0oNF9Ro81lutB8tItCbbWBz3p4Xx38OF4H5nnb/IWsN11Evwn9xQnm1hibGgRz1Hq7wOahsIb5zKUusrkCfy3oo811UBemY97mftPj4ni978m8cI5iriMt7FNozPONF3WBcfdijPPNX2jXtSBL8s3O4/W3a11gkm/3DSWP94mN82ycZ/e7szxntjM7z+53R2mMIy+esHsrDpgdSspsChoT5qfE7Z5OmCQDiZu2OsZTzEmkzG955k82WJ5p+lNx83tJItlISaQ17SQY937LSVIamQ/LSSEcJ8xHSdkSk2bbpyqrA1I2Hf1ezKafYp8K4H3Wjy85ivfby4o2JZVw1t/ENMeBOHv5UpCuBwxRk89y/TiR5/20DafZzoS52dpsmy8ed0hk6pzPM7SmHRptL0E9awNJWH7c9pmwOezSWR5AX8J89CezejEnzh7pGONx1puP+ZyXOV77nFTQj6ysT9J0wvI9K6uJGy9KIq2TlpOwumSYZ7Z9CkfCxlw38xj3kq4nZ163uF1r72cvs+lnftLuE2oftz7B3LBrkUSYz3rmeG21Gdt6JWzMHsGaUlZHSVpt0mwT68+6pN1/lAS1+UJNH+s5TsQTvi5h9y/H3p+242lNf8LXJ0Af500mgvky9WEfmyvBGNeYTPn8uF3sIC8RzMUc6530Pdkn9Ju22qTB9flmU7M+7vtbPFuHeVn9kxb3YnNTJxhjnmk/tjh7etv6UydNp0ySzLM45/N+s1PeH6yRfvaLNzSCNexDYQ71/5AbAAAMeUlEQVR1to/rzR77ftar0dbBPn5s/ZlHSdrcvi99luNrTXM+b8fj9nqMg3UB32DMeSnsSZ2wPqEOchPI+Ky3j7WqrZ/Vxu2m9v1tPs7LtSXMTlhNMIflpcdcM/0Jjq3Wz2O6qQfntny7qZMmYV7Scnxuui5un4uDGss1X9zE55pO+q/z2CcQ5iZsLQmLJfxaze913D6X2Nc+od80c5P2oovbJ5GEn5/XLZiD9Unv47jR7tMG+7xgNn0mcVtTJsfW63PNT18gjfDaPhn43ox5Cfz+/ZGNg3012te01tvWxPUE+Y02Z2PQw/x+PstnPG7/Zvne5k/Y3AnOQdskzIvbnoNc9mUfE6tvqmvyM5f+DFfrl7Tc0J+wMeMJ83lt8yTTttcWT9qLPuDdaJwaEeQGOuhjtq0pTtamfS619fE90nbCerHW11iMts+lzRyK2UkT5oTzsi8lzGeMdXGyslzaPmb9fR77hGLx0BfqMJ9jzkGdsFrvt7qk1VB8TxvT768pbYtl+xPpumSWP+iXvgbmz4tEImhNotFoxl9RUeFt+iorK71NTT99rM/W2XYYC32sC23GQmEvSjhmDoXjWCyG0KZmXqhb2uzPGsaps4U+5oc+5tLHMf2U0Ec/bQptCuPUzA+FvtDelQ77MCe02SvbZoxCP3UozAltxijhONTZPtqU7Fg4ztbsy/VTmMsYfdQU+lpK6KdmLuOsp9BHoY+a8Ww/x/SHwjwKxy11mFteXu7vN8ZbCutCCfOZQx/npY9j2vTRpoQ2NXMYD/30+TnT93/UXh+VZlMzj8Ia5sUqYwhtjinlFeWZe5U9KfRTU2izB/tR2Jt+CnuFY9r0VfHeT6+B41DCOPvRR02f7831mtBPCXsyh2MKbQptCm2Kt22+ChOOKezp/emenMdLesx9UHxOus7b0WjAgp8/KiJgTriWTNzyI1HGoqiMVqYliqjlM7ey0mzLoe1rzGYf2vTRzhb6IjZfzD5P0c8xdczWSruZsJflRkOxccTutypfW4FKG1fa2lhLP6XK1sNx1K5zpdXFGI9UIGa5MZuDmv4q+i1ebfnV5vc+nxNBpdVW0fZ1rE2LzR1jLF1bxXqK5VZZbpXFqk1Tqix3G/2ma2yOWPlW0O+15VXZeFukHFUVW71Um2+bSY3VV5VvAe1qxiyv2qTG5qTPz2F+1oZSZfEqq6m2uVjje1lv2sypNptxzsXxtmgFKPRzTD+F4yrrxTpv2zzV0XJbd7lfY8zGPs/qq028zd7mb8qv8PkcM4fz1FRGmnxZ+T7HxtRhL87Bccz2w1ra1bYGxpv6bPX9GKdUbt0C7rma7IxhUBuxNZd7f5X5qo1flV2rmNlVvC6WW2l2zHy8B6osXmk2x7xHqiJRVNl9EbP7nLrK7n36Yqaj5qs0iVlOpQl9HNP2UhG1e8jEYn4csddN2mYupdJ8UfPFolV2H1eiqrLKXn+W5/tHEWEPy4mUR3yc+TF+PmOcfotHTVdGYz43WsHamO9BfyQ9ZjwYR7NitGNgjBKzNYQ5rItaz0rzUaKRmH1+rwzEekZszozYmHHmhb4K80WsJpquj1h+ZWW1rbHS5g97UcfAHErE8kPhmMJxqGmHfUO7orzS1hTzwjwKY5wrsCt9f9ZFo9WgpkRtXdQV5Gc2a+ijjthaI1lrYU6QG/RiHsflVktdyWtmPegnAwptMmEt7aBfJSKWRx/5RsJ5jFU0bUf9vJZnOma8Ku0aRE2CnjG7VnafWA/vt5ywD3PYL+r7VNq/d1XgmMJY6Of9RB/r6KNwzBz2pM0cjikce7/tlWOug+unhHkxv3+uza6D5bEm7Ottu1fD3Eq7dyN2vwf19u+J7S3KuK2bOmrjILfSrlul7SPQUcuJpmtZz3FLzTjnLbfXCmOtCev4byV1pfWjZl6l9Y/YazkcN7ftdcK4rZt5lArLDXP4+i0vrwDn9j7LYx9KmBezuVgXi9n1Mzuck7XMYYx2tmR6WT/mhDHmsp5CmxIx7twPfVU2B3PDccSvNWos7fpYL8aYx/VRfG/uzz7PRawPhX6fY/kR+zxJCblFvC9gQl/Ex6P22jIG1oc+9qSfdsTyK/lvtfUP+0bSNaEO8iKgDnIidv9Sgnla9mMdc9mXEtQEuaEdsXkjfp6I70s7yI1metPHfPZnLBxTc8xYIFHrUWl1UZNIM6mwfy+Yy5pQOKaf9wVt9ghjXHfE1kZNP3XE1hloXqdKu1acyz7ne26VxjZi83PuJgnyI34tlZbHHhHrS2GMEvF9K9O1QT/6+F6Ne2YO1xnxdUGviNVQGKOwN3No0886Cv2Bj2uK+Dk4pjDOmkhWX/oj1puaEsSb6sJ+jDGPQjs7L/TRTzs7Fvpa0/RlC+difejLtrN99HMO+qiz6xgjR8ZoU1OYU25fg4U+6uxajinMpaZk2xyzR7YvzzmH8OGcg3OB0OdcYDvnOPSSSqW85gmYc87n00G/cw7UHFOcc1QZcS6IM8e5pphzDnl5eSgoKAAfzgUx5nHMuSjMoc855+fl2LmgJ/Occ1Q+RsM518zO9jnnOPTrZR+Kc029fNCeWs5nLl9DTXEu6OOc83OF+ezHOMU5ZydtyWZ1zOOeQnHOMdX3oOGc8/nOOTvVsuN4Ok2ca/LbMJNPm+Jc0Ic21+BckM8x56SmcF5q5lCcC+roz85zrqneuSCHdZQwz7nm/jDGXs4FMeZSnGvqx7xQGKM4F8TDWq4tzKHm2DmX2TdrKMxnnMKxc87fU7Rhj1A752yETD3rwp5IP/Lz8z370M87PrTtBeNjztl1sdNHlrA3xTmX6Rv6nQt8jNNnCb6etnMOyfTriWPmUJxzHFqqxe2kmYOW/nAc6pY59IdrZoxCn3PO93XO0ZWxGaPQ6ZyDMyMct9aHMeccyMM512xPzrEaAAJxzsb2kbLvfjjn4Fwg7EGxkU+kTYPzhTbgAPAK2HOaVRijpjjn4JyzvCDHucB2rmldzKPk5edl1sqCPPu8Q50tzHPO+Z7OAhwzj0I7ZdfEuTw4x6glpLVzwXzOBX7mJyzXMvwH7zXWA0Ee7MGxcw7OOTBuLm7C1ugtWMD7mQd7OOfs2T5MB1Tg4845S6XkWRDeZtw588GBD+cCzXVxTKHtnPmNbWtzMIfCPMZDoY82NYWvGWpKyr6jFMacc2AthT4bggLYnLAHF2nKOQfnnFkwDf9wzhmHIMG5IBb0cJbTFPPJ9uScs+fgg3kUjngNnHO+hutwLrAZc86B31GinbDvavDa5rnm90hBfj7DO6zFOQdrCvYM53LOfOn7NXxtwB6MM8+1uN/y8pzva/gtyz7McI494P20MvcFBxa3rMyH75nOp5PzOMdE2NLYO+nvD/ZoGYM9WB/6k/b6BBycc+D1pB/ph3PslfJroot11JRsmzUU+ilJu/+tyvfMzvN+24tzwXzOBZo1FOccrMg+TANeO+e8RvoRzsNezgUxZzH6nQvWa0P/4Rwj3vRPzgXxMNc77SnP/Pl5eV36tQi/zikqKkS/fsUoLi72uqioCIGvH/r365fxFRfTTylEYWFhJp+1YY3X6Rh7O+f8PVlQWIC8/HwE95wDH845//UE980xhTYZhuJcUy7jzgWvB2q+RujzYmmsdc4MczgXaDP9h3McUwBee+ecvxcZZB01hfNSM4finOPQ5zLPuWDsnMvcf84FPp9oT8wzBecsh0aWMJZKJX2M7pTddxTnLNds+rKFMYpzQZyfE2DTcW3ZeRw75+Cc827WUML90Mmxcw7Zn1Pogz2cC+qcCzTrwp4W9h/h6zDb75zzczoXrM+54Jr6AtsP+zsX5HifPWX7aJvL98i2OT/9FPopzjkOfW4Yb+kPx6H2BbYOlxfUpuzzSl5+HjLjIMH3dM6ZzvMe52gHe7LyHXx05Nlr0zlH0wvnpOGcsz6BZPsAh+yHc8E4lb4fnHNwLpwz5W3mp9IL4HyhbWkMWQ4y92EYo6Y4F/RjYjgGAh/HsAc1Jbu3ucExdbYwz7mg3rlgncyjMMZr4lwQZ51zjgrOBbnOBWPmM9cH7Yk2681sthfnXKY2jIV5zrFnMpPvnGNKs3z2dc55n3MuE3fO7eBjkOuiptB2ztHcYQ7vTD8xj2viJaKmO9S0+ZqhpnA9Ycw55xmH9c45vya0eDi3o9857j3lM51zXrOvc873oO2d6SfnXNoK7pUwzvU453wN1+FcYDPZOec/59Hm51nmMiespT/cW+hzztHt+znn/P5axmAP55zPMdOzZV/K/w8AAP//hqvgpAAAAAZJREFUAwBieneQ7lrFFQAAAABJRU5ErkJggg==" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
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

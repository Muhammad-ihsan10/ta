<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Monitoring Pasien IoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary:   #0a0e1a;
            --bg-card:      rgba(255,255,255,0.04);
            --border:       rgba(255,255,255,0.10);
            --accent:       #3b82f6;
            --accent-glow:  rgba(59,130,246,0.35);
            --accent-hover: #60a5fa;
            --text-primary: #f1f5f9;
            --text-muted:   #94a3b8;
            --error:        #f87171;
            --success:      #34d399;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: drift 12s ease-in-out infinite;
        }
        .blob-1 { width: 500px; height: 500px; background: #3b82f6; top: -150px; left: -150px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: #8b5cf6; bottom: -100px; right: -100px; animation-delay: 4s; }
        .blob-3 { width: 300px; height: 300px; background: #06b6d4; top: 50%; left: 50%; animation-delay: 8s; }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -20px) scale(1.05); }
            66%       { transform: translate(-20px, 30px) scale(0.95); }
        }

        /* Grid overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05);
            animation: slideUp 0.6s cubic-bezier(0.16,1,0.3,1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 32px rgba(59,130,246,0.4);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 8px 32px rgba(59,130,246,0.4); }
            50%       { box-shadow: 0 8px 48px rgba(139,92,246,0.6); }
        }

        .logo-icon svg { width: 32px; height: 32px; fill: white; }

        .logo-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--accent);
            background: rgba(59,130,246,0.08);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        input:focus + svg, .input-wrap:focus-within svg {
            color: var(--accent);
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
            box-shadow: 0 4px 20px rgba(59,130,246,0.4);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(59,130,246,0.5);
            opacity: 0.95;
        }

        .btn-login:active { transform: translateY(0); }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            padding: 0;
        }

        .remember-row label {
            margin: 0;
            font-size: 0.85rem;
            text-transform: none;
            letter-spacing: 0;
            cursor: pointer;
        }

        .error-box {
            background: rgba(248,113,113,0.1);
            border: 1px solid rgba(248,113,113,0.3);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 1.25rem;
            color: var(--error);
            font-size: 0.875rem;
        }

        .badge-info {
            margin-top: 1.75rem;
            padding: 12px;
            background: rgba(59,130,246,0.08);
            border: 1px solid rgba(59,130,246,0.2);
            border-radius: 10px;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.6;
        }

        .badge-info span { color: var(--accent-hover); font-weight: 600; }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="login-card">
        <div class="logo-area">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7zm0 9.5A2.5 2.5 0 1 0 12 6.5a2.5 2.5 0 0 0 0 5z"/>
                </svg>
            </div>
            <div class="logo-title">Monitoring Pasien IoT</div>
            <div class="logo-subtitle">Sistem Monitoring Real-Time</div>
        </div>

        @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                ⚠️ {{ $error }}<br>
            @endforeach
        </div>
        @endif

        @if (session('success'))
        <div class="success-box" style="background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.3); border-radius: 10px; padding: 12px 14px; margin-bottom: 1.25rem; color: var(--success); font-size: 0.875rem;">
            ✅ {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="dokter@ta-pasien.com"
                           autocomplete="email" required>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           autocomplete="current-password" required>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login" id="btn-login">
                &nbsp;Masuk
            </button>
        </form>

        <div class="divider"></div>

        <!-- <div class="badge-info">
            <strong style="color:var(--text-primary);">Akun Default:</strong><br>
            Email: <span>admin@ta-pasien.com</span><br>
            Password: <span>password123</span>
        </div> -->

        <div class="divider"></div>

        <div style="text-align:center;font-size:0.875rem;color:var(--text-muted)">
            Belum punya akun?
            <a href="{{ route('register') }}"
               style="color:var(--accent-hover);text-decoration:none;font-weight:600">
               Daftar sekarang
            </a>
        </div>
    </div>

    <script>
        // Disable button on form submit (not on click) to avoid blocking submission
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('btn-login');
            btn.textContent = '⏳  Memproses...';
            btn.disabled = true;
            // Re-enable after 8s in case of server error
            setTimeout(() => {
                btn.textContent = 'Masuk';
                btn.disabled = false;
            }, 8000);
        });
    </script>
</body>
</html>

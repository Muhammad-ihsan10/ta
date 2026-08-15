<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun – Monitoring Pasien IoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary:   #0a0e1a;
            --bg-card:      rgba(255,255,255,0.04);
            --border:       rgba(255,255,255,0.10);
            --accent:       #8b5cf6;
            --accent-glow:  rgba(139,92,246,0.35);
            --accent-hover: #a78bfa;
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
            overflow-x: hidden;
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
        .blob-1 { width: 500px; height: 500px; background: #8b5cf6; top: -150px; right: -150px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: #3b82f6; bottom: -100px; left: -100px; animation-delay: 4s; }
        .blob-3 { width: 300px; height: 300px; background: #06b6d4; top: 40%; right: 30%; animation-delay: 8s; }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -20px) scale(1.05); }
            66%       { transform: translate(-20px, 30px) scale(0.95); }
        }

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

        .register-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 2.5rem;
            margin: 2rem auto;
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
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 32px rgba(139,92,246,0.4);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 8px 32px rgba(139,92,246,0.4); }
            50%       { box-shadow: 0 8px 48px rgba(59,130,246,0.6); }
        }

        .logo-icon svg { width: 32px; height: 32px; fill: white; }

        .logo-title    { font-size: 1.4rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.5px; }
        .logo-subtitle { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }

        .form-group { margin-bottom: 1.1rem; }

        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrap { position: relative; }

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

        input[type="text"],
        input[type="email"],
        input[type="password"] {
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
            background: rgba(139,92,246,0.08);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .input-wrap:focus-within svg { color: var(--accent); }

        /* Password strength indicator */
        .strength-bar {
            height: 4px;
            border-radius: 99px;
            background: rgba(255,255,255,0.08);
            margin-top: 6px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 99px;
            width: 0;
            transition: width 0.3s ease, background 0.3s;
        }
        .strength-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 4px;
            display: block;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            margin-top: 0.75rem;
            transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
            box-shadow: 0 4px 20px rgba(139,92,246,0.4);
        }

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(139,92,246,0.5);
            opacity: 0.95;
        }

        .btn-register:active { transform: translateY(0); }

        .error-box {
            background: rgba(248,113,113,0.1);
            border: 1px solid rgba(248,113,113,0.3);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 1.25rem;
            color: var(--error);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .field-error {
            font-size: 0.78rem;
            color: var(--error);
            margin-top: 5px;
            display: block;
        }

        input.is-invalid { border-color: rgba(248,113,113,0.5); }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.5rem 0;
        }

        .login-link {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--accent-hover);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover { text-decoration: underline; }

        /* Terms notice */
        .terms-note {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.5;
            margin-top: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="register-card">
        <div class="logo-area">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
            </div>
            <div class="logo-title">Buat Akun Baru</div>
            <div class="logo-subtitle">Sistem Monitoring Pasien IoT</div>
        </div>

        @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
                ⚠️ {{ $error }}<br>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <div class="input-wrap">
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="Dr. Budi Santoso"
                           class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                           autocomplete="name" required>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="nama@ta-pasien.com"
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                           autocomplete="email" required>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="Minimal 6 karakter"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           autocomplete="new-password" required
                           oninput="checkStrength(this.value)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <div class="strength-bar">
                    <div class="strength-fill" id="strength-fill"></div>
                </div>
                <span class="strength-label" id="strength-label">Masukkan password...</span>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Ulangi password"
                           class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                           autocomplete="new-password" required
                           oninput="checkMatch()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <span class="strength-label" id="match-label"></span>
                @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn-register" id="btn-register">
                ✨ &nbsp;Buat Akun Sekarang
            </button>
            <p class="terms-note">Dengan mendaftar, Anda menyetujui bahwa akun ini hanya untuk penggunaan sistem monitoring pasien.</p>
        </form>

        <div class="divider"></div>

        <div class="login-link">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>

    <script>
        function checkStrength(val) {
            const fill  = document.getElementById('strength-fill');
            const label = document.getElementById('strength-label');
            if (!val) {
                fill.style.width = '0'; label.textContent = 'Masukkan password...';
                fill.style.background = '#64748b'; return;
            }
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { w:'20%', c:'#ef4444', t:'Sangat lemah' },
                { w:'40%', c:'#f59e0b', t:'Lemah' },
                { w:'60%', c:'#f59e0b', t:'Sedang' },
                { w:'80%', c:'#10b981', t:'Kuat' },
                { w:'100%',c:'#10b981', t:'Sangat kuat ✓' },
            ];
            const lvl = levels[Math.min(score, 4)];
            fill.style.width      = lvl.w;
            fill.style.background = lvl.c;
            label.textContent     = lvl.t;
            label.style.color     = lvl.c;
        }

        function checkMatch() {
            const p1 = document.getElementById('password').value;
            const p2 = document.getElementById('password_confirmation').value;
            const lbl = document.getElementById('match-label');
            if (!p2) { lbl.textContent = ''; return; }
            if (p1 === p2) {
                lbl.textContent  = '✓ Password cocok';
                lbl.style.color  = '#34d399';
            } else {
                lbl.textContent  = '✗ Password tidak cocok';
                lbl.style.color  = '#f87171';
            }
        }

        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('btn-register');
            btn.textContent = '⏳  Mendaftarkan...';
            btn.disabled = true;
            setTimeout(() => {
                btn.textContent = '✨  Buat Akun Sekarang';
                btn.disabled = false;
            }, 5000);
        });
    </script>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mohamilon Society - Staff & Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #254db1 0%, #2f5bc7 50%, #2243a0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        /* Ambient Wave Graphic at Bottom */
        .bottom-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 140px;
            pointer-events: none;
            z-index: 1;
        }

        /* Main Floating Card */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 860px;
            min-height: 480px;
            background: #ffffff;
            border-radius: 20px;
            border: 4px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(10, 25, 60, 0.35);
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            overflow: hidden;
        }

        /* LEFT PANEL (Dark Navy) */
        .panel-left {
            background: #092147;
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
        }

        .left-header {
            text-align: center;
        }
        .left-header h2 {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.3;
        }
        .left-header span {
            font-size: 13px;
            font-weight: 700;
            color: #93c5fd;
            letter-spacing: 2px;
            display: block;
            margin-top: 4px;
        }

        /* SVG Graphic Illustration */
        .illustration-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 0;
        }
        .illustration-svg {
            width: 100%;
            max-width: 260px;
            height: auto;
            filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.25));
        }

        /* Wise Quote */
        .quote-box {
            margin-top: auto;
        }
        .quote-tag {
            color: #f97316;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .quote-content {
            border-left: 3px solid #ea580c;
            padding-left: 12px;
            color: #cbd5e1;
            font-size: 12px;
            font-style: italic;
            line-height: 1.5;
        }

        /* RIGHT PANEL (Clean Light) */
        .panel-right {
            background: #f8fafc;
            padding: 40px 38px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Logo Emblem Circle */
        .logo-circle {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            border: 2px dashed #94a3b8;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            margin-bottom: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }
        .logo-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0284c7;
            font-size: 26px;
        }

        .panel-right h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f2757;
            margin-bottom: 6px;
        }
        .panel-right p {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 26px;
        }

        /* Form */
        .auth-form {
            width: 100%;
            max-width: 320px;
        }

        .input-group {
            position: relative;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
            display: flex;
            align-items: center;
        }
        .input-field {
            width: 100%;
            padding: 12px 14px 12px 40px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            font-size: 13px;
            font-family: inherit;
            color: #1e293b;
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .input-field::placeholder {
            color: #94a3b8;
        }

        .eye-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.15s ease;
        }
        .eye-btn:hover {
            color: #475569;
        }

        /* Submit Button */
        .submit-btn {
            width: 140px;
            background: #4a228a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 11px 0;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(74, 34, 138, 0.35);
            transition: all 0.2s ease;
            margin-top: 6px;
            margin-bottom: 22px;
        }
        .submit-btn:hover {
            background: #3c1a70;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(74, 34, 138, 0.45);
        }
        .submit-btn:active {
            transform: translateY(0);
        }

        /* Security Badge */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #0f766e;
        }
        .security-badge i {
            color: #d97706;
            font-size: 13px;
        }

        /* Alert Banners */
        .err {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 14px;
            text-align: left;
        }
        .flash {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 14px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
                max-width: 420px;
            }
            .panel-left {
                display: none;
            }
            .panel-right {
                padding: 36px 24px;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Bottom Wave Graphics -->
    <svg class="bottom-wave" viewBox="0 0 1440 140" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,40 C320,120 420,10 740,60 C1060,110 1140,20 1440,70 L1440,140 L0,140 Z" fill="#e2e8f0" fill-opacity="0.25"/>
        <path d="M0,70 C360,130 520,30 860,80 C1160,120 1280,40 1440,90 L1440,140 L0,140 Z" fill="#ffffff" fill-opacity="0.4"/>
    </svg>

    <div class="login-card">
        <!-- LEFT PANEL -->
        <div class="panel-left">
            <div class="left-header">
                <h2>Mohamilon Cooperative</h2>
                <span>Society Portal</span>
            </div>

            <div class="illustration-wrap">
                <!-- SVG Isometric Dashboard Graphic -->
                <svg class="illustration-svg" viewBox="0 0 320 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Base Isometric Plane -->
                    <polygon points="160,40 280,105 160,170 40,105" fill="#1e3a8a" fill-opacity="0.3"/>
                    
                    <!-- Monitor / Dashboard Screen -->
                    <rect x="80" y="45" width="160" height="110" rx="10" fill="#ffffff" stroke="#60a5fa" stroke-width="4"/>
                    <rect x="80" y="45" width="160" height="24" rx="8" fill="#3b82f6"/>
                    <circle cx="96" cy="57" r="4" fill="#ffffff" fill-opacity="0.7"/>
                    <circle cx="108" cy="57" r="4" fill="#ffffff" fill-opacity="0.7"/>
                    <circle cx="120" cy="57" r="4" fill="#ffffff" fill-opacity="0.7"/>

                    <!-- Screen Chart Elements -->
                    <rect x="94" y="80" width="40" height="45" rx="4" fill="#dbeafe"/>
                    <path d="M98 115 L106 100 L118 108 L130 88" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>
                    
                    <rect x="144" y="80" width="82" height="8" rx="4" fill="#cbd5e1"/>
                    <rect x="144" y="94" width="60" height="6" rx="3" fill="#e2e8f0"/>
                    <rect x="144" y="106" width="70" height="6" rx="3" fill="#e2e8f0"/>
                    <rect x="144" y="118" width="50" height="6" rx="3" fill="#e2e8f0"/>

                    <!-- Screen Stand -->
                    <path d="M152 155 L150 178 L170 178 L168 155 Z" fill="#94a3b8"/>
                    <ellipse cx="160" cy="180" rx="26" ry="7" fill="#64748b"/>

                    <!-- User Avatar 1 (Left) -->
                    <circle cx="65" cy="130" r="14" fill="#fbcfe8"/>
                    <path d="M48 165 C48 145 82 145 82 165 Z" fill="#ec4899"/>

                    <!-- User Avatar 2 (Right) -->
                    <circle cx="255" cy="130" r="14" fill="#fed7aa"/>
                    <path d="M238 165 C238 145 272 145 272 165 Z" fill="#0284c7"/>

                    <!-- Floating Coins & Shield -->
                    <g transform="translate(60, 65)">
                        <circle cx="12" cy="12" r="12" fill="#f59e0b"/>
                        <circle cx="12" cy="12" r="9" stroke="#fbbf24" stroke-width="2"/>
                        <text x="9" y="16" fill="#ffffff" font-size="12" font-weight="bold">â‚¹</text>
                    </g>
                    <g transform="translate(245, 60)">
                        <path d="M12 2 L22 7 V14 C22 19 12 24 12 24 C12 24 2 19 2 14 V7 Z" fill="#10b981"/>
                        <path d="M8 12 L11 15 L16 9" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    </g>
                </svg>
            </div>

            <div class="quote-box">
                <div class="quote-tag">A Wise Quote</div>
                <div class="quote-content">
                    "You can get everything you want if you work hard, trust the process, and stick to the plan."
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="panel-right">
            <div class="logo-circle">
                <div class="logo-inner">
                    <i class="bi bi-bank2"></i>
                </div>
            </div>

            <h1>Welcome Back</h1>
            <p>Enter your email and password to access your account</p>

            <form method="post" action="/admin/login" class="auth-form">
                @csrf

                @if(session('ok'))
                    <div class="flash">{{ session('ok') }}</div>
                @endif

                @error('email')
                    <div class="err">{{ $message }}</div>
                @enderror

                @if(isset($errors) && $errors->has('error'))
                    <div class="err">{{ $errors->first('error') }}</div>
                @endif

                <div class="input-group">
                    <span class="input-icon"><i class="bi bi-person"></i></span>
                    <input name="email" type="email" class="input-field" placeholder="techvision@gmail.com" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="input-group">
                    <span class="input-icon"><i class="bi bi-lock"></i></span>
                    <input id="passwordInput" name="password" type="password" class="input-field" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
                    <button type="button" class="eye-btn" onclick="togglePassword()" title="Toggle password visibility">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="submit-btn">LOGIN</button>

                <div class="security-badge">
                    <i class="bi bi-shield-fill-check"></i>
                    <span>Secured with Banking Security</span>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
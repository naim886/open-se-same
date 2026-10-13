<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title') | Open Se Same</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary:          #C2436B;
            --primary-dark:     #7A1235;
            --primary-mid:      #A33560;
            --primary-light:    #E070A0;
            --primary-gradient: linear-gradient(135deg, #7A1235 0%, #C2436B 55%, #E070A0 100%);
            --text-dark:        #1E0010;
            --text-body:        #3A2030;
            --text-muted:       #998899;
            --bg-light:         #FFF6FB;
            --border:           #F2D0DF;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        /* Header */
        .error-header {
            background: #fff;
            box-shadow: 0 3px 24px rgba(122,18,53,0.12);
            border-bottom: 3px solid transparent;
            border-image: var(--primary-gradient) 1;
            padding: 16px 0;
        }
        .error-header .logo-img { height: 42px; object-fit: contain; }
        .back-home-btn {
            background: var(--primary-gradient);
            color: #fff; border: none; border-radius: 50px;
            padding: 10px 24px; font-size: 14px; font-weight: 600;
            text-decoration: none; transition: all 0.25s ease;
            display: inline-flex; align-items: center; gap: 7px;
        }
        .back-home-btn:hover { color: #fff; box-shadow: 0 6px 24px rgba(122,18,53,0.35); transform: translateY(-2px); }
        /* Main */
        .error-main {
            flex: 1; display: flex; align-items: center;
            justify-content: center; padding: 60px 20px;
        }
        .error-card {
            background: #fff; border-radius: 24px;
            box-shadow: 0 20px 80px rgba(122,18,53,0.13);
            padding: 60px 50px; text-align: center;
            max-width: 620px; width: 100%;
            position: relative; overflow: hidden;
        }
        .error-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 5px;
            background: var(--primary-gradient);
        }
        /* Icon bubble */
        .error-icon-bubble {
            width: 90px; height: 90px; border-radius: 50%;
            background: var(--primary-gradient);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 32px rgba(122,18,53,0.25);
            font-size: 36px; color: #fff;
            animation: bubblePop 0.6s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }
        @keyframes bubblePop {
            0%   { transform: scale(0); opacity: 0; }
            70%  { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        /* Error code */
        .error-code {
            font-size: clamp(72px, 18vw, 120px);
            font-weight: 900; line-height: 1;
            background: var(--primary-gradient);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; letter-spacing: -3px;
            margin-bottom: 4px;
            animation: pulse404 2.8s ease-in-out infinite;
        }
        @keyframes pulse404 {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(0.97); }
        }
        /* Divider */
        .error-divider {
            display: flex; align-items: center; justify-content: center;
            gap: 6px; margin: 12px 0 22px;
        }
        .error-divider span { display: block; height: 4px; border-radius: 2px; background: var(--primary-gradient); }
        .error-divider span:nth-child(1) { width: 10px; opacity: 0.35; }
        .error-divider span:nth-child(2) { width: 40px; }
        .error-divider span:nth-child(3) { width: 10px; opacity: 0.35; }
        /* Text */
        .error-heading { font-size: clamp(18px, 4vw, 24px); font-weight: 700; color: var(--text-dark); margin-bottom: 10px; }
        .error-subtitle { font-size: 14.5px; color: var(--text-muted); line-height: 1.7; margin-bottom: 32px; }
        /* Buttons */
        .error-actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .btn-primary-brand {
            background: var(--primary-gradient); color: #fff; border: none; border-radius: 50px;
            padding: 12px 30px; font-size: 14px; font-weight: 600; text-decoration: none;
            transition: all 0.25s ease; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary-brand:hover { color: #fff; box-shadow: 0 8px 28px rgba(122,18,53,0.35); transform: translateY(-2px); }
        .btn-outline-brand {
            background: transparent; color: var(--primary-mid);
            border: 2px solid var(--primary-mid); border-radius: 50px;
            padding: 10px 28px; font-size: 14px; font-weight: 600; text-decoration: none;
            transition: all 0.25s ease; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-outline-brand:hover { background: var(--primary-gradient); border-color: transparent; color: #fff; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(122,18,53,0.25); }
        /* Quick links */
        .quick-links-section { margin-top: 36px; padding-top: 28px; border-top: 1px solid var(--border); }
        .quick-links-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.2px; color: var(--text-muted); margin-bottom: 14px; }
        .quick-pill-list { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
        .quick-pill {
            background: var(--bg-light); border: 1px solid var(--border); border-radius: 50px;
            padding: 6px 16px; font-size: 13px; color: var(--primary-dark); font-weight: 500;
            text-decoration: none; transition: all 0.2s ease;
        }
        .quick-pill:hover { background: var(--primary-gradient); border-color: transparent; color: #fff; transform: translateY(-1px); }
        /* Floating shapes */
        .shape { position: fixed; border-radius: 50%; background: var(--primary-gradient); opacity: 0.06; pointer-events: none; animation: floatShape 8s ease-in-out infinite; }
        .shape-1 { width: 300px; height: 300px; top: -80px; left: -80px; animation-delay: 0s; }
        .shape-2 { width: 200px; height: 200px; bottom: 60px; right: -60px; animation-delay: 3s; }
        .shape-3 { width: 120px; height: 120px; top: 40%; right: 10%; animation-delay: 1.5s; }
        @keyframes floatShape { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-20px) scale(1.05); } }
        /* Footer */
        .error-footer { background: var(--primary-dark); color: rgba(255,255,255,0.6); text-align: center; padding: 18px 20px; font-size: 13px; }
        .error-footer strong { color: rgba(255,255,255,0.9); }
        @media (max-width: 575px) {
            .error-card { padding: 40px 24px; }
            .btn-primary-brand, .btn-outline-brand { width: 100%; justify-content: center; }
        }
    </style>
    @yield('extra_css')
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <!-- Header -->
    <header class="error-header">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo-web.png') }}" alt="Open Se Same" class="logo-img"
                     onerror="this.alt='Open Se Same';">
            </a>
            <a href="{{ url('/') }}" class="back-home-btn">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </header>

    <!-- Main -->
    <main class="error-main">
        <div class="error-card">

            <div class="error-icon-bubble">
                <i class="@yield('icon')"></i>
            </div>

            <div class="error-code">@yield('code')</div>

            <div class="error-divider"><span></span><span></span><span></span></div>

            <h1 class="error-heading">@yield('heading')</h1>
            <p class="error-subtitle">@yield('subtitle')</p>

            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn-primary-brand">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
                @yield('extra_actions')
            </div>

            <!-- Quick Links -->
            <div class="quick-links-section">
                <p class="quick-links-label">Popular Pages</p>
                <div class="quick-pill-list">
                    <a href="{{ url('/products') }}" class="quick-pill"><i class="fas fa-shopping-bag me-1"></i>All Products</a>
                    <a href="{{ url('/blogs') }}" class="quick-pill"><i class="fas fa-rss me-1"></i>Blog</a>
                    <a href="{{ url('/cart') }}" class="quick-pill"><i class="fas fa-shopping-cart me-1"></i>My Cart</a>
                    <a href="https://wa.me/+8801886870406" target="_blank" class="quick-pill"><i class="fab fa-whatsapp me-1"></i>WhatsApp Us</a>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="error-footer">
        © {{ date('Y') }} <strong>Open Se Same</strong>. All rights reserved.
    </footer>

</body>
</html>
        </div>
    </body>
</html>

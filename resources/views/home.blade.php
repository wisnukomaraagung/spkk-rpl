<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Koperasi Kampus</title>

    <!-- ✅ PANGGIL CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <!-- ✅ STYLING KHUSUS UNTUK INDEX (Landing Page Balance & Minimalis) -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0 !important; /* Overwrite padding 20px dari style.css untuk halaman ini */
            background-color: #f8fafc; /* Warna background soft modern */
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .landing-card {
            background: #ffffff;
            padding: 3rem 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05), 0 4px 10px rgba(0, 0, 0, 0.03);
            text-align: center;
            max-width: 420px;
            width: 90%;
            border: 1px solid #eef2f6;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
        }

        .landing-title {
            color: #1e293b;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.5px;
        }

        .landing-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin: 0 0 2rem 0;
            line-height: 1.5;
        }

        .btn-login-modern {
            display: block;
            width: 100%;
            background: #0f172a;
            color: #ffffff;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.85rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2), 0 2px 4px -1px rgba(15, 23, 42, 0.1);
            border: none;
            cursor: pointer;
            box-sizing: border-box;
        }

        .btn-login-modern:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -1px rgba(15, 23, 42, 0.3), 0 3px 6px -1px rgba(15, 23, 42, 0.15);
        }
        
        /* Hilangkan efek text-decoration saat a di hover dari style.css */
        a.login-link:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="landing-card">
    <div class="brand-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
    </div>

    <h1 class="landing-title">POS Koperasi</h1>
    <p class="landing-subtitle">Sistem Penjualan & Kasir<br>Manajemen Cerdas dan Praktis</p>

    <a href="{{ route('login') }}" class="login-link">
        <button class="btn-login-modern">Masuk ke Sistem</button>
    </a>
</div>

</body>
</html>

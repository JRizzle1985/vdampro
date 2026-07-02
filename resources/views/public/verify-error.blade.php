<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('general.verification.not_found') }}</title>
    
    <!-- Premium Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --primary: #ef4444;
            --primary-hover: #dc2626;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
        }

        .error-container {
            width: 100%;
            max-width: 450px;
            background-color: var(--card-bg);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 40px 30px;
            border: 1px solid var(--border-color);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .error-badge {
            width: 80px;
            height: 80px;
            background-color: #fef2f2;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            border: 2px solid #fee2e2;
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.15);
        }

        .error-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
        }

        .error-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .tag-display {
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
            padding: 10px 20px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 1.1rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.5px;
        }

        .btn-retry {
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-retry:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.92); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

    <div class="error-container">
        <div class="error-badge">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        
        <h1 class="error-title">{{ trans('general.verification.not_found') }}</h1>
        
        <p class="error-desc">
            {{ trans('general.verification.not_found_desc') }}
        </p>

        @if(!empty($tag))
            <div class="tag-display">
                {{ $tag }}
            </div>
        @endif

        <a href="{{ route('login') }}" class="btn-retry">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> {{ trans('general.verification.admin_login') }}
        </a>
    </div>

</body>
</html>

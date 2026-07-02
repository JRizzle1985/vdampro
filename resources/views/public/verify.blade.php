<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('general.verification.verified') }} - {{ $asset->name }}</title>
    
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
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --success: #10b981;
            --success-light: #ecfdf5;
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
            justify-content: flex-start;
            padding: 20px 15px;
        }

        /* Admin Header Bar */
        .admin-bar {
            width: 100%;
            max-width: 500px;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 16px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            font-size: 0.9rem;
            animation: slideDown 0.5s ease-out;
        }

        .admin-bar a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .admin-bar a:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }

        /* Main Container Card */
        .verification-container {
            width: 100%;
            max-width: 500px;
            background-color: var(--card-bg);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            animation: fadeIn 0.6s ease-out;
        }

        /* Verified Header Banner */
        .verified-header {
            background-color: var(--success-light);
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
            padding: 30px 20px 25px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .verified-icon-badge {
            width: 64px;
            height: 64px;
            background: var(--success);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
            animation: pulse 2s infinite;
        }

        .verified-title {
            color: var(--success);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .verified-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 400;
        }

        /* Grid info table styling */
        .info-list {
            display: flex;
            flex-direction: column;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .info-item:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.4;
        }

        /* Collapsible items */
        .collapsible-fields {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .collapsible-fields.expanded {
            max-height: 1200px; /* high enough value for custom fields */
        }

        /* Show more / less toggler */
        .toggle-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 20px;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            border-bottom: 1px solid var(--border-color);
        }

        .toggle-btn:hover {
            background-color: rgba(2, 132, 199, 0.03);
            color: var(--primary-hover);
        }

        /* Action Buttons */
        .actions-container {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn-leaflet {
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 15px 25px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2), 0 2px 4px -1px rgba(2, 132, 199, 0.1);
            transition: var(--transition);
        }

        .btn-leaflet:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.3);
        }

        .btn-leaflet.disabled {
            background-color: var(--border-color);
            color: var(--text-secondary);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* Image Display Grid Container */
        .image-showcase {
            width: 100%;
            max-width: 500px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }

        .image-showcase img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        /* Language Switcher Footer */
        .language-switcher {
            width: 100%;
            max-width: 500px;
            background-color: #f1f5f9;
            border-radius: 14px;
            padding: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .lang-link {
            flex: 1;
            text-align: center;
            padding: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 10px;
            transition: var(--transition);
        }

        .lang-link.active {
            background-color: #ffffff;
            color: var(--text-primary);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            font-weight: 600;
        }

        /* Keyframe Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            .verification-container {
                border-radius: 20px;
            }
            .info-item {
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Admin / Staff Navigation Portal -->
    <div class="admin-bar">
        <span><i class="fa-solid var(--signedIn ? 'fa-user-shield' : 'fa-lock')"></i> {{ var_export(trans('general.admin'), true) }}</span>
        @if($signedIn)
            <a href="{{ route('hardware.show', $asset->id) }}">
                <i class="fa-solid fa-gauge-high"></i> {{ trans('general.verification.admin_details') }}
            </a>
        @else
            <a href="{{ route('login') }}?redirect_to={{ urlencode(route('hardware.show', $asset->id)) }}">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> {{ trans('general.verification.admin_login') }}
            </a>
        @endif
    </div>

    <!-- Image Grid Card -->
    <div class="image-showcase">
        <img src="{{ $image_url }}" alt="{{ $asset->name }}">
    </div>

    <!-- Verification Info Table Card -->
    <div class="verification-container">
        <!-- Badge verified banner -->
        <div class="verified-header">
            <div class="verified-icon-badge">
                <i class="fa-solid fa-check"></i>
            </div>
            <div class="verified-title">{{ trans('general.verification.verified') }}</div>
            <div class="verified-subtitle">{{ app()->getLocale() == 'hi-IN' ? 'सत्यापित' : 'Authentication Successful' }}</div>
        </div>

        <!-- Info field List -->
        <div class="info-list">
            <!-- primary fields displayed always -->
            <div class="info-item">
                <span class="info-label">{{ trans('general.verification.medication_name') }}</span>
                <span class="info-value">{{ $asset->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ trans('general.verification.brand_name') }}</span>
                <span class="info-value">{{ $asset->model ? $asset->model->name : trans('general.na') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ trans('general.verification.expiry_date') }}</span>
                <span class="info-value">{{ $expiry_date }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ trans('general.verification.batch_number') }}</span>
                <span class="info-value">{{ $batch_number }}</span>
            </div>

            <!-- Collapsible elements (hidden by default) -->
            <div class="collapsible-fields" id="collapsibleFields">
                <div class="info-item">
                    <span class="info-label">{{ trans('general.verification.manufacturer') }}</span>
                    <span class="info-value">
                        {{ $asset->model && $asset->model->manufacturer ? $asset->model->manufacturer->name : trans('general.na') }}
                        @if(!empty($manufacturer_address))
                            <br><span style="font-weight: 400; font-size: 0.9rem; color: var(--text-secondary);">{{ $manufacturer_address }}</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('general.verification.manufacturing_date') }}</span>
                    <span class="info-value">{{ $mfg_date }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('general.verification.license_number') }}</span>
                    <span class="info-value">{{ $license_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('general.verification.product_id') }}</span>
                    <span class="info-value" style="font-family: monospace; font-size: 1.1rem; letter-spacing: 0.5px;">{{ $asset->asset_tag }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ trans('general.verification.serial_number') }}</span>
                    <span class="info-value" style="font-family: monospace; font-size: 1.1rem; letter-spacing: 0.5px;">{{ $asset->serial ?: trans('general.na') }}</span>
                </div>

                <!-- Custom fields not mapped above -->
                @foreach($custom_fields as $field_name => $field_val)
                    <div class="info-item">
                        <span class="info-label">{{ $field_name }}</span>
                        <span class="info-value">{{ $field_val }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Toggle panel button -->
            <button class="toggle-btn" id="toggleBtn" onclick="toggleFields()">
                <span id="btnText">{{ trans('general.verification.show_more') }}</span>
                <i class="fa-solid fa-chevron-down" id="btnIcon"></i>
            </button>
        </div>

        <!-- Call to actions panel -->
        <div class="actions-container">
            <!-- Count of verifications display -->
            <div style="display:flex; justify-content: space-between; align-items:center; background:#f8fafc; padding:15px; border-radius:12px; border:1px solid var(--border-color); font-size:0.95rem;">
                <span style="color:var(--text-secondary); font-weight:500;">
                    <i class="fa-solid fa-fingerprint" style="color:var(--primary); margin-right:6px;"></i> 
                    {{ trans('general.verification.authentication_count') }}
                </span>
                <span style="font-weight:700; color:var(--text-primary); font-size:1.1rem; background:#ffffff; padding:4px 10px; border-radius:8px; border:1px solid var(--border-color);">
                    {{ $asset->scan_count ?: 1 }}
                </span>
            </div>

            @if($leaflet)
                <a href="{{ route('public.asset.leaflet', ['tag' => $asset->asset_tag, 'file_id' => $leaflet->id]) }}" target="_blank" class="btn-leaflet">
                    <i class="fa-regular fa-file-pdf"></i> {{ trans('general.verification.view_eleaflet') }}
                </a>
            @else
                <button class="btn-leaflet disabled" disabled>
                    <i class="fa-regular fa-file-pdf"></i> {{ trans('general.verification.view_eleaflet') }} ({{ trans('general.na') }})
                </button>
            @endif
        </div>
    </div>

    <!-- Language Selector Footer -->
    <div class="language-switcher">
        <a href="?locale=en" class="lang-link {{ app()->getLocale() == 'en-US' ? 'active' : '' }}">English</a>
        <a href="?locale=hi" class="lang-link {{ app()->getLocale() == 'hi-IN' ? 'active' : '' }}">हिन्दी</a>
    </div>

    <script>
        function toggleFields() {
            var panel = document.getElementById('collapsibleFields');
            var btnText = document.getElementById('btnText');
            var btnIcon = document.getElementById('btnIcon');
            
            if (panel.classList.contains('expanded')) {
                panel.classList.remove('expanded');
                btnText.innerText = "{{ trans('general.verification.show_more') }}";
                btnIcon.className = "fa-solid fa-chevron-down";
            } else {
                panel.classList.add('expanded');
                btnText.innerText = "{{ trans('general.verification.show_less') }}";
                btnIcon.className = "fa-solid fa-chevron-up";
            }
        }
    </script>
</body>
</html>

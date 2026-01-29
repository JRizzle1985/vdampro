
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      
      <title>
        @section('title')
         VDOT {{ trans('general.setup') }}
        @show
      </title>
      
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <link rel="stylesheet" href="{{ url(mix('css/dist/all.css')) }}">

        <script nonce="{{ csrf_token() }}">
            window.snipeit = {
                settings: {
                    "per_page": 20
                }
            };
        </script>

        <style>
          /* Inline overrides for critical setup page layout if LESS hasn't compiled yet */
          body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
          }
          .setup-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
          }
          .logo-header {
            text-align: center;
            margin-bottom: 40px;
          }
          .logo-header h1 {
            font-size: 2.5rem;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.025em;
          }
          .card-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
          }
          .card-header {
            background: #f8fafc;
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
          }
          .card-header h4 {
            margin: 0;
            font-size: 1.25rem;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
          }
          .card-body {
            padding: 40px;
          }
          .card-footer {
            background: #f8fafc;
            padding: 20px 30px;
            border-top: 1px solid #e2e8f0;
            text-align: right;
          }
          .footer-info {
            text-align: center;
            margin-top: 30px;
            color: #94a3b8;
            font-size: 0.875rem;
          }
          
          /* Cleaner Progress Steps */
          .steps-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
          }
          .steps-container::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
          }
          .step-item {
            position: relative;
            z-index: 1;
            text-align: center;
            background: #f8fafc;
            padding: 0 10px;
          }
          .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s;
          }
          .step-item.active .step-circle {
            border-color: #0ea5e9;
            background: #0ea5e9;
            color: white;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.2);
          }
          .step-item.complete .step-circle {
            border-color: #10b981;
            background: #10b981;
            color: white;
          }
          .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
          }
          .step-item.active .step-label {
            color: #0f172a;
          }
          
          /* Form cleanup */
          .form-group {
            margin-bottom: 24px;
          }
          label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #334155;
          }
          .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.2s;
          }
          .form-control:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
            outline: none;
          }
        </style>
    </head>
    <body>
          <div class="setup-container">
            
              <div class="logo-header">
                <h1>VDOT Pre-Flight</h1>
              </div>

              <!-- New Steps Indicator -->
              <div class="steps-container">
                  <div class="step-item {{ ($step > 1) ? 'complete': 'active' }}">
                    <div class="step-circle">
                      @if($step > 1) <i class="fas fa-check"></i> @else 1 @endif
                    </div>
                    <div class="step-label">Config</div>
                  </div>
                  
                  <div class="step-item {{ ($step == 2) ? 'active': (($step > 2) ? 'complete' : '') }}">
                    <div class="step-circle">
                      @if($step > 2) <i class="fas fa-check"></i> @else 2 @endif
                    </div>
                    <div class="step-label">Database</div>
                  </div>
                  
                  <div class="step-item {{ ($step == 3) ? 'active': (($step > 3) ? 'complete' : '') }}">
                    <div class="step-circle">
                      @if($step > 3) <i class="fas fa-check"></i> @else 3 @endif
                    </div>
                    <div class="step-label">Admin</div>
                  </div>
                  
                  <div class="step-item {{ ($step == 4) ? 'active': (($step > 4) ? 'complete' : '') }}">
                    <div class="step-circle">
                       @if($step > 4) <i class="fas fa-check"></i> @else 4 @endif
                    </div>
                    <div class="step-label">Done</div>
                  </div>
              </div>


              <div class="card-box">
                  <div class="card-header">
                      <h4><i class="{{ $icon ?? '' }}"></i> {{ $section }}</h4>
                  </div>
                  
                  <div class="card-body">
                      @include('notifications')
                      @yield('content')
                  </div>
                  
                  <div class="card-footer">
                      @section('button')
                      @show
                  </div>
              </div>

              <div class="footer-info">
                  <strong>VDOT {{ trans('general.version') }}</strong> {{ config('version.app_version') }}<br>
                  Build {{ config('version.build_version') }} ({{ config('version.branch') }})<br>
                  <small class="text-muted">
                    VDOT powered by <a href="https://github.com/grokability/snipe-it" target="_blank">Snipe-IT</a>.
                    Licensed under <a href="https://github.com/grokability/snipe-it/blob/master/LICENSE" target="_blank">AGPLv3</a>.
                  </small>
              </div>
          </div>
          
        {{-- Javascript files --}}
          <script src="{{ url('js/dist/all.js') }}" nonce="{{ csrf_token() }}"></script>

        <script nonce="{{ csrf_token() }}">
            $(function () {
                $(".select2").select2();
            });
        </script>
          @section('moar_scripts')
          @show

    </body>
</html>

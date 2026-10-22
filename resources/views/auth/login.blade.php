<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Login - Petrotechnical Platform</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <!-- CSS files -->
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet" integrity="sha384-GgnF119bh9fxkKuWHRQYSgEe1rSp5jB0EJ2W8eMf8mjowfwhZP2H1u8n8xJUW3FQ" crossorigin="anonymous" />
  <style>
    @import url('https://rsms.me/inter/inter.css');

    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }

    body {
      font-feature-settings: "cv03", "cv04", "cv11";
    }

    .logo-wrap {
      display: flex;
      justify-content: center;
      margin-top: -2.5rem;
      margin-bottom: 1.5rem;
    }

    .logo-icon {
      width: 76px;
      height: 76px;
      border-radius: 20px;
      background: linear-gradient(135deg, #2563eb 0%, #1a3c6b 60%, #0f2545 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow:
        0 8px 24px rgba(26, 60, 107, 0.35),
        0 2px 6px rgba(26, 60, 107, 0.2),
        inset 0 1px 0 rgba(255,255,255,0.15);
      position: relative;
      overflow: hidden;
    }

    .logo-icon::before {
      content: '';
      position: absolute;
      top: -18px;
      left: -18px;
      width: 60px;
      height: 60px;
      background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
      border-radius: 50%;
    }

    .logo-icon svg {
      position: relative;
      z-index: 1;
    }
  </style>
</head>

<body class="d-flex flex-column bg-white">
  <div class="row g-0 flex-fill">
    {{-- Illustration on the left --}}
    <div class="col-12 col-lg-6 col-xl-8 d-none d-lg-block">
      <div class="bg-cover h-100 min-vh-100"
        style="background-image: url(https://images.unsplash.com/photo-1550684848-fac1c5b4e853?q=80&w=2070&auto=format&fit=crop); background-color: #1a3c6b; background-blend-mode: multiply;">
        <div class="h-100 d-flex flex-column justify-content-center p-5 text-white">
          <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <h1 class="display-3 fw-bold mb-3">Petrotechnical Platform</h1>
            <p class="lead" style="color: rgba(255,255,255,0.8)">Application UC2 Cloud Infrastructure<br>Intelligent
              Virtual Desktop Access Management</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Form on the right --}}
    <div class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center">
      <div class="container container-tight my-5 px-lg-5">

        {{-- Premium P Logo --}}
        <div class="logo-wrap">
          <div class="logo-icon">
            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="p_letter" x1="10" y1="8" x2="36" y2="36" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#ffffff"/>
                  <stop offset="100%" stop-color="#b8d4f8"/>
                </linearGradient>
              </defs>
              {{-- Vertical stem --}}
              <line x1="13" y1="9" x2="13" y2="35" stroke="url(#p_letter)" stroke-width="5.5" stroke-linecap="round"/>
              {{-- Bowl of P --}}
              <path d="M13 9 L24 9 C31 9 35 13.5 35 19.5 C35 25.5 31 30 24 30 L13 30"
                stroke="url(#p_letter)" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
              {{-- Accent dot --}}
              <circle cx="33" cy="34" r="3" fill="#60a5fa" opacity="0.85"/>
            </svg>
          </div>
        </div>

        <div class="text-center mb-4">
          <h2 class="h2 text-center mb-4" style="color: #1a3c6b;">Login to your account</h2>
        </div>

        @if(session('status'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('status') }}
          <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" autocomplete="on">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
              placeholder="your@email.com" autocomplete="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label">
              Password
              @if (Route::has('password.request'))
              <span class="form-label-description">
                <a href="{{ route('password.request') }}" tabindex="-1">I forgot password</a>
              </span>
              @endif
            </label>
            <div class="input-group input-group-flat">
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                placeholder="Your password" autocomplete="current-password" required>
            </div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-check">
              <input type="checkbox" name="remember" class="form-check-input" id="remember_me" />
              <span class="form-check-label">Remember me on this device</span>
            </label>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn w-100 text-white" style="background:#1a3c6b;">Sign in</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" integrity="sha384-skr6f2xmgHOAc9USAHvYxA0lNy9tzSr5JIkdr9ytaCy2C/ofOu+OWd+d/hsnEJFN" crossorigin="anonymous"></script>
</body>

</html>
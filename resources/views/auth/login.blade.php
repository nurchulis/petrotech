<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title>Login - Petrotechnical Platform</title>
  <!-- CSS files -->
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet" />
  <style>
    @import url('https://rsms.me/inter/inter.css');

    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }

    body {
      font-feature-settings: "cv03", "cv04", "cv11";
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
            <p class="lead" style="color: rgba(255,255,255,0.8)">Pertamina UC2 Cloud Infrastructure<br>Intelligent
              Virtual Desktop Access Management</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Form on the right --}}
    <div class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center">
      <div class="container container-tight my-5 px-lg-5">
        <div class="text-center mb-5 pb-3 d-flex justify-content-center align-items-center gap-2"
          style="margin-top: -4.5rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
            stroke="#1a3c6b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="icon icon-tabler icons-tabler-outline icon-tabler-database">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" />
            <path d="M4 6v6a8 3 0 0 0 16 0v-6" />
            <path d="M4 12v6a8 3 0 0 0 16 0v-6" />
          </svg>
          <div class="text-start lh-sm">
            <div style="color:#1a3c6b; font-weight: 800; font-size: 1.25rem;">Petrotechnical</div>
            <div
              style="color:#4a7fa5; font-size: 0.8rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 600;">
              Platform</div>
          </div>
        </div>

        <div class="text-center mb-4" style="margin-top: 70px;">
          <h2 class="h2 text-center mb-4" style="color: #1a3c6b;">Login to your account</h2>
        </div>

        @if(session('status'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('status') }}
          <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" autocomplete="off">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
              placeholder="your@email.com" autocomplete="off" value="{{ old('email') }}" required autofocus>
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
                placeholder="Your password" autocomplete="off" required>
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
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>

</html>
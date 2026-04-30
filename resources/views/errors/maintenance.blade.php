<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance — Petrotechnical Platform</title>
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <style>
        body {
            background-color: #eef2f6;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        /* Oil/Molecule Animation Background */
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
            overflow: hidden;
        }

        .molecule {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
        }

        .molecule:nth-child(1) {
            width: 400px;
            height: 400px;
            background: #1a3c6b;
            top: 20%;
            left: 20%;
            animation: float1 25s infinite ease-in-out alternate;
        }

        .molecule:nth-child(2) {
            width: 600px;
            height: 600px;
            background: #e8731a;
            bottom: 10%;
            right: 10%;
            animation: float2 28s infinite ease-in-out alternate;
        }

        .molecule:nth-child(3) {
            width: 350px;
            height: 350px;
            background: #4a7fa5;
            top: 60%;
            left: 40%;
            animation: float3 22s infinite ease-in-out alternate;
        }

        @keyframes float1 {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30vw, -20vh) scale(1.2); }
            66% { transform: translate(-20vw, 30vh) scale(0.8); }
            100% { transform: translate(0, 0) scale(1); }
        }

        @keyframes float2 {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-40vw, 20vh) scale(1.1); }
            66% { transform: translate(-10vw, -30vh) scale(1.3); }
            100% { transform: translate(0, 0) scale(1); }
        }

        @keyframes float3 {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40vw, -10vh) scale(0.9); }
            66% { transform: translate(20vw, -40vh) scale(1.2); }
            100% { transform: translate(0, 0) scale(1); }
        }

        .maintenance-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .maintenance-card {
            max-width: 500px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
            text-align: center;
            padding: 3rem 2rem;
            border-top: 4px solid #1a3c6b;
        }

        .brand-logo svg {
            margin-bottom: 1.5rem;
        }

        .maintenance-title {
            color: #1a3c6b;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .maintenance-desc {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .contact-support {
            font-size: 0.85rem;
            color: #adb5bd;
        }

        .contact-support a {
            color: #e8731a;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-support a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="antialiased border-top-wide border-primary d-flex flex-column">
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="molecule"></div>
        <div class="molecule"></div>
        <div class="molecule"></div>
    </div>

    <div class="maintenance-container">
        <div class="maintenance-card">
            <div class="brand-logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 42 42" fill="none">
                    <rect x="2" y="2" width="38" height="38" rx="10" fill="#1a3c6b" />
                    <rect x="2" y="2" width="38" height="38" rx="10" fill="url(#grad_sidebar_p)" fill-opacity="0.8" />
                    <path
                        d="M14 12h8.5c4.14 0 7.5 3.36 7.5 7.5S26.64 27 22.5 27H18v5h-4V12zm4 4v7h4.5c1.93 0 3.5-1.57 3.5-3.5S24.43 16 22.5 16H18z"
                        fill="#ffffff" />
                    <defs>
                        <linearGradient id="grad_sidebar_p" x1="2" y1="2" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#4a7fa5" />
                            <stop offset="1" stop-color="#1a3c6b" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            
            <h1 class="maintenance-title">System Maintenance</h1>
            
            <p class="maintenance-desc">
                We're currently performing scheduled maintenance to improve our services and upgrade the Petrotechnical Platform. 
                <br><br>
                Please check back shortly. We apologize for any inconvenience caused.
            </p>
            
            <div class="contact-support">
                Need urgent help? <a href="mailto:support@petrotech.pertamina.com">Contact Support</a>
            </div>
        </div>
    </div>
</body>

</html>

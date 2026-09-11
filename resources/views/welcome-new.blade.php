<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIS Service Request System - WVSU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #0A0F1E;
            --primary-darker: #060B14;
            --footer-dark: #050A12;
            --accent-orange: #E8431A;
            --card-dark: #111827;
            --card-border: rgba(255, 255, 255, 0.08);
            --text-muted: #A0AEC0;
            --text-white: #FFFFFF;
        }

        * {
            font-family: 'DM Sans', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
        }

        body {
            background-color: var(--primary-dark);
            color: var(--text-white);
        }

        /* Dot Grid Background */
        .dot-grid {
            background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 50px 50px;
            background-position: 0 0, 25px 25px;
        }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
        }

        /* Glow Effects */
        .glow-orange {
            box-shadow: 0 0 40px rgba(232, 67, 26, 0.2);
        }

        .glow-orange-sm {
            box-shadow: 0 0 24px rgba(232, 67, 26, 0.15);
        }

        /* Navbar Styles */
        .navbar {
            background: transparent;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: var(--primary-darker);
            box-shadow: 0 4px 40px rgba(0, 0, 0, 0.3);
        }

        .nav-link {
            position: relative;
            color: var(--text-white);
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-orange);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-primary {
            background: var(--accent-orange);
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: scale(1.04);
            box-shadow: 0 0 30px rgba(232, 67, 26, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-white);
            border: 2px solid var(--text-white);
            padding: 11px 27px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: var(--accent-orange);
            border-color: var(--accent-orange);
            transform: scale(1.04);
        }

        /* Hero Styles */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-darker) 100%);
            position: relative;
            display: flex;
            align-items: center;
            padding-top: 80px;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(232, 67, 26, 0.1) 1px, transparent 1px);
            background-size: 80px 80px;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            padding: 0 5%;
        }

        @media (max-width: 768px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        .hero-left h1 {
            font-size: clamp(2.5rem, 8vw, 4.5rem);
            line-height: 1.1;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: -0.02em;
        }

        .hero-left .highlight {
            color: var(--accent-orange);
        }

        .hero-left p {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 32px;
            max-width: 500px;
            line-height: 1.8;
        }

        .hero-left .btn-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .hero-right {
            display: none;
        }

        @media (min-width: 1024px) {
            .hero-right {
                display: block;
            }
        }

        .floating-card {
            position: relative;
            background: var(--card-dark);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(20px);
            animation: float 6s ease-in-out infinite;
            box-shadow: 0 0 40px rgba(232, 67, 26, 0.2);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-delay-1 {
            animation-delay: 0.1s;
        }

        .anim-delay-2 {
            animation-delay: 0.2s;
        }

        .anim-delay-3 {
            animation-delay: 0.3s;
        }

        /* Stats Band */
        .stats-band {
            background: var(--card-dark);
            border-top: 1px solid var(--card-border);
            border-bottom: 1px solid var(--card-border);
            padding: 60px 5%;
            margin: 80px 0;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }

        .stat-item {
            text-align: center;
            animation: fade-in-up 0.6s ease forwards;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--accent-orange);
            line-height: 1;
            margin-bottom: 12px;
            font-family: 'Barlow Condensed', sans-serif;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 16px;
        }

        /* Services Section */
        .services-section {
            padding: 100px 5%;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .section-label {
            display: inline-block;
            color: var(--accent-orange);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 20px;
            padding: 8px 16px;
            border: 1px solid rgba(232, 67, 26, 0.3);
            border-radius: 50px;
        }

        .section-title {
            font-size: clamp(2rem, 6vw, 3.5rem);
            line-height: 1.2;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 32px;
            margin-top: 60px;
        }

        .service-card {
            background: var(--card-dark);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 40px 32px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            animation: fade-in-up 0.6s ease forwards;
        }

        .service-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .service-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .service-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .service-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 0 24px rgba(232, 67, 26, 0.15);
            border-color: rgba(232, 67, 26, 0.3);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-orange), rgba(232, 67, 26, 0.6));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(232, 67, 26, 0.3);
        }

        .service-card h3 {
            font-size: 1.5rem;
            margin-bottom: 16px;
            transition: color 0.3s ease;
        }

        .service-card:hover h3 {
            color: var(--accent-orange);
        }

        .service-card p {
            color: var(--text-muted);
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .service-card a {
            color: var(--accent-orange);
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .service-card a:hover {
            transform: translateX(6px);
        }

        /* Why Digital Section */
        .why-digital {
            padding: 100px 5%;
            background: linear-gradient(135deg, rgba(10, 15, 30, 0.8) 0%, rgba(6, 11, 20, 0.8) 100%);
            margin: 80px 0;
        }

        .why-digital-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .why-digital-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }

        .why-digital-image {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            aspect-ratio: 1;
        }

        .why-digital-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .why-digital-badge {
            position: absolute;
            top: 30px;
            left: 30px;
            background: var(--card-dark);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 16px 24px;
            backdrop-filter: blur(12px);
            z-index: 10;
        }

        .why-digital-badge-number {
            font-size: 2rem;
            font-weight: 900;
            color: var(--accent-orange);
            font-family: 'Barlow Condensed', sans-serif;
        }

        .why-digital-badge-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 4px;
        }

        .why-digital-content h2 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            line-height: 1.2;
            margin-bottom: 32px;
            text-transform: uppercase;
        }

        .feature-row {
            display: flex;
            gap: 20px;
            margin-bottom: 32px;
            padding: 24px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .feature-row:hover {
            background: rgba(232, 67, 26, 0.08);
            border-color: rgba(232, 67, 26, 0.2);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent-orange), rgba(232, 67, 26, 0.6));
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .feature-content h4 {
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .feature-content p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: var(--footer-dark);
            border-top: 1px solid var(--card-border);
            padding: 80px 5%;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 60px;
            margin-bottom: 60px;
        }

        .footer-section h3 {
            color: var(--accent-orange);
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .footer-section a,
        .footer-section p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 2;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--accent-orange);
        }

        .footer-bottom {
            border-top: 1px solid var(--card-border);
            padding-top: 40px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            background: var(--primary-darker);
            border-bottom: 1px solid var(--card-border);
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 40;
            animation: slide-down 0.3s ease;
        }

        @keyframes slide-down {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-menu-hidden {
            display: none;
        }

        /* Animations */
        .animate-in {
            animation: fade-in-up 0.6s ease forwards;
        }

        .count-animation {
            animation: count-up 2s ease-out forwards;
        }

        @keyframes count-up {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding-top: 100px;
            }

            .hero-left h1 {
                font-size: 2.5rem;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .why-digital {
                padding: 60px 5%;
            }

            footer {
                padding: 60px 5%;
            }

            .footer-container {
                gap: 40px;
            }
        }

        /* University Badge */
        .uni-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: rgba(232, 67, 26, 0.1);
            border: 1px solid rgba(232, 67, 26, 0.3);
            border-radius: 50px;
            margin-bottom: 24px;
            color: var(--accent-orange);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Hero Features */
        .hero-features {
            display: flex;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .feature-check {
            width: 24px;
            height: 24px;
            background: rgba(232, 67, 26, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-orange);
            flex-shrink: 0;
            font-weight: 700;
        }

        /* Scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar fixed top-0 w-full z-50 px-5% py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Logo -->
            <a href="#home" class="flex items-center gap-3 cursor-pointer group">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white font-black text-xl transform group-hover:scale-110 transition-transform duration-300">M</div>
                <div class="text-lg font-bold text-white hidden sm:block group-hover:text-orange-500 transition-colors">MIS Request</div>
            </a>

            <!-- Desktop Menu -->
            <ul class="hidden md:flex items-center gap-10 list-none">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#services" class="nav-link">Services</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="{{ route('login') }}" class="nav-link">Login</a></li>
                <li><a href="{{ route('register') }}" class="btn-primary">Get Started</a></li>
            </ul>

            <!-- Mobile Menu Button -->
            <button class="md:hidden text-2xl text-white hover:text-orange-500 transition-colors" onclick="toggleMobileMenu()">☰</button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu mobile-menu-hidden">
        <div class="flex flex-col gap-4 p-6">
            <a href="#home" onclick="toggleMobileMenu()" class="nav-link py-2">Home</a>
            <a href="#services" onclick="toggleMobileMenu()" class="nav-link py-2">Services</a>
            <a href="#about" onclick="toggleMobileMenu()" class="nav-link py-2">About</a>
            <a href="{{ route('login') }}" class="nav-link py-2">Login</a>
            <a href="{{ route('register') }}" class="btn-primary w-full text-center">Get Started</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <!-- Left Content -->
            <div class="hero-left animate-in">
                <div class="uni-badge">🎓 West Visayas State University</div>
                <h1>Streamline Your <span class="highlight">IT Requests</span></h1>
                <p>Efficient, digital, and transparent MIS service request management system. Submit, track, and resolve IT issues faster than ever.</p>

                <div class="btn-group">
                    <a href="{{ route('register') }}" class="btn-primary">Get Started →</a>
                    <a href="#services" class="btn-secondary">Learn More ▶</a>
                </div>

                <div class="hero-features">
                    <div class="feature-item">
                        <div class="feature-check">✓</div>
                        <span>24/7 Support</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-check">✓</div>
                        <span>Fast Response</span>
                    </div>
                </div>
            </div>

            <!-- Right Content - Floating Card (Desktop Only) -->
            <div class="hero-right">
                <div class="floating-card">
                    <div class="mb-6">
                        <div class="text-4xl mb-4">🚀</div>
                        <h3 class="text-xl font-bold mb-3">Instant Request Submission</h3>
                        <p class="text-sm text-gray-400">Submit your IT requests instantly and receive real-time updates on processing status.</p>
                    </div>
                    <div class="pt-6 border-t border-gray-700">
                        <div class="text-sm text-gray-500">✓ Real-time tracking</div>
                        <div class="text-sm text-gray-500">✓ Transparent updates</div>
                        <div class="text-sm text-gray-500">✓ Expert support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Band -->
    <div class="stats-band">
        <div class="stats-container">
            <div class="stat-item anim-delay-1">
                <div class="stat-icon">👥</div>
                <div class="stat-number count-animation" data-target="200">200</div>
                <div class="stat-label">Requests Processed</div>
            </div>
            <div class="stat-item anim-delay-2">
                <div class="stat-icon">✓</div>
                <div class="stat-number count-animation" data-target="100">100</div>
                <div class="stat-label">% Digital Workflow</div>
            </div>
            <div class="stat-item anim-delay-3">
                <div class="stat-icon">🎯</div>
                <div class="stat-number count-animation" data-target="1000">1K</div>
                <div class="stat-label">Issues Resolved</div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="section-label">✦ OUR SERVICES</div>
        <h2 class="section-title">Comprehensive <span class="text-orange-500">MIS Support</span></h2>

        <div class="services-grid">
            <!-- Service 1 -->
            <div class="service-card">
                <div class="service-icon">🌐</div>
                <h3>Network & Telephone</h3>
                <p>Network installation, troubleshooting, cabling, and telephone line services for seamless connectivity.</p>
                <a href="{{ route('register') }}">Learn More →</a>
            </div>

            <!-- Service 2 -->
            <div class="service-card">
                <div class="service-icon">💻</div>
                <h3>ICT Equipment</h3>
                <p>Computer troubleshooting, printer repair, hardware installation, and equipment maintenance services.</p>
                <a href="{{ route('register') }}">Learn More →</a>
            </div>

            <!-- Service 3 -->
            <div class="service-card">
                <div class="service-icon">⚙️</div>
                <h3>Software & Applications</h3>
                <p>User account management, software installation, data backup, and technical support.</p>
                <a href="{{ route('register') }}">Learn More →</a>
            </div>

            <!-- Service 4 -->
            <div class="service-card">
                <div class="service-icon">🌍</div>
                <h3>Web Administration</h3>
                <p>Email account setup, website updates, content management, and multimedia file hosting.</p>
                <a href="{{ route('register') }}">Learn More →</a>
            </div>
        </div>
    </section>

    <!-- Why Digital Section -->
    <section class="why-digital" id="about">
        <div class="why-digital-container">
            <!-- Image -->
            <div class="why-digital-image">
                <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=600&h=600&fit=crop" alt="WVSU">
                <div class="why-digital-badge">
                    <div class="why-digital-badge-number">25+</div>
                    <div class="why-digital-badge-label">Years of Service</div>
                </div>
            </div>

            <!-- Content -->
            <div class="why-digital-content animate-in">
                <div class="section-label">WHY DIGITAL?</div>
                <h2>Transform <span class="text-orange-500">Manual Processes</span></h2>
                <p style="color: var(--text-muted); line-height: 1.8; margin: 24px 0; font-size: 1rem;">WVSU recognizes the challenges of manual service requests - delays, lost forms, and lack of transparency. Our system brings digitalization to streamline operations, improve response times, and enhance service quality for our entire academic community.</p>

                <div class="feature-row">
                    <div class="feature-icon">🚀</div>
                    <div class="feature-content">
                        <h4>Faster Processing</h4>
                        <p>Submit requests instantly and track progress in real-time with our intuitive dashboard.</p>
                    </div>
                </div>

                <div class="feature-row">
                    <div class="feature-icon">📊</div>
                    <div class="feature-content">
                        <h4>Complete Transparency</h4>
                        <p>View detailed history and status updates of all your requests anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <!-- Brand -->
            <div class="footer-section">
                <h3>MIS Service Request</h3>
                <p>West Visayas State University</p>
                <p>Luna Street, La Paz</p>
                <p>Iloilo City, Philippines</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="#home">Home</a>
                <a href="#services">Services</a>
                <a href="#about">About</a>
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Sign Up</a>
            </div>

            <!-- Contact -->
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><strong>Email:</strong> <a href="mailto:mis@wvsu.edu.ph">mis@wvsu.edu.ph</a></p>
                <p><strong>Phone:</strong> (033) 320-0870</p>
                <p><strong>Hours:</strong> Mon-Fri 8AM - 5PM</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <span id="currentYear"></span> West Visayas State University. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Set current year
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('mobile-menu-hidden');
        }

        // Close mobile menu on link click
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', toggleMobileMenu);
        });

        // Counter animation for stats
        function animateCounter() {
            const counters = document.querySelectorAll('.count-animation');
            const options = {
                threshold: 0.5,
                rootMargin: '0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = parseInt(counter.dataset.target);
                        let current = 0;
                        const increment = target / 50;
                        const timer = setInterval(() => {
                            if (current >= target) {
                                clearInterval(timer);
                                counter.textContent = target + (target === 100 ? '' : '+');
                            } else {
                                current += increment;
                                counter.textContent = Math.floor(current) + (target === 100 ? '' : '+');
                            }
                        }, 30);
                        observer.unobserve(counter);
                    }
                });
            }, options);

            counters.forEach(counter => observer.observe(counter));
        }

        // Initialize animations on scroll
        document.addEventListener('DOMContentLoaded', () => {
            animateCounter();

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
require_once 'config/config.php';

$page_title = "Digital Feedback and Complaint Management System";
$base_path = '';
$nav_transparent = false; // Makes navbar clean white instead of transparent
$extra_css = '
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
    /* Landing Page Specific Custom CSS */
    :root {
        --landing-dark: #0f172a;
        --landing-accent: #10b981;
        --landing-accent-hover: #059669;
        --landing-secondary: #6366f1;
        --landing-bg: #f8fafc;
    }
    
    body.landing-page {
        font-family: "Inter", sans-serif;
        background-color: var(--landing-bg);
        color: #334155;
    }
    
    /* Navigation Override */
    .top-navbar.navbar {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 0;
    }
    
    .navbar .btn-light {
        background: transparent !important;
        color: #475569 !important;
        border: 1px solid transparent !important;
        transition: all 0.2s ease;
    }
    
    .navbar .btn-light:hover {
        color: #0f172a !important;
        background: rgba(0,0,0,0.03) !important;
    }
    
    .navbar .btn-primary {
        background-color: var(--landing-secondary) !important;
        color: white !important;
        border: none !important;
        transition: all 0.2s ease;
    }
    
    .navbar .btn-primary:hover {
        background-color: #4f46e5 !important;
        transform: translateY(-1px);
    }

    /* Buttons */
    .btn-mint {
        background-color: var(--landing-accent);
        color: white;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }
    .btn-mint:hover {
        background-color: var(--landing-accent-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
    }

    /* Hero Section */
    .hero-section {
        background-color: var(--landing-dark);
        position: relative;
        overflow: hidden;
        padding: 9rem 0 7rem;
        color: white;
    }
    .hero-glow {
        position: absolute;
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, rgba(15,23,42,0) 70%);
        pointer-events: none;
        z-index: 1;
    }
    .hero-content {
        position: relative;
        z-index: 2;
    }
    .trust-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: var(--landing-accent);
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.875rem;
        margin-bottom: 2rem;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
    }
    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
    }
    .text-mint { color: var(--landing-accent) !important; }
    
    .hero-subtitle {
        font-size: 1.25rem;
        color: #94a3b8;
        max-width: 650px;
        margin: 0 auto 2.5rem;
        font-weight: 400;
        line-height: 1.6;
    }
    
    .trust-checks {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-bottom: 4rem;
        flex-wrap: wrap;
    }
    .trust-checks .check-item {
        color: #cbd5e1;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    .stats-row {
        display: flex;
        justify-content: center;
        gap: 4.5rem;
        margin-bottom: 4rem;
        flex-wrap: wrap;
    }
    .stat-box { text-align: center; }
    .stat-num {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--landing-accent);
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .stat-text {
        font-size: 0.875rem;
        color: #cbd5e1;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    /* Features Section */
    .features-section {
        background-color: var(--landing-bg);
        padding: 7rem 0;
    }
    .feature-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 2.5rem 2rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        height: 100%;
        border: none;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .feature-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.75rem;
        font-size: 1.5rem;
    }
    .icon-green { background: rgba(16, 185, 129, 0.1); color: var(--landing-accent); }
    .icon-indigo { background: rgba(99, 102, 241, 0.1); color: var(--landing-secondary); }
    .icon-blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

    /* Testimonial Section */
    .testimonial-section {
        background-color: var(--landing-dark);
        padding: 7rem 0;
        position: relative;
        overflow: hidden;
    }
    .testimonial-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 700px;
        height: 700px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(15,23,42,0) 70%);
        pointer-events: none;
    }
    .testimonial-card {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 20px;
        padding: 4rem 3rem;
        max-width: 850px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .testimonial-card::before {
        content: "";
        position: absolute;
        inset: -5px;
        background: radial-gradient(circle at 50% 50%, var(--landing-accent) 0%, var(--landing-secondary) 100%);
        border-radius: 25px;
        z-index: -1;
        filter: blur(40px);
        opacity: 0.5;
    }
    .stars { color: #fbbf24; margin-bottom: 1.5rem; font-size: 1.25rem; letter-spacing: 0.2rem; }
    .quote-text {
        font-size: 1.75rem;
        color: #ffffff;
        line-height: 1.6;
        font-style: italic;
        margin-bottom: 2.5rem;
        font-weight: 500;
    }
    .author-avatar {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--landing-accent) 0%, #34d399 100%);
        border-radius: 50%;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
    }
    
    /* Footer */
    .landing-footer {
        background: #ffffff;
        padding: 5rem 0 2rem;
    }
    .footer-title { font-weight: 700; margin-bottom: 1.5rem; color: var(--landing-dark); }
    .footer-link {
        color: #64748b;
        text-decoration: none;
        display: block;
        margin-bottom: 0.85rem;
        transition: color 0.2s;
        font-weight: 500;
    }
    .footer-link:hover { color: var(--landing-secondary); }
    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 50%;
        margin-left: 0.75rem;
        transition: all 0.2s;
        font-size: 1.1rem;
    }
    .social-icon:hover {
        background: var(--landing-dark);
        color: white;
        transform: translateY(-2px);
    }
    
    /* Floating Dashboard Mockup */
    @keyframes floatMockup {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
    .hero-login-card {
        background: #1e293b;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 40px rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(255,255,255,0.05);
        padding: 3rem;
        position: relative;
        z-index: 10;
        animation: floatMockup 6s ease-in-out infinite;
    }
    .hero-login-input {
        background: #eff6ff;
        border: none;
        color: #0f172a;
        padding-left: 2.75rem;
    }
    .hero-login-input:focus {
        background: #ffffff;
        box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
    }
    .input-icon-left {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        color: var(--landing-accent);
        z-index: 5;
    }
    .input-icon-right {
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 5;
        cursor: pointer;
    }
    .btn-hero-login {
        background: var(--landing-accent);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        border: none;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }
    .btn-hero-login:hover {
        background: var(--landing-accent-hover);
        transform: translateY(-2px);
    }
    /* Pre-Footer CTA */
    .pre-footer-cta {
        background: linear-gradient(135deg, var(--landing-secondary) 0%, #3730a3 100%);
        padding: 6rem 0;
        text-align: center;
        color: white;
    }
    .cta-headline {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }
    .cta-subhead {
        font-size: 1.25rem;
        color: #e0e7ff;
        margin-bottom: 2.5rem;
        font-weight: 400;
    }
    .btn-white-solid {
        background: #ffffff;
        color: var(--landing-dark);
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-white-solid:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .btn-outline-transparent {
        border: 2px solid rgba(255, 255, 255, 0.8);
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        background: transparent;
    }
    .btn-outline-transparent:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .hero-title { font-size: 2.5rem; }
        .stat-num { font-size: 2.5rem; }
        .stats-row { gap: 2rem; }
        .quote-text { font-size: 1.25rem; }
    }
</style>
';

include 'components/head.php';
?>
<body class="landing-page">
    <?php include 'components/navbar.php'; ?>

    <!-- Premium Hero Section -->
    <section class="hero-section">
        <div class="hero-glow"></div>
        <div class="container hero-content">
            <div class="row align-items-center min-vh-100">
                <!-- Left Column -->
                <div class="col-lg-6 text-start mb-5 mb-lg-0" data-aos="fade-right" data-aos-duration="1000">
                    <div class="trust-badge d-inline-block mb-4">
                        🚀 Trusted by 500+ Universities
                    </div>
                    
                    <h1 class="hero-title mb-4">
                        DFCMS: <span class="text-mint">Transforming</span><br> Institutional Excellence
                    </h1>
                    
                    <p class="hero-subtitle mb-4 text-start ms-0" style="color: #cbd5e1; max-width: 100%;">
                        The most advanced feedback management platform for universities, built with cutting-edge technology and premium design to streamline communications.
                    </p>
                    
                    <div class="trust-checks d-flex flex-wrap gap-3 mb-5 justify-content-start">
                        <div class="check-item"><i class="bi bi-check-circle-fill text-mint"></i> Enterprise security</div>
                        <div class="check-item"><i class="bi bi-check-circle-fill text-mint"></i> AI-powered</div>
                        <div class="check-item"><i class="bi bi-check-circle-fill text-mint"></i> Seamless integration</div>
                    </div>
                    
                    <div class="stats-row justify-content-start" style="gap: 3rem !important;">
                        <div class="stat-box text-start">
                            <div class="stat-num">10K+</div>
                            <div class="stat-text">Active Users</div>
                        </div>
                        <div class="stat-box text-start">
                            <div class="stat-num">95%</div>
                            <div class="stat-text">Satisfaction</div>
                        </div>
                        <div class="stat-box text-start">
                            <div class="stat-num">24/7</div>
                            <div class="stat-text">Support</div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Floating Form) -->
                <div class="col-lg-5 offset-lg-1" data-aos="fade-left" data-aos-delay="200" data-aos-duration="1000">
                    <div class="hero-login-card">
                        <div class="text-center mb-4">
                            <div class="feature-icon-wrapper mx-auto icon-green mb-3"><i class="bi bi-bank"></i></div>
                            <h3 class="text-white fw-bold mb-1">Welcome Back</h3>
                            <p class="text-secondary mb-0">Sign in to access your dashboard</p>
                        </div>

                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger py-2 px-3 mb-4 rounded-3 text-start small border-0" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5;">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <?php 
                                    echo $_SESSION['login_error']; 
                                    unset($_SESSION['login_error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="auth/login.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generate(); ?>">
                            <div class="mb-4">
                                <label class="form-label text-white mb-2"><i class="bi bi-envelope-fill text-mint me-2"></i>Email Address</label>
                                <div class="position-relative">
                                    <i class="bi bi-envelope input-icon-left"></i>
                                    <input type="email" name="email" class="form-control form-control-lg hero-login-input border-0" placeholder="admin@university.edu" value="<?php echo isset($_SESSION['login_email']) ? htmlspecialchars($_SESSION['login_email']) : ''; ?>" required>
                                    <?php unset($_SESSION['login_email']); ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-white mb-2"><i class="bi bi-lock-fill text-mint me-2"></i>Password</label>
                                <div class="position-relative">
                                    <i class="bi bi-lock input-icon-left"></i>
                                    <input type="password" name="password" id="loginPassword" class="form-control form-control-lg hero-login-input border-0" placeholder="••••••••" required>
                                    <i class="bi bi-eye input-icon-right" id="togglePassword"></i>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4 pt-2">
                                <div class="form-check">
                                    <input class="form-check-input bg-dark border-secondary" type="checkbox" id="rememberMe">
                                    <label class="form-check-label text-secondary" for="rememberMe">Remember me</label>
                                </div>
                                <a href="#" class="text-mint text-decoration-none">Forgot password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-hero-login w-100 rounded-pill btn-lg d-flex align-items-center justify-content-center border-0">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center mb-5 pb-4">
                <h2 class="fw-bold mb-3" style="color: var(--landing-dark); font-size: 2.75rem;">Features</h2>
                <p class="text-secondary fs-5" style="max-width: 600px; margin: 0 auto;">Experience the future of institutional feedback management with our cutting-edge platform.</p>
            </div>
            
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper icon-green"><i class="bi bi-robot"></i></div>
                        <h4 class="fw-bold mb-3" style="color: var(--landing-dark);">AI-Powered Insights</h4>
                        <p class="text-secondary mb-0 line-height-lg">Advanced analytics and machine learning provide actionable intelligence for your institution.</p>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper icon-indigo"><i class="bi bi-lightning-charge-fill"></i></div>
                        <h4 class="fw-bold mb-3" style="color: var(--landing-dark);">Lightning Fast</h4>
                        <p class="text-secondary mb-0 line-height-lg">Optimized performance with sub-second response times globally, ensuring a smooth experience.</p>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper icon-blue"><i class="bi bi-people-fill"></i></div>
                        <h4 class="fw-bold mb-3" style="color: var(--landing-dark);">Team Collaboration</h4>
                        <p class="text-secondary mb-0 line-height-lg">Seamless workflows and real-time collaboration across all departments and faculties.</p>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper icon-indigo"><i class="bi bi-bar-chart-fill"></i></div>
                        <h4 class="fw-bold mb-3" style="color: var(--landing-dark);">Advanced Analytics</h4>
                        <p class="text-secondary mb-0 line-height-lg">Comprehensive reporting and dashboards designed specifically for data-driven administrative decisions.</p>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper icon-blue"><i class="bi bi-phone-fill"></i></div>
                        <h4 class="fw-bold mb-3" style="color: var(--landing-dark);">Mobile First</h4>
                        <p class="text-secondary mb-0 line-height-lg">Fully responsive design works perfectly on all devices, from desktops to responsive smartphones.</p>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper icon-green"><i class="bi bi-shield-lock-fill"></i></div>
                        <h4 class="fw-bold mb-3" style="color: var(--landing-dark);">Enterprise Security</h4>
                        <p class="text-secondary mb-0 line-height-lg">Bank-level encryption and strict ISO compliance standards protect your most sensitive ecosystem data.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="testimonial-section">
        <div class="testimonial-glow"></div>
        <div class="container">
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <h3 class="quote-text">"DFCMS revolutionized how we handle student feedback. The platform is intuitive, powerful, and has significantly improved our response times."</h3>
                <div class="author-avatar">SJ</div>
                <div class="fw-bold text-white fs-5 mb-1">Dr. Sarah Johnson</div>
                <div style="color: #94a3b8; font-weight: 500;">Dean of Student Affairs</div>
            </div>
        </div>
    </section>

    <!-- Pre-Footer CTA Section -->
    <section class="pre-footer-cta">
        <div class="container">
            <h2 class="cta-headline">Ready to transform your institution?</h2>
            <p class="cta-subhead">Join 500+ universities using DFCMS today.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="auth/register.php" class="btn btn-white-solid rounded-pill px-5 py-3 fs-6">Get Started Now</a>
                <a href="#" class="btn btn-outline-transparent rounded-pill px-5 py-3 fs-6">Talk to Sales</a>
            </div>
        </div>
    </section>

    <!-- Clean Light Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row mb-5 pb-3">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <a class="navbar-brand fw-bold fs-3 mb-3 d-flex align-items-center gap-2 text-decoration-none" href="#" style="color: var(--landing-dark);">
                        <i class="bi bi-shield-check text-mint"></i> DFCMS
                    </a>
                    <p class="text-secondary pe-lg-5" style="line-height: 1.7;">Transforming institutions through innovative technology and advanced, AI-powered feedback management solutions natively built for higher education.</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Product</h5>
                    <a href="#features" class="footer-link">Features</a>
                    <a href="#" class="footer-link">Pricing</a>
                    <a href="#" class="footer-link">Security</a>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-title">Company</h5>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Careers</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="footer-title">Support</h5>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">Documentation</a>
                    <a href="#" class="footer-link">System Status</a>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-4 border-top">
                <p class="text-secondary fw-500 mb-3 mb-md-0">&copy; 2024 DFCMS. All rights reserved.</p>
                <div class="d-flex">
                    <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-github"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Password Visibility Toggle
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#loginPassword');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    </script>
</body>
</html>

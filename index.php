<?php
require_once 'config/config.php';

$page_title = "The Core Platform Engine";
$base_path = '';
$extra_css = '<link href="assets/css/landing.css?v=' . time() . '" rel="stylesheet">';
$nav_transparent = true;
include 'components/head.php';
?>
<body class="landing-page">
    <?php include 'components/navbar.php'; ?>

    <!-- Ultra-Premium Hero Section -->
    <section class="hero-ultra" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="hero-background">
            <div class="animated-gradient-bg"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            <div class="particle-field"></div>
        </div>

        <div class="hero-content" style="position: relative; z-index: 3; max-width: 1200px; width: 100%; text-align: center; color: white;">
            <div class="hero-badge" style="margin-bottom: 2rem;">
                <span class="badge-text" style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 50px; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #10b981;">🚀 Trusted by 500+ Universities</span>
            </div>

            <div class="hero-brand" style="margin-bottom: 2rem;">
                <div class="brand-icon-premium" style="position: relative; width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; box-shadow: 0 20px 60px rgba(16, 185, 129, 0.4);">
                    <i class="fas fa-university"></i>
                    <div class="icon-glow" style="position: absolute; top: -10px; left: -10px; right: -10px; bottom: -10px; background: linear-gradient(45deg, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0.1)); border-radius: 50%; animation: glowPulse 3s ease-in-out infinite;"></div>
                </div>
                <h1 class="brand-title" style="font-size: 3rem; font-weight: 700; background: linear-gradient(135deg, #ffffff 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0; letter-spacing: -0.02em;">DFCMS</h1>
            </div>

            <div class="hero-headline" style="margin-bottom: 2rem;">
                <h2 class="headline-main" style="font-size: 5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -0.03em;">
                    <span class="text-gradient" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Transforming</span><br>
                    <span class="text-emphasis" style="background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Institutional Excellence</span>
                </h2>
                <p class="headline-subtitle" style="font-size: 1.25rem; color: #cbd5e1; line-height: 1.6; max-width: 600px; margin: 0 auto; font-weight: 400;">
                    The most advanced feedback management platform for universities, built with cutting-edge technology and premium design.
                </p>
            </div>

            <div class="hero-features" style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
                <div class="feature-bullet" style="display: flex; align-items: center; gap: 0.75rem; color: #cbd5e1; font-size: 0.95rem; font-weight: 500;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.125rem;"></i>
                    <span>Enterprise-grade security & compliance</span>
                </div>
                <div class="feature-bullet" style="display: flex; align-items: center; gap: 0.75rem; color: #cbd5e1; font-size: 0.95rem; font-weight: 500;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.125rem;"></i>
                    <span>AI-powered insights & analytics</span>
                </div>
                <div class="feature-bullet" style="display: flex; align-items: center; gap: 0.75rem; color: #cbd5e1; font-size: 0.95rem; font-weight: 500;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.125rem;"></i>
                    <span>Seamless integration & automation</span>
                </div>
            </div>

            <div class="hero-stats" style="display: flex; justify-content: center; gap: 3rem; margin-bottom: 3rem;">
                <div class="stat-item" style="text-align: center;">
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 700; color: #10b981; margin-bottom: 0.5rem;">10K+</div>
                    <div class="stat-label" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Active Users</div>
                </div>
                <div class="stat-item" style="text-align: center;">
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 700; color: #10b981; margin-bottom: 0.5rem;">95%</div>
                    <div class="stat-label" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Satisfaction</div>
                </div>
                <div class="stat-item" style="text-align: center;">
                    <div class="stat-number" style="font-size: 2.5rem; font-weight: 700; color: #10b981; margin-bottom: 0.5rem;">24/7</div>
                    <div class="stat-label" style="font-size: 0.875rem; color: #cbd5e1; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Support</div>
                </div>
            </div>

            <div class="hero-actions" style="display: flex; justify-content: center; gap: 1.5rem; margin-bottom: 4rem; flex-wrap: wrap;">
                <a href="auth/register.php" class="btn-premium-primary" style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1.125rem; text-decoration: none; transition: all 0.3s ease; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: white; box-shadow: 0 20px 60px rgba(16, 185, 129, 0.4);">
                    <span>Get Started Free</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="auth/login.php" class="btn-premium-secondary" style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1.125rem; text-decoration: none; transition: all 0.3s ease; background: rgba(30, 41, 59, 0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(16, 185, 129, 0.2); color: white;">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Sign In</span>
                </a>
                <a href="#features" class="btn-premium-ghost" style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1.125rem; text-decoration: none; transition: all 0.3s ease; background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(16, 185, 129, 0.3); color: white;">
                    <i class="fas fa-play-circle"></i>
                    <span>Watch Demo</span>
                </a>
            </div>

            <div class="hero-testimonial" style="max-width: 600px; margin: 3rem auto 2rem;">
                <div class="testimonial-content" style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(16, 185, 129, 0.1); border-radius: 16px; padding: 2rem; text-align: left;">
                    <div class="testimonial-stars" style="display: flex; gap: 0.25rem; margin-bottom: 1rem;">
                        <i class="fas fa-star" style="color: #fbbf24; font-size: 1rem;"></i>
                        <i class="fas fa-star" style="color: #fbbf24; font-size: 1rem;"></i>
                        <i class="fas fa-star" style="color: #fbbf24; font-size: 1rem;"></i>
                        <i class="fas fa-star" style="color: #fbbf24; font-size: 1rem;"></i>
                        <i class="fas fa-star" style="color: #fbbf24; font-size: 1rem;"></i>
                    </div>
                    <p class="testimonial-text" style="color: #cbd5e1; line-height: 1.6; font-size: 0.95rem; font-style: italic; margin-bottom: 1.5rem;">"DFCMS revolutionized how we handle student feedback. The platform is intuitive, powerful, and has significantly improved our response times."</p>
                    <div class="testimonial-author" style="display: flex; align-items: center; gap: 1rem;">
                        <div class="author-avatar" style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: 50%; flex-shrink: 0;"></div>
                        <div class="author-info" style="flex: 1;">
                            <div class="author-name" style="color: white; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.25rem;">Dr. Sarah Johnson</div>
                            <div class="author-title" style="color: #cbd5e1; font-size: 0.875rem;">Dean of Student Affairs</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="dashboard-preview">
                    <div class="preview-glow"></div>
                    <div class="preview-content">
                        <div class="preview-header"></div>
                        <div class="preview-cards">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Premium Features Section -->
    <section id="features" class="features-premium">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="title-gradient">Revolutionary</span> Features
                </h2>
                <p class="section-subtitle">
                    Experience the future of institutional feedback management with our cutting-edge platform.
                </p>
            </div>

            <div class="features-grid-premium">
                <div class="feature-card-premium feature-large">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Enterprise Security</h3>
                    <p>Bank-level encryption and compliance standards protect your most sensitive data.</p>
                    <div class="feature-accent"></div>
                </div>

                <div class="feature-card-premium">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>AI-Powered Insights</h3>
                    <p>Advanced analytics and machine learning provide actionable intelligence.</p>
                </div>

                <div class="feature-card-premium">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Lightning Fast</h3>
                    <p>Optimized performance with sub-second response times globally.</p>
                </div>

                <div class="feature-card-premium">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Team Collaboration</h3>
                    <p>Seamless workflows and real-time collaboration across departments.</p>
                </div>

                <div class="feature-card-premium">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Advanced Analytics</h3>
                    <p>Comprehensive reporting and dashboards for data-driven decisions.</p>
                </div>

                <div class="feature-card-premium">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile First</h3>
                    <p>Fully responsive design works perfectly on all devices.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Premium Footer -->
    <footer class="footer-premium">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="footer-logo">
                    <i class="fas fa-university"></i>
                </div>
                <h3>DFCMS</h3>
                <p>Transforming institutions through innovative technology.</p>
            </div>

            <div class="footer-links">
                <div class="link-group">
                    <h4>Product</h4>
                    <a href="#features">Features</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#security">Security</a>
                </div>
                <div class="link-group">
                    <h4>Company</h4>
                    <a href="#about">About</a>
                    <a href="#careers">Careers</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="link-group">
                    <h4>Support</h4>
                    <a href="#help">Help Center</a>
                    <a href="#docs">Documentation</a>
                    <a href="#status">Status</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2024 DFCMS. All rights reserved.</p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
                <a href="#"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </footer>

    <script src="assets/js/dfcms-ui.js"></script>
    <script>
        // Enhanced smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll-triggered animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card-premium, .stat-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>

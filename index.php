<?php
require_once 'config/config.php';

$page_title = "The Core Platform Engine";
$base_path = '';
$extra_css = '<link href="assets/css/landing.css" rel="stylesheet">';
$nav_transparent = true;
include 'components/head.php';
?>
<body class="landing-page">
    <?php include 'components/navbar.php'; ?>

    <!-- Redesigned Hero Section -->
    <section class="hero-section">
        <div class="hero-left">
            <div class="hero-glow"></div>
            <div class="nav-brand-icon" style="margin-bottom: 2rem; width: 64px; height: 64px; font-size: 2rem;">
                <i class="fas fa-university"></i>
            </div>
            
            <h1 class="hero-title">
                Shaping<br>
                <span class="text-accent">Better Together.</span>
            </h1>

            <p class="hero-description">
                Welcome to the Digital Feedback & Complaint Management System. A space where integrity meets technology, and every voice contributes to institutional excellence.
            </p>

            <div class="hero-cards-row">
                <div class="hero-mini-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Secure End-to-End</h4>
                    <p>Advanced encryption ensures your feedback remains confidential.</p>
                </div>
                <div class="hero-mini-card">
                    <i class="fas fa-chart-line"></i>
                    <h4>Impact Driven</h4>
                    <p>Real-time insights transform complaints into actionable improvements.</p>
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="portal-header">
                <h2>Access Portal</h2>
                <p>Select an option to enter the University Information Science Department Hub</p>
            </div>

            <div class="portal-actions">
                <a href="auth/login.php" class="btn-portal btn-portal-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login to System
                </a>
                <a href="auth/register.php" class="btn-portal btn-portal-secondary">
                    <i class="fas fa-user-plus"></i>
                    Register Account
                </a>
            </div>

            <div class="system-guidelines">
                <div class="guideline-header">
                    <i class="fas fa-info-circle"></i>
                    System Guidelines
                </div>
                <p>
                    Members of the Information Science department (Students, CRs, Teachers, and HODs) can utilize this platform for efficient grievance redressal. Automated workflow ensures your concerns reach the right desk instantly.
                </p>
            </div>
        </div>
    </section>

    <!-- Platform Features -->
    <section id="features" class="section-platform-features">
        <h2>Platform Features</h2>
        <p class="subtitle">Discover the powerful capabilities that make DFCMS the ultimate solution for institutional feedback management</p>
        
        <div class="features-grid-3">
            <div class="feature-large-card">
                <i class="fas fa-shield-halved"></i>
                <h3>Secure End-to-End</h3>
                <p>Advanced encryption and role-based access ensure your feedback remains confidential while reaching the right desk for resolution.</p>
            </div>
            <div class="feature-large-card">
                <i class="fas fa-chart-line-up"></i>
                <h3>Impact Analytics</h3>
                <p>Real-time dashboards and comprehensive reporting transform complaints into actionable insights for institutional improvement.</p>
            </div>
            <div class="feature-large-card">
                <i class="fas fa-route"></i>
                <h3>Smart Routing</h3>
                <p>Intelligent escalation algorithms automatically direct issues to the appropriate department based on category and priority.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-about">
        <div class="about-header">
            <h2>About DFCMS</h2>
            <p class="subtitle">Transforming institutional communication through innovative technology and community-driven solutions</p>
        </div>

        <div class="about-grid-2">
            <div class="feature-large-card">
                <i class="fas fa-users"></i>
                <h3>Community First</h3>
                <p>Built on the principle that every voice matters. DFCMS creates an inclusive environment where students, faculty, and staff collaborate to build a better educational experience.</p>
            </div>
            <div class="feature-large-card">
                <i class="fas fa-lightbulb"></i>
                <h3>Innovation Driven</h3>
                <p>Leveraging cutting-edge technology to streamline feedback processes, reduce resolution times, and provide actionable insights for continuous institutional improvement.</p>
            </div>
        </div>
    </section>

    <!-- Trust & Excellence Section -->
    <section class="section-trust-excellence">
        <div class="wide-cards-grid">
            <div class="feature-large-card" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                <i class="fas fa-handshake"></i>
                <h3>Trust & Transparency</h3>
                <p>Committed to upholding the highest standards of integrity with complete audit trails, transparent processes, and fair resolution mechanisms.</p>
            </div>
            <div class="feature-large-card" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                <i class="fas fa-trophy"></i>
                <h3>Excellence Standard</h3>
                <p>Setting the benchmark for institutional feedback management with proven methodologies, continuous improvement, and exceptional user experiences.</p>
            </div>
        </div>
    </section>

    <!-- Get In Touch Section -->
    <section id="contact" class="main-footer">
        <div class="footer-top">
            <div class="footer-brand-info">
                <h3>
                    <div class="nav-brand-icon" style="width: 40px; height: 40px; font-size: 1.2rem;">
                        <i class="fas fa-university"></i>
                    </div>
                    DFCMS<span class="text-accent">.</span>
                </h3>
                <p>Digital Feedback & Complaint Management System - Transforming institutional communication through innovative technology and community-driven solutions.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-nav">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#features">Platform Features</a></li>
                    <li><a href="#about">About DFCMS</a></li>
                    <li><a href="#">Contact Support</a></li>
                    <li><a href="auth/login.php">Login</a></li>
                    <li><a href="auth/register.php">Register</a></li>
                </ul>
            </div>

            <div class="footer-nav">
                <h4>Resources</h4>
                <ul>
                    <li><a href="#">User Guide</a></li>
                    <li><a href="#">System Documentation</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
        </div>

        <div class="contact-info-section">
            <h4>Contact Info</h4>
            <div class="contact-grid">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>support@dfcms.university.edu</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+1 (555) 123-4567</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Admin Building, Room 201</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Mon-Fri: 9:00 AM - 5:00 PM</span>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/dfcms-ui.js"></script>
    <script>
        // Smooth scroll for redesign links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>

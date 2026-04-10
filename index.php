<?php
// index.php
require_once 'config/config.php';

$page_title = "Digital Feedback System";
$extra_css = '<link href="assets/css/landing.css" rel="stylesheet">';
include 'components/head.php';
// #region agent log
if (function_exists('dfcms_debug_log')) {
    dfcms_debug_log('pre-fix', 'H5', 'index.php', 'landing_render_start', array('hasExtraCss' => isset($extra_css) ? 1 : 0));
}
// #endregion
?>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="master-layout">
        <!-- Visual Section -->
        <div class="section-visual">
            <div class="mb-5"><i class="fas fa-university fa-3x text-accent"></i></div>
            <h1 class="hero-title">Shaping <br><span class="text-accent">Better Together.</span></h1>
            <p class="hero-sub">Welcome to the Digital Feedback & Complaint Management System. A space where integrity meets technology, and every voice contributes to institutional excellence.</p>
            
            <div class="row w-100">
                <div class="col-md-6">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt"></i>
                        <h6>Secure End-to-End</h6>
                        <p>Advanced routing ensures your feedback reaches the right person instantly.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-card">
                        <i class="fas fa-chart-line"></i>
                        <h6>Impact Driven</h6>
                        <p>We don't just track complaints; we measure campus improvement.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portal Section -->
        <div class="section-portal">
            <div class="portal-header">
                <h2>Access Portal</h2>
                <p>Select an option to enter the University Information Science Department Hub</p>
            </div>

            <div class="actions">
                <a href="auth/login.php" class="btn-portal btn-login"><i class="fas fa-sign-in-alt me-2"></i> Login to System</a>
                <a href="auth/register.php" class="btn-portal btn-reg"><i class="fas fa-user-plus me-2"></i> Register Account</a>
            </div>

            <div id="about" class="guidelines-section">
                <div class="guidelines-header">
                    <i class="fas fa-info-circle"></i>
                    <span class="guidelines-title">SYSTEM GUIDELINES</span>
                </div>
                <p class="guidelines-text">
                    Members of the Information Science department (Students, CRs, Teachers, and HODs) can utilize this platform for efficient grievance redressal. Automated workflow tracking is active for all accounts.
                </p>
            </div>
            
            <div class="copyright">
                <p class="copyright-text">© 2026 University Intelligence Division. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Platform Section -->
    <section id="platform" class="section-padding bg-gradient-dark">
        <div class="container">
            <h2 class="section-title text-center">About The Platform</h2>
            <p class="section-subtitle text-center">
                DFCMS is a structured digital complaint and feedback platform designed for academic institutions.
                It connects Students, CRs, Teachers, Lab Assistants, and HOD through transparent workflow routing.
            </p>

            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-sitemap"></i></div>
                    <h3 class="feature-title">Role-Based Workflow</h3>
                    <p class="feature-description">Complaints move through a controlled routing chain so the right person handles the issue at the right time.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="feature-title">Integrated Communication</h3>
                    <p class="feature-description">Built-in messaging and notifications keep all stakeholders aligned during complaint resolution.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                    <h3 class="feature-title">Traceable Resolution</h3>
                    <p class="feature-description">Each action is tracked in history logs to improve accountability and service quality.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section-padding bg-gradient-dark">
        <div class="container">
            <h2 class="section-title text-center">Platform Features</h2>
            <p class="section-subtitle text-center">Discover the powerful capabilities that make DFCMS the ultimate solution for institutional feedback management</p>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="feature-title">Secure End-to-End</h3>
                    <p class="feature-description">Advanced encryption and role-based access ensure your feedback remains confidential.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="feature-title">Impact Analytics</h3>
                    <p class="feature-description">Real-time dashboards transform complaints into actionable insights.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-route"></i></div>
                    <h3 class="feature-title">Smart Routing</h3>
                    <p class="feature-description">Intelligent escalation algorithms automatically direct issues to the appropriate handlers.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h3 class="feature-title">Real-Time Tracking</h3>
                    <p class="feature-description">Complete audit trails with timestamps ensure transparency and accountability.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="feature-title">Mobile Optimized</h3>
                    <p class="feature-description">Fully responsive design enables seamless access from any device.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <h3 class="feature-title">Instant Notifications</h3>
                    <p class="feature-description">Automated alerts keep all stakeholders informed about resolution progress.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer" class="main-footer">
        <div class="container text-center">
            <p>© 2026 University Digital Intelligence. Powered by DFCMS Engine.</p>
        </div>
    </footer>
</body>
</html>

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
            <div class="mb-4"><i class="fas fa-university fa-3x text-accent"></i></div>
            <h1 class="hero-title">Shaping <br><span class="text-accent">Better Together.</span></h1>
            <p class="hero-sub opacity-75">DFCMS provides the official centralized grievance, communication, and performance-tracking infrastructure for the University department.</p>
            
            <div class="row w-100 mt-4">
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

            <!-- Platform Quick Links -->
            <div class="quick-nav mb-5 pt-3 border-top border-secondary border-opacity-10">
                <p class="text-dim extra-small text-uppercase fw-bold mb-3 opacity-50" style="letter-spacing: 2px;">Nav Protocols</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#platform" class="btn btn-sm btn-outline-secondary border-opacity-25 rounded-pill px-3 py-1 text-white hover-accent" style="font-size: 0.75rem;"><i class="fas fa-network-wired me-2 text-accent"></i>Engine</a>
                    <a href="#features" class="btn btn-sm btn-outline-secondary border-opacity-25 rounded-pill px-3 py-1 text-white hover-accent" style="font-size: 0.75rem;"><i class="fas fa-star me-2 text-accent"></i>Capability</a>
                    <a href="#about" class="btn btn-sm btn-outline-secondary border-opacity-25 rounded-pill px-3 py-1 text-white hover-accent" style="font-size: 0.75rem;"><i class="fas fa-shield-halved me-2 text-accent"></i>Protocols</a>
                </div>
            </div>

            <div id="about" class="protocol-card mt-auto p-4 rounded-4" style="background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); border: 1px solid rgba(16, 185, 129, 0.15);">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-accent bg-opacity-10 p-2 rounded-3 me-3">
                        <i class="fas fa-terminal text-accent"></i>
                    </div>
                    <span class="fw-bold text-white small" style="letter-spacing: 1px;">ENTITY ACTION MATRIX</span>
                </div>
                
                <div class="matrix-list small text-dim">
                    <div class="mb-2 d-flex align-items-center"><i class="fas fa-circle-check text-accent me-2" style="font-size: 0.6rem;"></i> <span class="text-white me-2">Students:</span> Priority secure filing</div>
                    <div class="mb-2 d-flex align-items-center"><i class="fas fa-circle-check text-accent me-2" style="font-size: 0.6rem;"></i> <span class="text-white me-2">CRs:</span> Tier-1 validation & routing</div>
                    <div class="mb-2 d-flex align-items-center"><i class="fas fa-circle-check text-accent me-2" style="font-size: 0.6rem;"></i> <span class="text-white me-2">Staff:</span> Technical resolution</div>
                    <div class="mb-0 d-flex align-items-center"><i class="fas fa-circle-check text-accent me-2" style="font-size: 0.6rem;"></i> <span class="text-white me-2">HOD:</span> Oversight & audit Control</div>
                </div>
            </div>
            
            <div class="copyright text-center mt-5">
                <p class="copyright-text text-dim small">© 2026 University Intelligence Division. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Platform Section -->
    <section id="platform" class="section-padding bg-gradient-dark py-5">
        <div class="container py-5">
            <h2 class="section-title text-center text-white fw-bold mb-3"><i class="fas fa-network-wired text-accent me-2"></i>The Core Platform Engine</h2>
            <p class="section-subtitle text-center text-dim mx-auto mb-5" style="max-width: 800px; font-size: 1.1rem;">
                DFCMS leverages a sophisticated multi-tier routing architecture that completely eliminates the historical friction of traditional university bureaucracy. By dynamically assigning issues based on priority and category, resolutions happen in days rather than weeks.
            </p>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="p-4 rounded-4 bg-glass border border-secondary border-opacity-10 h-100 shadow-sm" style="transition: transform 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-accent bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-sitemap text-accent fa-lg"></i>
                            </div>
                            <h4 class="text-white m-0" style="font-size: 1.25rem;">Role-Based Routing</h4>
                        </div>
                        <p class="text-dim small lh-lg mb-0">Complaints are intelligently routed through a hierarchical chain—from CR to Teacher to HOD. This guarantees that issues are filtered properly and handled contextually by the right authoritative body.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-4 rounded-4 bg-glass border border-secondary border-opacity-10 h-100 shadow-sm" style="transition: transform 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-comments text-info fa-lg"></i>
                            </div>
                            <h4 class="text-white m-0" style="font-size: 1.25rem;">Live Unified Comm.</h4>
                        </div>
                        <p class="text-dim small lh-lg mb-0">Built-in secure messaging and real-time Toast notifications bridge the gap between students and higher-ups. No external emails required; everything happens seamlessly within the platform framework.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-4 rounded-4 bg-glass border border-secondary border-opacity-10 h-100 shadow-sm" style="transition: transform 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-clipboard-check text-warning fa-lg"></i>
                            </div>
                            <h4 class="text-white m-0" style="font-size: 1.25rem;">Immutable Audit Trail</h4>
                        </div>
                        <p class="text-dim small lh-lg mb-0">Every forward, resolution, and communication is permanently logged in the system's history ledger. The HOD retains full oversight capabilities, turning anecdotal complaints into measurable analytics.</p>
                    </div>
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

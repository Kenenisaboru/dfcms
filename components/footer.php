<?php
// components/footer.php
?>
<footer class="dfcms-footer-ultra py-8 overflow-hidden">
    <div class="container position-relative">
        <!-- Floating Ambient Orbs -->
        <div class="footer-orb orb-1"></div>
        <div class="footer-orb orb-2"></div>
        
        <div class="ultra-card-wrapper animate-on-scroll">
            <div class="ultra-card">
                <div class="card-inner p-4 p-md-5">
                    <div class="row gy-5 gx-4">
                        <!-- Brand DNA -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="brand-section mb-4">
                                <div class="brand-logo mb-3">
                                    <div class="logo-orb">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <span class="logo-text">DF<span>CMS</span></span>
                                </div>
                                <p class="brand-description">
                                    Engineering the future of departmental communication. A premium management ecosystem 
                                    crafted for <strong>Information Science</strong> with precision, transparency, and speed.
                                </p>
                            </div>
                            <div class="social-stack d-flex gap-3">
                                <a href="https://github.com/Kenenisaboru" target="_blank" class="glass-icon-btn" data-tooltip="Github">
                                    <i class="fab fa-github"></i>
                                </a>
                                <a href="#" class="glass-icon-btn" data-tooltip="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="#" class="glass-icon-btn" data-tooltip="Portfolio">
                                    <i class="fas fa-globe"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Main Grid -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="row gy-5">
                                <!-- Architect Section -->
                                <div class="col-md-6">
                                    <div class="section-label mb-4">
                                        <div class="label-line"></div>
                                        <span>System Architect</span>
                                    </div>
                                    <a href="https://github.com/Kenenisaboru" target="_blank" class="architect-card">
                                        <div class="architect-content">
                                            <div class="architect-avatar">
                                                <i class="fas fa-user-ninja"></i>
                                            </div>
                                            <div class="architect-info">
                                                <h5 class="m-0 text-white">Kenenisa Boru</h5>
                                                <span class="text-accent gradient-text fw-bold small uppercase tracking-1">Fullstack Engineer</span>
                                            </div>
                                            <div class="architect-arrow">
                                                <i class="fas fa-arrow-right"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <!-- Engineering Team -->
                                <div class="col-md-6">
                                    <div class="section-label mb-4">
                                        <div class="label-line"></div>
                                        <span>Engineering Team</span>
                                    </div>
                                    <div class="team-mosaic">
                                        <?php 
                                        $team = ['Hailu Hade', 'Lidia Guluma', 'Nuhami Firehewot', 'Firaol Aduna', 'Hamzia Abdulexif', 'Alia Awel'];
                                        foreach($team as $member): ?>
                                            <div class="mosaic-item">
                                                <span class="dot"></span>
                                                <span class="name"><?php echo $member; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class="footer-meta mt-5 pt-4 border-top border-white-10">
                        <div class="row align-items-center gy-3">
                            <div class="col-md-6 order-2 order-md-1">
                                <div class="copyright-info d-flex align-items-center gap-2">
                                    <i class="fas fa-copyright small opacity-50"></i>
                                    <span class="small text-muted">2026 Information Science Department. All rights reserved.</span>
                                </div>
                            </div>
                            <div class="col-md-6 order-1 order-md-2 text-md-end">
                                <div class="footer-nav-sm">
                                    <a href="#">Privacy</a>
                                    <a href="#">Security</a>
                                    <a href="#">Infrastructure</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.dfcms-footer-ultra {
    --primary: #10b981;
    --primary-glow: rgba(16, 185, 129, 0.4);
    --bg-dark: #08090a;
    --white-10: rgba(255, 255, 255, 0.08);
    --white-5: rgba(255, 255, 255, 0.04);
    font-family: 'Plus Jakarta Sans', sans-serif;
    position: relative;
    background: var(--bg-dark);
}

/* Ambient Visuals */
.footer-orb {
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;
    z-index: 0;
    pointer-events: none;
}
.orb-1 { background: var(--primary); top: -100px; right: -50px; }
.orb-2 { background: #3b82f6; bottom: -100px; left: -50px; }

.ultra-card-wrapper {
    position: relative;
    z-index: 1;
}

.ultra-card {
    background: rgba(255, 255, 255, 0.015);
    border: 1px solid var(--white-10);
    border-radius: 2.5rem;
    backdrop-filter: blur(40px);
    -webkit-backdrop-filter: blur(40px);
    position: relative;
    overflow: hidden;
    box-shadow: 0 40px 100px -20px rgba(0,0,0,0.8);
}

.ultra-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top left, var(--white-10), transparent 40%);
    pointer-events: none;
}

/* Brand DNA Styling */
.logo-orb {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.25rem;
    box-shadow: 0 0 20px var(--primary-glow);
}

.brand-logo {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo-text {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
}

.logo-text span { color: var(--primary); }

.brand-description {
    color: #94a3b8;
    line-height: 1.8;
    font-size: 0.9375rem;
    max-width: 340px;
}

/* icon Buttons */
.glass-icon-btn {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--white-5);
    border: 1px solid var(--white-10);
    border-radius: 15px;
    color: #cbd5e1;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    text-decoration: none;
}

.glass-icon-btn:hover {
    background: var(--primary);
    color: #000;
    transform: translateY(-8px) rotate(-10deg);
    box-shadow: 0 15px 30px var(--primary-glow);
    border-color: transparent;
}

/* Section Labels */
.section-label {
    display: flex;
    align-items: center;
    gap: 12px;
}

.label-line {
    width: 24px;
    height: 2px;
    background: var(--primary);
    border-radius: 2px;
}

.section-label span {
    text-transform: uppercase;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 2px;
    color: #475569;
}

/* Architect Card */
.architect-card {
    text-decoration: none;
    display: block;
}

.architect-content {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--white-5);
    padding: 24px;
    border-radius: 2rem;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.5s ease;
}

.architect-card:hover .architect-content {
    background: var(--white-10);
    border-color: var(--primary);
    transform: translateX(10px);
}

.architect-avatar {
    width: 60px;
    height: 60px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 1.5rem;
    transition: all 0.5s ease;
}

.architect-card:hover .architect-avatar {
    background: var(--primary);
    color: #fff;
    transform: scale(1.1) rotate(10deg);
}

.architect-arrow {
    margin-left: auto;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--white-10);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    transition: all 0.3s ease;
}

.architect-card:hover .architect-arrow {
    background: #fff;
    color: #000;
}

/* Team Mosaic */
.team-mosaic {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.mosaic-item {
    background: rgba(255,255,255,0.01);
    border: 1px solid var(--white-5);
    padding: 12px 18px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.mosaic-item:hover {
    background: var(--white-5);
    border-color: var(--white-10);
    transform: translateY(-3px);
}

.mosaic-item .dot {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    opacity: 0.5;
}

.mosaic-item .name {
    color: #94a3b8;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Bottom Nav */
.footer-nav-sm {
    display: flex;
    gap: 2rem;
    justify-content: flex-end;
}

.footer-nav-sm a {
    color: #475569;
    text-decoration: none;
    font-size: 0.8125rem;
    font-weight: 600;
    transition: color 0.3s ease;
}

.footer-nav-sm a:hover {
    color: #fff;
}

@media (max-width: 768px) {
    .team-mosaic { grid-template-columns: 1fr; }
    .footer-nav-sm { justify-content: center; }
    .copyright-info { justify-content: center; }
}

/* Gradient Text Support */
.gradient-text {
    background: linear-gradient(90deg, #10b981, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>

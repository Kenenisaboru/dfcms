<?php
// components/footer.php
?>
<footer class="dfcms-footer mt-auto py-5 bg-glass border-top border-secondary border-opacity-10">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5 text-center text-lg-start">
                <div class="footer-brand mb-3">
                    <span class="fw-bold fs-4 text-white">DF<span class="text-accent">CMS</span></span>
                    <p class="text-muted small mt-2">Digital Feedback & Complaint Management System specifically designed for the <br><strong class="text-light">Information Science Department</strong>.</p>
                </div>
                <div class="footer-socials d-flex justify-content-center justify-content-lg-start gap-3">
                    <a href="https://github.com/Kenenisaboru" target="_blank" class="social-link"><i class="fab fa-github"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="credits-grid">
                    <div class="credit-category">
                        <h6 class="text-accent small fw-bold text-uppercase mb-3">Lead Developer</h6>
                        <div class="developer-card">
                            <i class="fas fa-code me-2"></i>
                            <a href="https://github.com/Kenenisaboru" target="_blank" class="text-decoration-none text-white fw-bold">Kenenisa Boru</a>
                            <span class="d-block text-muted small">Fullstack Developer</span>
                        </div>
                    </div>
                    
                    <div class="credit-category">
                        <h6 class="text-muted small fw-bold text-uppercase mb-3">Core Contributors</h6>
                        <div class="row row-cols-2 g-2">
                            <div class="col small text-white"><i class="fas fa-user-check me-2 text-primary-400"></i>Hailu Hade</div>
                            <div class="col small text-white"><i class="fas fa-user-check me-2 text-primary-400"></i>Lidia Guluma</div>
                            <div class="col small text-white"><i class="fas fa-user-check me-2 text-primary-400"></i>Nuhami Firehewot</div>
                            <div class="col small text-white"><i class="fas fa-user-check me-2 text-primary-400"></i>Firaol Aduna</div>
                            <div class="col small text-white"><i class="fas fa-user-check me-2 text-primary-400"></i>Hamzia Abdulexif</div>
                            <div class="col small text-white"><i class="fas fa-user-check me-2 text-primary-400"></i>Alia Awel</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="my-4 border-secondary border-opacity-10">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <span class="text-muted small">&copy; <?php echo date('Y'); ?> Information Science Department. All Rights Reserved.</span>
            <div class="footer-links small">
                <a href="#" class="text-muted text-decoration-none mx-2">Privacy Policy</a>
                <a href="#" class="text-muted text-decoration-none mx-2">Terms of Service</a>
                <a href="#" class="text-muted text-decoration-none mx-2">Support</a>
            </div>
        </div>
    </div>
</footer>

<style>
.dfcms-footer {
    position: relative;
    overflow: hidden;
}
.social-link {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    color: #94a3b8;
    transition: all 0.3s ease;
    text-decoration: none;
}
.social-link:hover {
    background: var(--primary-500);
    color: #000;
    transform: translateY(-3px);
}
.credits-grid {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}
.credit-category {
    flex: 1;
    min-width: 250px;
}
.developer-card {
    background: rgba(16, 185, 129, 0.05);
    border-left: 3px solid var(--primary-500);
    padding: 12px 16px;
    border-radius: 0 8px 8px 0;
}
@media (max-width: 991px) {
    .credits-grid { gap: 1.5rem; justify-content: center; }
    .credits-grid > div { text-align: center; }
    .row-cols-2 { justify-content: center; }
    .developer-card { display: inline-block; text-align: left; }
}
</style>

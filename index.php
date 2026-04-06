<?php
// index.php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University DFCMS - Digital Feedback & Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0c0d0e; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        
        html {
            scroll-behavior: smooth;
        }
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(12, 13, 14, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-header .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .main-header .logo i {
            color: #10b981;
            margin-right: 12px;
            font-size: 1.5rem;
        }

        .main-header .logo span {
            color: #10b981;
        }

        .main-header .nav-links {
            display: flex;
            align-items: center;
        }

        .main-header .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            margin-left: 30px;
            transition: all 0.3s ease;
            position: relative;
        }

        .main-header .nav-links a:hover {
            color: #10b981;
        }

        .main-header .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #10b981;
            transition: width 0.3s ease;
        }

        .main-header .nav-links a:hover::after {
            width: 100%;
        }

        .header-btn-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #000000;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .header-btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: #000000;
        }

        .master-layout { 
            display: flex; 
            height: 100vh; 
            width: 100%; 
            transition: 0.5s;
            min-height: 100vh;
            margin-top: 80px;
        }
        
        /* Left Section: Visual Impact & Motivation */
        .section-visual {
            flex: 1.3;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(16, 185, 129, 0.3) 100%), 
                        url('https://images.unsplash.com/photo-1523050853064-96ef21182470?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            position: relative;
        }

        /* Right Section: Core Interaction Portal */
        .section-portal {
            flex: 0.7;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            border-left: 1px solid rgba(16, 185, 129, 0.2);
            position: relative;
        }
        .section-portal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .text-accent { color: #10b981; }
        .hero-title { font-size: 4.5rem; font-weight: 800; line-height: 1; margin-bottom: 30px; letter-spacing: -2px; }
        .hero-sub { font-size: 1.4rem; color: #bbb; max-width: 600px; margin-bottom: 40px; line-height: 1.6; }

        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: 0.3s;
        }
        .feature-card:hover { background: rgba(16, 185, 129, 0.05); transform: translateX(10px); }
        .feature-card i { font-size: 1.5rem; color: #10b981; margin-bottom: 10px; display: block; }
        .feature-card h6 { font-weight: bold; margin-bottom: 5px; }
        .feature-card p { font-size: 0.85rem; color: #888; margin: 0; }

        .btn-portal {
            padding: 18px 40px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 10px;
            transition: 0.4s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        .btn-login { background-color: #10b981; color: #000; }
        .btn-login:hover { background-color: #059669; color: #000; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }
        .btn-reg { background-color: transparent; border: 1px solid #444; color: #fff; }
        .btn-reg:hover { background-color: #333; color: #fff; transform: translateY(-3px); }

        .portal-header { margin-bottom: 50px; position: relative; z-index: 1; }
        .portal-header h2 { font-weight: 800; font-size: 2.2rem; color: #ffffff; margin-bottom: 15px; }
        .portal-header p { color: #a0a0a0; font-size: 1rem; line-height: 1.5; }

        .actions { position: relative; z-index: 1; margin-bottom: 40px; }
        
        .guidelines-section {
            position: relative; 
            z-index: 1; 
            padding: 25px; 
            background: rgba(255, 255, 255, 0.02); 
            border-radius: 12px; 
            border: 1px solid rgba(255, 255, 255, 0.05); 
            margin-bottom: 30px;
        }
        
        .guidelines-header {
            display: flex; 
            align-items: center; 
            margin-bottom: 15px;
        }
        
        .guidelines-header i { 
            color: #10b981; 
            margin-right: 12px; 
            font-size: 1.1rem;
        }
        
        .guidelines-title { 
            color: #10b981; 
            font-weight: 600; 
            font-size: 0.85rem; 
            letter-spacing: 1px;
        }
        
        .guidelines-text { 
            color: #c0c0c0; 
            font-size: 0.9rem; 
            line-height: 1.6; 
            margin: 0;
        }
        
        .copyright {
            position: relative; 
            z-index: 1; 
            text-align: center;
        }
        
        .copyright-text {
            color: #808080; 
            font-size: 0.8rem; 
            margin: 0;
        }

        /* Additional Sections Styling */
        .section-padding { padding: 80px 0; }
        .bg-gradient-dark { 
            background: linear-gradient(135deg, #0c0d0e 0%, #1a1a1a 100%);
            position: relative;
        }
        .bg-gradient-dark::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
        }

        .section-title {
            font-size: 3rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #a0a0a0;
            max-width: 600px;
            margin: 0 auto 60px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #059669);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-item:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .feature-item:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #10b981;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .feature-description {
            color: #a0a0a0;
            line-height: 1.6;
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin: 60px 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #a0a0a0;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contact-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 50px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            color: #ffffff;
            font-weight: 500;
            margin-bottom: 10px;
            display: block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #ffffff;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: #666;
        }

        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #000000;
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        /* Footer Styling */
        .main-footer {
            background: linear-gradient(135deg, #0a0a0a 0%, #121212 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 60px 0 30px;
            position: relative;
        }

        .main-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h4 {
            color: #10b981;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-section p, .footer-section li {
            color: #a0a0a0;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li a {
            color: #a0a0a0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #10b981;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .footer-logo i {
            color: #10b981;
            font-size: 2rem;
            margin-right: 15px;
        }

        .footer-logo span {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .footer-logo span strong {
            color: #10b981;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a0a0a0;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #10b981;
            color: #000000;
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 30px;
            text-align: center;
        }

        .footer-bottom p {
            color: #666;
            margin: 0;
            font-size: 0.9rem;
        }

        @media (max-width: 1024px) {
            .section-visual { display: none; }
            .section-portal { flex: 1; padding: 40px; }
            .main-header { padding: 15px 30px; }
            .main-header .logo { font-size: 1.5rem; }
            .main-header .nav-links a { margin-left: 20px; }
            .section-title { font-size: 2.5rem; }
            .feature-grid { grid-template-columns: 1fr; gap: 20px; }
        }

        @media (max-width: 768px) {
            .main-header { padding: 15px 20px; }
            .main-header .logo { font-size: 1.3rem; }
            .main-header .nav-links { display: none; }
            .section-title { font-size: 2rem; }
            .contact-form { padding: 30px 20px; }
            .footer-content { grid-template-columns: 1fr; gap: 30px; }
            .main-footer { padding: 40px 0 20px; }
        }
    </style>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <a href="index.php" class="logo">
            <i class="fas fa-university"></i>
            DFCMS<span>.</span>
        </a>
        <nav class="nav-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <a href="auth/login.php" class="header-btn-login">Login</a>
            <a href="auth/register.php">Sign Up</a>
        </nav>
    </header>

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

            <div class="guidelines-section">
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

    <!-- Features Section -->
    <section id="features" class="section-padding bg-gradient-dark">
        <div class="container">
            <h2 class="section-title text-center">Platform Features</h2>
            <p class="section-subtitle text-center">Discover the powerful capabilities that make DFCMS the ultimate solution for institutional feedback management</p>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Secure End-to-End</h3>
                    <p class="feature-description">Advanced encryption and role-based access ensure your feedback remains confidential while reaching the right decision-makers instantly.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Impact Analytics</h3>
                    <p class="feature-description">Real-time dashboards and comprehensive reporting transform complaints into actionable insights for institutional improvement.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3 class="feature-title">Smart Routing</h3>
                    <p class="feature-description">Intelligent escalation algorithms automatically direct issues to the appropriate department based on category and priority.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">Real-Time Tracking</h3>
                    <p class="feature-description">Complete audit trails with timestamps ensure transparency and accountability throughout the resolution process.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Mobile Optimized</h3>
                    <p class="feature-description">Fully responsive design enables seamless access from any device, ensuring feedback submission anytime, anywhere.</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="feature-title">Instant Notifications</h3>
                    <p class="feature-description">Automated alerts keep all stakeholders informed about complaint status updates and resolution progress.</p>
                </div>
            </div>
            
            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Resolution Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24h</div>
                    <div class="stat-label">Avg Response Time</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">5000+</div>
                    <div class="stat-label">Issues Resolved</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8★</div>
                    <div class="stat-label">User Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-padding">
        <div class="container">
            <h2 class="section-title text-center">About DFCMS</h2>
            <p class="section-subtitle text-center">Transforming institutional communication through innovative technology and community-driven solutions</p>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="feature-item h-100">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">Community First</h3>
                        <p class="feature-description">Built on the principle that every voice matters. DFCMS creates an inclusive environment where students, faculty, and staff collaborate to build a better educational experience.</p>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="feature-item h-100">
                        <div class="feature-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3 class="feature-title">Innovation Driven</h3>
                        <p class="feature-description">Leveraging cutting-edge technology to streamline feedback processes, reduce resolution times, and provide actionable insights for continuous institutional improvement.</p>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="feature-item h-100">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="feature-title">Trust & Transparency</h3>
                        <p class="feature-description">Committed to upholding the highest standards of integrity with complete audit trails, transparent processes, and fair resolution mechanisms.</p>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="feature-item h-100">
                        <div class="feature-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3 class="feature-title">Excellence Standard</h3>
                        <p class="feature-description">Setting the benchmark for institutional feedback management with proven methodologies, continuous improvement, and exceptional user experiences.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding bg-gradient-dark">
        <div class="container">
            <h2 class="section-title text-center">Get In Touch</h2>
            <p class="section-subtitle text-center">Have questions or need support? Our team is here to help you make the most of DFCMS</p>
            
            <div class="contact-container">
                <form class="contact-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" placeholder="Enter your name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" placeholder="your.email@university.edu" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select class="form-control">
                            <option value="">Select your department</option>
                            <option value="student">Student</option>
                            <option value="cr">Class Representative</option>
                            <option value="teacher">Teacher</option>
                            <option value="lab_assistant">Lab Assistant</option>
                            <option value="hod">Head of Department</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn-submit">Send Message</button>
                    </div>
                </form>
                
                <div class="text-center mt-5">
                    <p class="feature-description">
                        <i class="fas fa-envelope me-2"></i>support@dfcms.university.edu | 
                        <i class="fas fa-phone me-2 ms-3"></i>+1 (555) 123-4567 | 
                        <i class="fas fa-map-marker-alt me-2 ms-3"></i>Admin Building, Room 201
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-university"></i>
                        <span>DFCMS<strong>.</strong></span>
                    </div>
                    <p>Digital Feedback & Complaint Management System - Transforming institutional communication through innovative technology and community-driven solutions.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#features">Platform Features</a></li>
                        <li><a href="#about">About DFCMS</a></li>
                        <li><a href="#contact">Contact Support</a></li>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Register</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">User Guide</a></li>
                        <li><a href="#">System Documentation</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-envelope me-2"></i>support@dfcms.university.edu</p>
                    <p><i class="fas fa-phone me-2"></i>+1 (555) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Admin Building, Room 201</p>
                    <p><i class="fas fa-clock me-2"></i>Mon-Fri: 9:00 AM - 5:00 PM</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© 2026 University Intelligence Division. All rights reserved. | Powered by DFCMS Engineering Core</p>
            </div>
        </div>
    </footer>
</body>
</html>

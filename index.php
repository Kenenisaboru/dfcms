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
        body { background-color: #0c0d0e; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden; height: 100vh; margin: 0; }
        .main-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            background: rgba(12, 13, 14, 0.8);
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

        .master-layout { display: flex; height: 100vh; width: 100%; transition: 0.5s; }
        
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

        @media (max-width: 1024px) {
            .section-visual { display: none; }
            .section-portal { flex: 1; padding: 40px; }
            .main-header { padding: 15px 30px; }
            .main-header .logo { font-size: 1.5rem; }
            .main-header .nav-links a { margin-left: 20px; }
        }

        @media (max-width: 768px) {
            .main-header { padding: 15px 20px; }
            .main-header .logo { font-size: 1.3rem; }
            .main-header .nav-links { display: none; }
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
</body>
</html>

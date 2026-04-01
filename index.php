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
            background: #121212;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            border-left: 1px solid #222;
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

        .portal-header { margin-bottom: 50px; }
        .portal-header h2 { font-weight: 800; font-size: 2.2rem; }
        .portal-header p { color: #666; font-size: 1rem; }

        @media (max-width: 1024px) {
            .section-visual { display: none; }
            .section-portal { flex: 1; padding: 40px; }
        }
    </style>
</head>
<body>
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

            <div class="mt-5 pt-5 border-top border-secondary">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-info-circle text-accent me-2"></i>
                    <span class="small text-muted fw-bold">SYSTEM GUIDELINES</span>
                </div>
                <p class="small text-muted" style="line-height: 1.8;">
                    Members of the Information Science department (Students, CRs, Teachers, and HODs) can utilize this platform for efficient grievance redressal. Automated workflow tracking is active for all accounts.
                </p>
            </div>
            
            <div class="mt-auto">
                <p class="small text-muted text-center mb-0">© 2026 University Intelligence Division. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>

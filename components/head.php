<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - DFCMS" : "University DFCMS"; ?></title>
    
    <!-- Core Assets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Style System -->
    <style>
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0c0d0e;
            --card-bg: rgba(18, 18, 18, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-light: #f8fafc;
            --text-dim: #94a3b8;
            --input-bg: #eef2f7;
        }

        body { 
            background-color: var(--bg-dark); 
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(16, 185, 129, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(16, 185, 129, 0.03) 0%, transparent 40%);
            color: var(--text-light); 
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .text-accent { color: var(--primary) !important; }
        .bg-glass { background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); }
        
        .btn-accent { 
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            border: none; color: #fff; font-weight: 700; border-radius: 10px; transition: 0.3s;
        }
        .btn-accent:hover { transform: translateY(-2px); box-shadow: 0 10px 20px var(--primary-glow); color: #fff; }

        .form-control-custom {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--glass-border) !important;
            color: #1e293b !important;
            border-radius: 10px;
            padding: 12px 16px;
        }
        .form-control-custom:focus {
            box-shadow: 0 0 0 4px var(--primary-glow) !important;
            border-color: var(--primary) !important;
        }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>

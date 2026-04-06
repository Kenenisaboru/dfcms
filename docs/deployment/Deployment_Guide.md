# DFCMS Deployment Guide

## 🚀 Production Deployment Manual

### 📋 Table of Contents
- [Prerequisites](#-prerequisites)
- [Environment Setup](#-environment-setup)
- [Database Configuration](#-database-configuration)
- [Application Deployment](#-application-deployment)
- [Security Configuration](#-security-configuration)
- [Performance Optimization](#-performance-optimization)
- [Monitoring & Logging](#-monitoring--logging)
- [Backup & Recovery](#-backup--recovery)
- [Troubleshooting](#-troubleshooting)

---

## ✅ Prerequisites

### 🖥️ System Requirements

#### **Minimum Requirements**
- **CPU**: 2 cores, 2.4 GHz
- **RAM**: 4 GB
- **Storage**: 50 GB SSD
- **Network**: 100 Mbps
- **OS**: Ubuntu 20.04+ / CentOS 8+ / Windows Server 2019+

#### **Recommended Requirements**
- **CPU**: 4 cores, 3.0 GHz
- **RAM**: 16 GB
- **Storage**: 200 GB SSD
- **Network**: 1 Gbps
- **OS**: Ubuntu 22.04 LTS

#### **Software Requirements**
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Cache**: Redis 6.0+ (recommended)
- **SSL Certificate**: Valid TLS certificate

### 🔧 PHP Extensions Required
```bash
php-cli php-fpm php-mysql php-pdo php-mbstring php-xml php-curl php-zip php-gd php-intl php-bcmath php-json php-opcache php-redis
```

---

## 🌍 Environment Setup

### 1. **Server Preparation**

#### **Update System**
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

#### **Install Required Software**
```bash
# Ubuntu/Debian
sudo apt install -y apache2 mysql-server redis-server php8.2 php8.2-fpm php8.2-mysql php8.2-pdo php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath php8.2-json php8.2-opcache php8.2-redis composer unzip

# CentOS/RHEL
sudo yum install -y httpd mariadb-server redis php82 php82-php-fpm php82-php-mysql php82-php-pdo php82-php-mbstring php82-php-xml php82-php-curl php82-php-zip php82-php-gd php82-php-intl php82-php-bcmath php82-php-json php82-php-opcache composer unzip
```

### 2. **Database Setup**

#### **Install MySQL**
```bash
# Ubuntu/Debian
sudo apt install -y mysql-server
sudo mysql_secure_installation

# CentOS/RHEL
sudo yum install -y mariadb-server
sudo mysql_secure_installation
```

#### **Create Database**
```sql
CREATE DATABASE dfcms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dfcms_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON dfcms.* TO 'dfcms_user'@'localhost';
FLUSH PRIVILEGES;
```

#### **Import Database Schema**
```bash
mysql -u dfcms_user -p dfcms < database.sql
mysql -u dfcms_user -p dfcms < database_updates.sql
```

### 3. **Web Server Configuration**

#### **Apache Configuration**
```apache
# /etc/apache2/sites-available/dfcms.conf
<VirtualHost *:80>
    ServerName dfcms.university.edu
    DocumentRoot /var/www/dfcms/public
    
    # Redirect HTTP to HTTPS
    Redirect permanent / https://dfcms.university.edu/
</VirtualHost>

<VirtualHost *:443>
    ServerName dfcms.university.edu
    DocumentRoot /var/www/dfcms/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/dfcms.crt
    SSLCertificateKeyFile /etc/ssl/private/dfcms.key
    SSLCertificateChainFile /etc/ssl/certs/dfcms-chain.crt
    
    # Security Headers
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.dfcms.university.edu;"
    
    # PHP Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>
    
    # Directory Permissions
    <Directory /var/www/dfcms>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Error and Access Logs
    ErrorLog ${APACHE_LOG_DIR}/dfcms_error.log
    CustomLog ${APACHE_LOG_DIR}/dfcms_access.log combined
</VirtualHost>
```

#### **Nginx Configuration**
```nginx
# /etc/nginx/sites-available/dfcms
server {
    listen 80;
    server_name dfcms.university.edu;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name dfcms.university.edu;
    root /var/www/dfcms;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/ssl/certs/dfcms.crt;
    ssl_certificate_key /etc/ssl/private/dfcms.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    
    # Security Headers
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.dfcms.university.edu;" always;
    
    # PHP Processing
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # PHP Security
        fastcgi_param HTTPS on;
        fastcgi_param SERVER_PORT 443;
    }
    
    # Static Files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
        try_files $uri =404;
    }
    
    # Block Access to Sensitive Files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|lib|docs|examples|integration)/ {
        deny all;
    }
    
    # Error and Access Logs
    access_log /var/log/nginx/dfcms_access.log;
    error_log /var/log/nginx/dfcms_error.log;
}
```

---

## 🗄️ Database Configuration

### **MySQL Optimization**

#### **MySQL Configuration (my.cnf)**
```ini
[mysqld]
# General Settings
user = mysql
pid-file = /var/run/mysqld/mysqld.pid
socket = /var/run/mysqld/mysqld.sock
port = 3306
basedir = /usr
datadir = /var/lib/mysql
tmpdir = /tmp
lc-messages-dir = /usr/share/mysql

# Performance Settings
innodb_buffer_pool_size = 4G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_io_capacity = 2000
innodb_io_capacity_max = 4000

# Connection Settings
max_connections = 500
max_connect_errors = 1000
wait_timeout = 600
max_allowed_packet = 64M

# Query Cache
query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 2M

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Binary Log
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M

# Character Set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

#### **Create Database User with Limited Privileges**
```sql
-- Application User (limited privileges)
CREATE USER 'dfcms_app'@'localhost' IDENTIFIED BY 'strong_app_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.* TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.complaints TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.complaint_history TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.notifications TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.users TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.user_sessions TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.email_queue TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.email_logs TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.security_audit_log TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.rate_limits TO 'dfcms_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON dfcms.user_activity TO 'dfcms_app'@'localhost';

-- Backup User (read-only access)
CREATE USER 'dfcms_backup'@'localhost' IDENTIFIED BY 'strong_backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW ON dfcms.* TO 'dfcms_backup'@'localhost';
GRANT RELOAD ON *.* TO 'dfcms_backup'@'localhost';

FLUSH PRIVILEGES;
```

---

## 🚀 Application Deployment

### 1. **Deploy Application Files**

#### **Clone Repository**
```bash
# Create deployment directory
sudo mkdir -p /var/www/dfcms
sudo chown $USER:$USER /var/www/dfcms

# Clone repository
cd /var/www/dfcms
git clone https://github.com/Kenenisaboru/dfcms.git .
```

#### **Install Dependencies**
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set proper permissions
sudo chown -R www-data:www-data /var/www/dfcms
sudo chmod -R 755 /var/www/dfcms
sudo chmod -R 777 /var/www/dfcms/uploads
sudo chmod -R 777 /var/www/dfcms/logs
```

### 2. **Configure Application**

#### **Environment Configuration**
```bash
# Create environment file
cp .env.example .env
nano .env
```

#### **.env File Content**
```bash
# Application Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dfcms.university.edu

# Database Configuration
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dfcms
DB_USERNAME=dfcms_app
DB_PASSWORD=strong_app_password

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_ENCRYPTION=tls
FROM_EMAIL=noreply@dfcms.university.edu
FROM_NAME=DFCMS System

# Security Configuration
ENCRYPTION_KEY=your-32-character-encryption-key-here
JWT_SECRET=your-jwt-secret-key-here
TWOFA_SECRET=your-2fa-secret-key-here

# Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# File Upload Configuration
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,pdf,doc,docx

# Logging Configuration
LOG_LEVEL=info
LOG_MAX_FILES=30
```

#### **Update Database Configuration**
```php
// config/database.php
<?php
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'dfcms',
    'username' => getenv('DB_USERNAME') ?: 'dfcms_app',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]
];
?>
```

### 3. **PHP Configuration**

#### **PHP.ini Settings**
```ini
; /etc/php/8.2/fpm/php.ini

; General Settings
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; File Uploads
file_uploads = On
upload_max_filesize = 10M
post_max_size = 12M
max_file_uploads = 20

; Session Settings
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
session.gc_maxlifetime = 7200
session.cookie_secure = On
session.cookie_httponly = On
session.cookie_samesite = Strict

; OPcache Settings
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
opcache.enable_cli = 1

; Security Settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
```

---

## 🔒 Security Configuration

### 1. **SSL Certificate Setup**

#### **Let's Encrypt Certificate**
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Obtain Certificate
sudo certbot --apache -d dfcms.university.edu

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### **Self-Signed Certificate (Development)**
```bash
# Generate Private Key
sudo openssl genrsa -out /etc/ssl/private/dfcms.key 2048

# Generate Certificate
sudo openssl req -new -x509 -key /etc/ssl/private/dfcms.key -out /etc/ssl/certs/dfcms.crt -days 365

# Set Permissions
sudo chmod 600 /etc/ssl/private/dfcms.key
sudo chmod 644 /etc/ssl/certs/dfcms.crt
```

### 2. **Firewall Configuration**

#### **UFW (Ubuntu)**
```bash
# Enable Firewall
sudo ufw enable

# Allow SSH
sudo ufw allow ssh

# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Allow MySQL (only if needed)
sudo ufw allow from 192.168.1.0/24 to any port 3306

# Check Status
sudo ufw status
```

#### **iptables (Advanced)**
```bash
# Basic iptables rules
sudo iptables -A INPUT -i lo -j ACCEPT
sudo iptables -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -j DROP

# Save rules
sudo iptables-save > /etc/iptables/rules.v4
```

### 3. **Application Security**

#### **File Permissions**
```bash
# Secure sensitive files
sudo chmod 600 /var/www/dfcms/.env
sudo chmod 600 /var/www/dfcms/config/database.php
sudo chmod 644 /var/www/dfcms/index.php

# Set ownership
sudo chown -R www-data:www-data /var/www/dfcms
sudo chown root:root /var/www/dfcms/.env
sudo chown root:root /var/www/dfcms/config/
```

#### **Disable Directory Listing**
```apache
# In .htaccess or Apache config
Options -Indexes
```

#### **Security Headers**
```apache
# Already configured in Apache/Nginx config above
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## ⚡ Performance Optimization

### 1. **PHP OPcache Configuration**

#### **Enable and Configure OPcache**
```ini
; /etc/php/8.2/mods-available/opcache.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.save_comments=1
opcache.load_comments=1
opcache.validate_timestamps=0
```

### 2. **Redis Configuration**

#### **Redis Configuration**
```bash
# /etc/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 3. **Database Optimization**

#### **Create Indexes**
```sql
-- Performance indexes
CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_complaints_student ON complaints(student_id);
CREATE INDEX idx_complaints_created ON complaints(created_at);
CREATE INDEX idx_notifications_user ON notifications(user_id, is_read);
CREATE INDEX idx_user_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_email_queue_status ON email_queue(status, created_at);
```

### 4. **Content Delivery Network**

#### **Configure CDN for Static Assets**
```php
// config/app.php
return [
    'cdn_url' => 'https://cdn.dfcms.university.edu',
    'assets_version' => '1.0.0'
];
```

---

## 📊 Monitoring & Logging

### 1. **Application Logging**

#### **Configure Logging**
```php
// config/logging.php
return [
    'default' => 'stack',
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
        ],
        'single' => [
            'driver' => 'single',
            'path' => '/var/www/dfcms/logs/app.log',
            'level' => 'info',
        ],
        'security' => [
            'driver' => 'daily',
            'path' => '/var/www/dfcms/logs/security.log',
            'level' => 'warning',
            'days' => 30,
        ]
    ]
];
```

### 2. **System Monitoring**

#### **Install Monitoring Tools**
```bash
# Install htop, iotop, nethogs
sudo apt install htop iotop nethogs

# Install monitoring scripts
sudo mkdir -p /opt/dfcms-monitoring
sudo nano /opt/dfcms-monitoring/health_check.sh
```

#### **Health Check Script**
```bash
#!/bin/bash
# /opt/dfcms-monitoring/health_check.sh

# Check if website is responding
if curl -f -s https://dfcms.university.edu/health > /dev/null; then
    echo "$(date): Website is UP" >> /var/log/dfcms-health.log
else
    echo "$(date): Website is DOWN" >> /var/log/dfcms-health.log
    # Send alert
    curl -X POST "https://api.slack.com/webhooks/..." -d '{"text":"DFCMS website is down!"}'
fi

# Check database connection
if mysql -u dfcms_app -p'password' -e "SELECT 1" > /dev/null 2>&1; then
    echo "$(date): Database is UP" >> /var/log/dfcms-health.log
else
    echo "$(date): Database is DOWN" >> /var/log/dfcms-health.log
fi

# Check Redis
if redis-cli ping > /dev/null 2>&1; then
    echo "$(date): Redis is UP" >> /var/log/dfcms-health.log
else
    echo "$(date): Redis is DOWN" >> /var/log/dfcms-health.log
fi
```

#### **Cron Job for Health Checks**
```bash
# Add to crontab
*/5 * * * * /opt/dfcms-monitoring/health_check.sh
```

### 3. **Log Rotation**

#### **Configure Logrotate**
```bash
# /etc/logrotate.d/dfcms
/var/www/dfcms/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
```

---

## 💾 Backup & Recovery

### 1. **Database Backup**

#### **Automated Backup Script**
```bash
#!/bin/bash
# /opt/dfcms-backup/backup_database.sh

BACKUP_DIR="/opt/dfcms-backup/database"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="dfcms"
DB_USER="dfcms_backup"
DB_PASS="backup_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u $DB_USER -p$DB_PASS --single-transaction --routines --triggers $DB_NAME | gzip > $BACKUP_DIR/dfcms_$DATE.sql.gz

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

# Upload to cloud storage (optional)
# aws s3 cp $BACKUP_DIR/dfcms_$DATE.sql.gz s3://dfcms-backups/database/

echo "Database backup completed: dfcms_$DATE.sql.gz"
```

#### **Cron Job for Daily Backups**
```bash
# Add to crontab
0 2 * * * /opt/dfcms-backup/backup_database.sh
```

### 2. **File Backup**

#### **File Backup Script**
```bash
#!/bin/bash
# /opt/dfcms-backup/backup_files.sh

BACKUP_DIR="/opt/dfcms-backup/files"
DATE=$(date +%Y%m%d_%H%M%S)
SOURCE_DIR="/var/www/dfcms"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create file backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $SOURCE_DIR uploads/ config/ templates/

# Remove backups older than 7 days
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "File backup completed: files_$DATE.tar.gz"
```

### 3. **Recovery Procedures**

#### **Database Recovery**
```bash
# Restore from backup
gunzip < /opt/dfcms-backup/database/dfcms_20240115_020000.sql.gz | mysql -u root -p dfcms
```

#### **File Recovery**
```bash
# Restore files
tar -xzf /opt/dfcms-backup/files/files_20240115_020000.tar.gz -C /var/www/dfcms
```

---

## 🔧 Troubleshooting

### **Common Issues**

#### **1. White Screen / 500 Error**
```bash
# Check PHP error log
tail -f /var/log/php_errors.log

# Check web server error log
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log

# Check file permissions
ls -la /var/www/dfcms/
```

#### **2. Database Connection Error**
```bash
# Test database connection
mysql -u dfcms_app -p -h localhost dfcms

# Check MySQL status
sudo systemctl status mysql

# Check MySQL logs
tail -f /var/log/mysql/error.log
```

#### **3. Slow Performance**
```bash
# Check system resources
htop
iotop
free -h
df -h

# Check MySQL processes
mysql -u root -p -e "SHOW PROCESSLIST;"

# Check slow queries
tail -f /var/log/mysql/slow.log
```

#### **4. Email Not Sending**
```bash
# Test email configuration
php -r "mail('test@example.com', 'Test', 'Test message');"

# Check email logs
tail -f /var/log/mail.log

# Check SMTP connection
telnet smtp.gmail.com 587
```

### **Performance Tuning**

#### **MySQL Performance**
```sql
-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;

-- Analyze table performance
SHOW TABLE STATUS FROM dfcms;

-- Check indexes
SHOW INDEX FROM complaints;
```

#### **PHP Performance**
```bash
# Check OPcache status
php -r "var_dump(opcache_get_status());"

# Check PHP-FPM status
systemctl status php8.2-fpm

# Check PHP-FPM processes
ps aux | grep php-fpm
```

---

## 📋 Deployment Checklist

### **Pre-Deployment**
- [ ] Server requirements verified
- [ ] SSL certificate obtained
- [ ] Database created and configured
- [ ] DNS records configured
- [ ] Firewall rules set
- [ ] Backup strategy planned

### **Deployment**
- [ ] Application files deployed
- [ ] Dependencies installed
- [ ] Configuration files updated
- [ ] Database schema imported
- [ ] Permissions set correctly
- [ ] Services restarted

### **Post-Deployment**
- [ ] Functionality tested
- [ ] Performance monitored
- [ ] Security verified
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Documentation updated

### **Security Review**
- [ ] SSL certificate valid
- [ ] Security headers present
- [ ] File permissions correct
- [ ] Database users limited
- [ ] Error messages sanitized
- [ ] Logging configured

---

## 📞 Support

### **Contact Information**
- **Technical Support**: support@dfcms.university.edu
- **Emergency Contact**: 555-DFCMS-HELP
- **Documentation**: https://docs.dfcms.university.edu
- **Status Page**: https://status.dfcms.university.edu

### **Useful Commands**
```bash
# Restart services
sudo systemctl restart apache2 php8.2-fpm mysql redis

# Check logs
sudo journalctl -u apache2 -f
sudo journalctl -u mysql -f

# Monitor system
sudo htop
sudo iotop
sudo nethogs
```

---

<div align="center">

**🚀 DFCMS Deployment Guide**

*Version 1.0 | Last Updated: January 2024*

*For production deployment assistance, contact our support team*

</div>

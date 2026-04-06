# DFCMS - Digital Feedback & Complaint Management System

## 🏆 Enterprise-Grade University Management Platform

![DFCMS Logo](https://img.shields.io/badge/DFCMS-v2.0.0-success) ![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4) ![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1) ![License](https://img.shields.io/badge/License-MIT-green)

> **Transforming institutional communication through innovative technology and community-driven solutions**

---

## 📋 Table of Contents

- [🎯 Overview](#-overview)
- [✨ Features](#-features)
- [🏗️ Architecture](#️-architecture)
- [🚀 Quick Start](#-quick-start)
- [📚 Documentation](#-documentation)
- [🔧 Configuration](#-configuration)
- [🛡️ Security](#️-security)
- [📊 Monitoring](#-monitoring)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)

---

## 🎯 Overview

The **Digital Feedback & Complaint Management System (DFCMS)** is a comprehensive, enterprise-grade platform designed to revolutionize how educational institutions handle student feedback, complaints, and grievances. Built with modern web technologies and following industry best practices, DFCMS provides a seamless, transparent, and efficient workflow for complaint resolution.

### 🎓 Target Audience
- **Universities and Colleges**
- **Educational Institutions**
- **Administrative Departments**
- **Student Services**
- **Quality Assurance Teams**

### 🌟 Key Differentiators
- **Hierarchical Escalation**: Smart routing based on institutional protocols
- **Real-time Tracking**: Complete audit trails with timestamps
- **Mobile-First Design**: Responsive across all devices
- **Enterprise Security**: Advanced authentication and data protection
- **Analytics Dashboard**: Comprehensive insights and reporting

---

## ✨ Features

### 🔄 Core Workflow Management
- **Smart Complaint Routing**: Automatic escalation based on category and priority
- **Multi-Role Support**: Student → Class Representative → Teacher → HOD workflow
- **Real-time Notifications**: Email and in-app alerts for status updates
- **Evidence Management**: Secure file upload and attachment system
- **Audit Trails**: Complete history of all actions and decisions

### 📱 User Experience
- **Modern Glassmorphism UI**: Beautiful, intuitive interface
- **Responsive Design**: Optimized for desktop, tablet, and mobile
- **Progressive Web App**: Offline capabilities and app-like experience
- **Accessibility**: WCAG 2.1 compliant design
- **Multi-language Support**: Internationalization ready

### 🔐 Security & Compliance
- **Two-Factor Authentication**: TOTP-based 2FA support
- **JWT Token Management**: Secure API authentication
- **Data Encryption**: AES-256 encryption for sensitive data
- **Role-Based Access Control**: Granular permissions system
- **Audit Logging**: Comprehensive security event tracking
- **GDPR Compliance**: Data protection and privacy features

### 📊 Analytics & Monitoring
- **Real-time Dashboard**: System health and performance metrics
- **Complaint Analytics**: Volume, trends, and resolution times
- **User Activity Tracking**: Engagement and usage statistics
- **Email Performance**: Delivery rates and engagement metrics
- **Security Monitoring**: Threat detection and incident response

### 🛠️ Administrative Features
- **Bulk Operations**: Mass actions for efficient management
- **Export Functionality**: PDF, Excel, CSV data exports
- **Backup & Recovery**: Automated backup system
- **Maintenance Mode**: Controlled system updates
- **API Integration**: RESTful API for third-party integrations

---

## 🏗️ Architecture

### 📐 System Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│                 │    │                 │    │                 │
│ • React/Vue.js  │◄──►│ • PHP 8.2+      │◄──►│ • MySQL 8.0+    │
│ • Bootstrap 5.3 │    │ • REST API      │    │ • Redis Cache   │
│ • PWA Support   │    │ • JWT Auth      │    │ • File Storage  │
│ • Responsive    │    │ • Email Queue   │    │ • Backups       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌─────────────────┐
                    │   Services      │
                    │                 │
                    │ • Email Service │
                    │ • Security      │
                    │ • Monitoring    │
                    │ • Notifications │
                    └─────────────────┘
```

### 🔄 Data Flow

```
Student Submission → Validation → Database → Email Notifications → Role Assignment → Processing → Resolution → Archive
```

### 🗂️ Database Schema

- **users**: User accounts and authentication
- **complaints**: Complaint records and metadata
- **complaint_history**: Audit trail of all actions
- **notifications**: User notification system
- **email_queue**: Bulk email processing
- **security_audit_log**: Security event tracking
- **user_sessions**: Session management
- **rate_limits**: API rate limiting

---

## 🚀 Quick Start

### 📋 Prerequisites

- **PHP 8.2+** with extensions: `pdo`, `mysqli`, `openssl`, `mbstring`
- **MySQL 8.0+** or MariaDB 10.5+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Composer** for dependency management
- **Node.js** (optional, for frontend development)

### ⚡ Installation Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/Kenenisaboru/dfcms.git
   cd dfcms
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install  # optional
   ```

3. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p
   CREATE DATABASE dfcms;
   
   # Import schema
   mysql -u root -p dfcms < database.sql
   mysql -u root -p dfcms < database_updates.sql
   ```

4. **Configuration**
   ```bash
   # Copy configuration template
   cp config/database.php.example config/database.php
   
   # Edit configuration
   nano config/database.php
   ```

5. **Environment Setup**
   ```bash
   # Create .env file
   cp .env.example .env
   
   # Set environment variables
   nano .env
   ```

6. **Set Permissions**
   ```bash
   chmod 755 -R .
   chmod 777 uploads/
   chmod 777 logs/
   ```

7. **Access Application**
   ```
   http://localhost/dfcms/
   ```

### 🔧 Default Credentials

- **Admin**: `admin@dfcms.edu` / `admin123`
- **HOD**: `hod@dfcms.edu` / `hod123`
- **Teacher**: `teacher@dfcms.edu` / `teacher123`
- **Student**: `student@dfcms.edu` / `student123`

---

## 📚 Documentation

### 📖 User Guides
- [Student Guide](docs/user-guides/student-guide.md)
- [Class Representative Guide](docs/user-guides/cr-guide.md)
- [Teacher Guide](docs/user-guides/teacher-guide.md)
- [HOD Guide](docs/user-guides/hod-guide.md)
- [Administrator Guide](docs/user-guides/admin-guide.md)

### 🔧 Technical Documentation
- [API Documentation](docs/api/api-documentation.md)
- [Database Schema](docs/database/database-schema.md)
- [Security Guide](docs/security/security-guide.md)
- [Deployment Guide](docs/deployment/deployment-guide.md)
- [Configuration Reference](docs/configuration/configuration-reference.md)

### 🛠️ Development
- [Contributing Guidelines](docs/development/contributing.md)
- [Coding Standards](docs/development/coding-standards.md)
- [Testing Guide](docs/development/testing.md)
- [Architecture Overview](docs/development/architecture.md)

---

## 🔧 Configuration

### 🌍 Environment Variables

```bash
# Database Configuration
DB_HOST=127.0.0.1
DB_NAME=dfcms
DB_USER=dfcms_user
DB_PASSWORD=secure_password

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
FROM_EMAIL=noreply@dfcms.university.edu

# Security Configuration
ENCRYPTION_KEY=your-32-character-encryption-key
JWT_SECRET=your-jwt-secret-key
TWOFA_SECRET=your-2fa-secret-key

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dfcms.university.edu
```

### 📧 Email Configuration

```php
// config/email_config.php
return [
    'smtp' => [
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
        'encryption' => 'tls'
    ],
    'from' => [
        'email' => getenv('FROM_EMAIL'),
        'name' => 'DFCMS System'
    ]
];
```

### 🛡️ Security Configuration

```php
// config/security_config.php
return [
    'two_factor' => [
        'enabled' => true,
        'issuer' => 'DFCMS University',
        'code_expiry' => 300
    ],
    'jwt' => [
        'algorithm' => 'HS256',
        'access_token_expiry' => 3600,
        'refresh_token_expiry' => 604800
    ],
    'password_policy' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special' => true
    ]
];
```

---

## 🛡️ Security

### 🔐 Authentication & Authorization
- **Multi-Factor Authentication**: TOTP-based 2FA
- **JWT Token Management**: Secure API authentication
- **Session Security**: Secure session handling with timeout
- **Password Policies**: Strong password requirements
- **Account Lockout**: Protection against brute force attacks

### 🔒 Data Protection
- **Encryption at Rest**: AES-256 encryption for sensitive data
- **Encryption in Transit**: TLS/SSL for all communications
- **Data Masking**: Sensitive information protection
- **Backup Encryption**: Encrypted backup storage
- **GDPR Compliance**: Data protection and privacy

### 🛡️ Security Monitoring
- **Audit Logging**: Comprehensive security event tracking
- **Rate Limiting**: API abuse prevention
- **IP Whitelisting**: Access control by IP address
- **Security Headers**: OWASP recommended headers
- **Vulnerability Scanning**: Regular security assessments

---

## 📊 Monitoring & Analytics

### 📈 System Monitoring
- **Real-time Dashboard**: System health metrics
- **Performance Monitoring**: Response times and resource usage
- **Error Tracking**: Comprehensive error logging
- **Uptime Monitoring**: Service availability tracking
- **Resource Monitoring**: CPU, memory, and disk usage

### 📊 Analytics
- **User Analytics**: Active users and engagement
- **Complaint Analytics**: Volume, trends, and patterns
- **Performance Metrics**: Resolution times and efficiency
- **Email Analytics**: Delivery rates and engagement
- **Security Analytics**: Threat detection and incidents

### 📋 Reporting
- **Automated Reports**: Scheduled report generation
- **Custom Reports**: Flexible report builder
- **Data Export**: Multiple format support (PDF, Excel, CSV)
- **Visual Analytics**: Charts and graphs
- **Trend Analysis**: Historical data analysis

---

## 🤝 Contributing

We welcome contributions to the DFCMS project! Please read our [Contributing Guidelines](docs/development/contributing.md) for details.

### 🚀 Getting Started
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

### 📋 Development Standards
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Write comprehensive tests
- Update documentation
- Use semantic versioning

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **University Partners**: For feedback and requirements
- **Development Team**: For dedication and expertise
- **Open Source Community**: For valuable libraries and tools
- **User Community**: For continuous feedback and improvement

---

## 📞 Support

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/Kenenisaboru/dfcms/issues)
- **Discussions**: [GitHub Discussions](https://github.com/Kenenisaboru/dfcms/discussions)
- **Email**: support@dfcms.university.edu

---

## 🗺️ Roadmap

### 🎯 Version 2.1 (Q2 2024)
- [ ] Mobile App (React Native)
- [ ] Advanced Analytics Dashboard
- [ ] Integration with LMS Systems
- [ ] AI-powered Complaint Categorization

### 🎯 Version 2.2 (Q3 2024)
- [ ] Multi-tenant Support
- [ ] Advanced Workflow Builder
- [ ] Video Conference Integration
- [ ] Blockchain-based Audit Trail

### 🎯 Version 3.0 (Q4 2024)
- [ ] Microservices Architecture
- [ ] Kubernetes Deployment
- [ ] Machine Learning Insights
- [ ] Global Deployment Support

---

<div align="center">

**🌟 Transforming Education Through Technology 🌟**

Made with ❤️ by the DFCMS Team

[![GitHub stars](https://img.shields.io/github/stars/Kenenisaboru/dfcms?style=social)](https://github.com/Kenenisaboru/dfcms/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/Kenenisaboru/dfcms?style=social)](https://github.com/Kenenisaboru/dfcms/network)
[![GitHub issues](https://img.shields.io/github/issues/Kenenisaboru/dfcms)](https://github.com/Kenenisaboru/dfcms/issues)

</div>

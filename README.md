# 🎓 SFCMS: Student Feedback & Complaint Management System
## *Bridging the Communication Gap with Transparency and Accountability*

![SFCMS Banner](https://img.shields.io/badge/Status-Active-brightgreen?style=for-the-badge)
![PHP Version](https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php)
![Database](https://img.shields.io/badge/Database-MySQL-orange?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)

---

## 🌟 Project Vision
The **Student Feedback and Complaint Management System (SFCMS)** is a state-of-the-art digital ecosystem designed to transform how educational institutions handle student grievances. By replacing fragmented manual processes with a centralized, secure, and data-driven platform, SFCMS ensures that every student voice is heard and every issue is tracked to resolution.

---

## 📚 Documentation Center
We have structured our documentation into four specialized guides to help you get the most out of SFCMS:

| Guide | Target Audience | Description |
| :--- | :--- | :--- |
| [📂 **Strategic Proposal**](docs/STRATEGIC_PROPOSAL.md) | Stakeholders | Objectives, methodology, budget, and project scope. |
| [📘 **User Guide**](docs/USER_GUIDE.md) | Students/Staff | How to use the portals, submit complaints, and track status. |
| [🛠️ **Developer Guide**](docs/DEVELOPER_GUIDE.md) | Developers | Technical architecture, file structure, and security standards. |
| [🔌 **API Reference**](docs/API_REFERENCE.md) | Developers | Documentation for internal AJAX endpoints and JSON responses. |

---

## 🚀 Key Features
- **Multi-Role Portal**: Dedicated environments for Students, Teachers, HODs, and Admins.
- **Real-Time Tracking**: Visual progress timelines for every submitted complaint.
- **Automated Routing**: Intelligent ticket assignment based on category (Academic, Facilities, Admin).
- **Security First**: AES-256-GCM encryption, JWT authentication, and full audit logging.
- **Analytics Dashboard**: Heatmaps and efficiency metrics for institutional oversight.

---

## 🛠️ Technology Stack
- **Frontend**: Modern CSS3 (Glassmorphism), JavaScript (ES6+), Bootstrap 5.3.
- **Backend**: PHP 8.2 (Secure PDO, Role-Based Access Control).
- **Database**: MySQL 8.0+ (Optimized InnoDB Engine).
- **Security**: SecurityService layer with Rate Limiting and CSRF Protection.

---

## 💻 Local Setup Instructions

### 1. Prerequisites
- **XAMPP / WAMP** installed with PHP 8.2+ and MySQL.
- A modern web browser (Chrome, Firefox, Edge).

### 2. Installation Steps
1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/Kenenisaboru/dfcms.git
    ```
2.  **Move to Htdocs**: Place the folder in your `C:\xampp\htdocs\` directory.
3.  **Database Setup**:
    - Open `phpMyAdmin` and create a database named `dfcms`.
    - Import the `database.sql` file provided in the root.
4.  **Configuration**: Edit `config/database.php` with your local credentials.
5.  **Access the App**: Navigate to `http://localhost/dfcms` in your browser.

---

## 👥 System Roles
- **Student**: Submit grievances and track their lifecycle.
- **Class Rep (CR)**: Triage group issues and facilitate departmental communication.
- **Teacher/Staff**: Resolve academic and facility-related incidents.
- **Admin/HOD**: Strategic oversight, ticket delegation, and performance analytics.

---

## 📜 License
Distributed under the MIT License. See `LICENSE` for more information.

---

**Developed with ❤️ by the SFCMS Engineering Core**
*Empowering Students, Modernizing Education.*

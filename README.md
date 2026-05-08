# 🎓 Campus Placement Hub

<div align="center">

![Project Status](https://img.shields.io/badge/Status-Active-brightgreen?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)
![Made With](https://img.shields.io/badge/Made%20With-❤️-red?style=for-the-badge)

**A modern, full-stack web application bridging academic potential and professional success.**

[Features](#-key-features) • [Tech Stack](#️-technology-stack) • [Installation](#-installation--setup) • [Project Structure](#-project-structure) • [Contributors](#-contributors)

</div>

---

## 📖 Overview

**Campus Placement Hub (MYCPH)** is a premium placement management system built for university placement cells. It streamlines the entire recruitment workflow — from managing company partnerships and job postings to tracking student applications and recording final placements.

> 🎯 **Goal:** Eliminate manual paperwork and Excel chaos from campus recruitment. Replace it with a clean, data-driven platform that works for everyone.

---

## 🌟 Key Features

### 👨‍🎓 Student Portal
| Feature | Description |
|---|---|
| 📊 **Personalized Dashboard** | Track CGPA, backlogs, active applications, and live placement status at a glance |
| 📄 **Resume Management** | Upload and manage PDF resumes securely |
| 🔍 **Job Discovery** | Browse curated opportunities from top-tier hiring partners |
| 📬 **Application Tracking** | Monitor application statuses in real-time |
| 👤 **Profile Builder** | Maintain a complete academic and professional profile |

### 🛡️ Admin Portal
| Feature | Description |
|---|---|
| 📈 **Analytics Dashboard** | Interactive charts and visualizations via Chart.js — placement stats, trends, and company-wise data |
| 🏢 **Company Management** | Add, edit, and manage hiring partner profiles and job drives |
| 💼 **Job & Internship Drives** | Create and manage job postings with eligibility criteria |
| 🎓 **Student Oversight** | Full CRUD for student records, marks, and academic data |
| 📥 **Data Import** | Bulk-import student and marks data via Excel/CSV |
| 🏆 **Placement Records** | Log successful placements with package (LPA) details |

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Styling** | Custom Design System (`style.css`, `modern.css`) |
| **Icons** | [Lucide Icons](https://lucide.dev/) |
| **Charts** | [Chart.js](https://www.chartjs.org/) |
| **Date Picker** | [Flatpickr](https://flatpickr.js.org/) |
| **Backend** | Core PHP (Procedural + OOP) |
| **Database** | MySQL 8.0 |
| **Local Environment** | Laragon / XAMPP compatible |

---

## 📂 Project Structure

```text
MYCPH/
├── 📁 admin/               # Administrator portal — dashboards, CRUD modules, analytics
├── 📁 api/                 # REST-style API endpoints for dynamic data fetching
├── 📁 assets/              # Static assets — CSS, fonts, images
├── 📁 docs/                # Project documentation, SQL schema, reports
├── 📁 includes/            # Reusable PHP components — config, header, sidebar, auth
├── 📁 js/                  # Frontend logic — validation, pagination, interactivity
├── 📁 student/             # Student portal — profile, job listings, applications
├── 📁 uploads/             # Secure storage for student-uploaded resumes (PDF)
├── 📁 uploaded_files/      # Additional file storage for processed uploads
├── 📄 about.php            # Team & creator information page
├── 📄 index.php            # Entry point & routing handler
└── 📄 mainpage.html        # Public-facing modern landing page
```

---

## 🚀 Installation & Setup

Follow these steps to get the project running on your local machine:

### Prerequisites
- PHP 8.0+
- MySQL 8.0
- [Laragon](https://laragon.org/) or XAMPP

---

### Step-by-Step

**1. Clone the repository**
```bash
git clone https://github.com/Arman-0909/Campus-Placement-Hub.git
```

**2. Place in server root**

Move the cloned folder into your local server's web root:
```
C:\laragon\www\MYCPH\
```
or for XAMPP:
```
C:\xampp\htdocs\MYCPH\
```

**3. Set up the database**

- Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
- Create a new database named **`miniproject`**
- Import the `.sql` schema file located in the `/docs` folder

**4. Configure database credentials**

Open `includes/config.php` and update with your credentials:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');     // your MySQL username
define('DB_PASSWORD', '');         // your MySQL password
define('DB_NAME', 'miniproject');
```

**5. Set folder permissions**

Ensure the `uploads/resumes/` directory is **writable** so students can upload PDF resumes.

**6. Launch the application**

Open your browser and navigate to:
```
http://localhost/MYCPH
```

---

## 👥 Contributors

<table>
  <tr>
    <td align="center">
      <b>Armandeep Singh</b><br/>
      <sub>Lead Developer & Architect</sub>
    </td>
  </tr>
</table>

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<div align="center">

*Designed with precision to empower the next generation of professionals.*

⭐ If you found this project useful, consider giving it a star!

</div>

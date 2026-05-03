# 🎓 Campus Placement Hub (MYCPH)

![Project Status](https://img.shields.io/badge/Status-Active-success)
![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)
![Database](https://img.shields.io/badge/MySQL-8.0-orange)
![License](https://img.shields.io/badge/License-MIT-green)

**Campus Placement Hub** is a premium, modern web application designed to bridge the gap between academic potential and professional success. Built for university placement cells, it streamlines the entire recruitment workflow—allowing administrators to manage companies, track job postings, and monitor placement records, while empowering students to build their profiles, apply for jobs, and upload their resumes.

---

## 🌟 Key Features

### 👨‍🎓 For Students
*   **Intuitive Dashboard:** A personalized hub tracking CGPA, backlogs, and live placement status.
*   **Resume Management:** Easily upload and manage PDF resumes.
*   **Job Discovery:** Explore tailored opportunities from top-tier global companies.
*   **Application Tracking:** Monitor the status of job applications in real-time.

### 🛡️ For Administrators
*   **Comprehensive Analytics:** Rich, interactive charts and data visualizations (via Chart.js) detailing placement statistics.
*   **Company & Job Management:** Create, edit, and manage hiring partners, job descriptions, and internship drives.
*   **Student Oversight:** Manage student records, academic data, and track individual placement progress.
*   **Data Import/Export:** Seamlessly import student and marks data via Excel/CSV.
*   **Placement Records:** Maintain an official log of successful placements and average packages (LPA).

---

## 🛠️ Technology Stack

*   **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
*   **Styling:** Custom scalable design system (`style.css`), Modern layout engine (`modern.css`)
*   **Icons & UI:** [Lucide Icons](https://lucide.dev/), [Chart.js](https://www.chartjs.org/), [Flatpickr](https://flatpickr.js.org/)
*   **Backend:** Core PHP (Procedural/OOP mix)
*   **Database:** MySQL
*   **Environment:** Laragon / XAMPP compatible

---

## 📂 Project Structure

```text
MYCPH/
├── admin/               # Administrator portal (Dashboards, CRUD modules)
├── api/                 # API endpoints for dynamic data fetching
├── assets/              # Static assets (CSS, Fonts, Images)
├── docs/                # Project documentation
├── includes/            # Reusable PHP components (Header, Sidebar, Config)
├── js/                  # Frontend logic (Validation, Pagination, Interactivity)
├── scripts/             # Backend maintenance scripts
├── student/             # Student portal (Profile, Jobs, Applications)
├── uploads/             # Secure storage for student resumes
├── about.php            # Team & Creator information
├── index.php            # Entry routing
└── mainpage.html        # Public-facing modern landing page
```

---

## 🚀 Installation & Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/Campus-Placement-Hub.git
    ```
2.  **Move to local server:**
    Place the project folder into your local server's root directory (e.g., `C:\laragon\www\MYCPH` or `htdocs\MYCPH`).
3.  **Database Configuration:**
    *   Create a new MySQL database named `mycph_db` (or your preferred name).
    *   Import the provided `.sql` file (usually found in `/docs` or the root folder) into your database.
    *   Update `includes/config.php` with your database credentials.
4.  **Permissions:**
    Ensure the `uploads/resumes/` directory is writable for handling PDF uploads.
5.  **Run the application:**
    Open your browser and navigate to `http://localhost/MYCPH`.

---

## 👥 Contributors

*   **Armandeep Singh** - *Lead Developer*

---

> Designed with precision to empower the next generation of professionals.

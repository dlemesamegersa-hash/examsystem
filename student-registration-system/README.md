# Student Registration System (SRS)

A responsive, secure, web-based **Student Registration System** built with **PHP (PDO)**, **MySQL**, **Bootstrap 4.6**, and **AdminLTE 3**, designed for local deployment using **XAMPP** (Apache + MySQL + phpMyAdmin).

---

## 📌 Project Overview & Features

### 1. Public Portal
- **Home (`index.php`)**: Hero showcase, admissions overview, statistics counters, feature cards, and direct links to Login and Registration.
- **About Us (`about.php`)**: Institutional background, system architecture details, core modules overview, and admissions support links.
- **Contact Us (`contact.php`)**: Validated contact form that records inquiries into the `contact_messages` database table with instant feedback.
- **Registration (`register.php`)**: Public account registration with client/server validation, email and username duplicate prevention, and Bcrypt password hashing.
- **Login (`login.php`)**: Secure authentication supporting username or email login, session fixation protection, and active account verification.

### 2. Admin & Staff Dashboard (AdminLTE 3)
- **Dashboard (`dashboard.php`)**:
  - Live metric summary cards: Total Students, Active Enrollments, Total Users, and Inquiries.
  - Recently enrolled students table with quick action links.
  - Quick action shortcuts and incoming contact messages widget.
- **Student Registration (`student_register.php`)**:
  - Full student profile form: Full Name, Gender, Date of Birth, Email, Phone, Address, Department, Registration Number, Admission Date, Status (Active/Inactive), and Photo upload.
  - Real-time photo preview with 3MB upload limit and image MIME-type validation.
  - Duplicate check on Registration Number and Email.
- **Show Students (`students.php`)**:
  - Searchable by name, registration number, email, or phone.
  - Filterable by department and status.
  - Responsive tabular view with student thumbnail, ID, registration number, department, status badge, and action buttons.
  - Pagination support.
  - Modal confirmation prior to student record deletion.
- **Student Profile View (`student_view.php`)**: Detailed breakdown card with printable slip layout.
- **Edit Student (`student_edit.php`)**: Full update capability with existing photo retention or replacement.
- **User Management (`users.php`)** *(Admin Only)*:
  - List all users with roles and status.
  - Add new staff or administrator accounts (`user_add.php`).
  - Edit user information, role assignment, and password reset (`user_edit.php`).
  - Safeguard preventing administrators from deleting or deactivating their own active account (`user_delete.php`).
- **Contact Inquiries (`messages.php`)**: Inbox displaying visitor inquiries with detail view modal and email reply action.

---

## 🔐 Security Specifications

- **SQL Injection Prevention**: 100% of database interactions utilize PDO prepared statements with parameter binding.
- **Password Security**: Passwords hashed with `PASSWORD_DEFAULT` (Bcrypt) and verified using `password_verify()`.
- **Cross-Site Request Forgery (CSRF)**: Forms protected by cryptographically secure session CSRF tokens (`hash_equals()` verified).
- **Cross-Site Scripting (XSS)**: All dynamic database outputs escaped using `htmlspecialchars()` with `ENT_QUOTES`.
- **Role-Based Access Control (RBAC)**: Strict role checking (`admin` vs. `staff`) ensuring sensitive administration pages (e.g. User Management) cannot be accessed by unauthorized accounts.
- **Session Protection**: Session cookies configured with `HttpOnly`, `SameSite=Lax`, and `session_regenerate_id(true)` upon authentication.

---

## 📂 File Structure

```
stud_reg_system/
│
├── config/
│   └── db.php                     # PDO connection, base URL helper, session initialization
│
├── includes/
│   ├── auth_check.php             # Session authentication and admin privilege guards
│   ├── functions.php              # Flash messages, CSRF, sanitization, photo upload handler
│   ├── public_header.php          # Public site navigation and headers
│   ├── public_footer.php          # Public site footer and scripts
│   ├── header.php                 # AdminLTE 3 header and top navigation
│   ├── sidebar.php                # AdminLTE 3 collapsible sidebar with active links
│   └── footer.php                 # AdminLTE 3 footer, delete confirmation modal & scripts
│
├── assets/
│   ├── css/
│   │   └── custom.css             # Custom styling for public site and dashboard
│   ├── js/
│   │   └── custom.js              # Client-side validation, password toggles, delete modal
│   └── images/
│       └── avatar.svg             # Default profile placeholder
│
├── uploads/
│   └── students/                  # Secure directory for student profile photographs
│       └── .gitkeep
│
├── database/
│   └── stud_reg_system.sql        # MySQL database schema and sample seed records
│
├── index.php                      # Public Home Page
├── about.php                      # Public About Us Page
├── contact.php                    # Public Contact Us Form
├── register.php                   # Public User Registration
├── login.php                      # User & Administrator Login
├── logout.php                     # Secure Session Termination
│
├── dashboard.php                  # AdminLTE 3 Admin Dashboard
├── student_register.php           # Register New Student Module
├── students.php                   # Show & Search Students Module
├── student_view.php               # Student Profile Details
├── student_edit.php               # Edit Student Record
├── student_delete.php             # Safe Delete Student Action
│
├── users.php                      # User Management Module (Admin)
├── user_add.php                   # Add New System User (Admin)
├── user_edit.php                  # Edit User & Password Reset (Admin)
├── user_delete.php                # Delete User Action (Admin)
│
├── messages.php                   # Contact Messages Inbox
└── README.md                      # Documentation & Setup Guide
```

---

## 🚀 Setup & Installation Guide for XAMPP

### Step 1: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 2: Verify Folder Location
Ensure the project folder resides inside the XAMPP `htdocs` directory:
```
C:\xampp\htdocs\stud_reg_system\
```

### Step 3: Import the Database into phpMyAdmin
1. Open your web browser and navigate to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab in the top navigation bar (or create a database named `stud_reg_system` first).
3. Click **Choose File** and browse to:
   ```
   C:\xampp\htdocs\stud_reg_system\database\stud_reg_system.sql
   ```
4. Scroll to the bottom and click **Import** (or **Go**).
5. All 3 tables (`users`, `students`, `contact_messages`) and demo seed records will be created.

### Step 4: Access the Application
- **Public Website**:
  [http://localhost/stud_reg_system/index.php](http://localhost/stud_reg_system/index.php)
- **Portal Login**:
  [http://localhost/stud_reg_system/login.php](http://localhost/stud_reg_system/login.php)

---

## 🔑 Default Login Credentials for Testing

| Role | Username | Email | Password | Access Level |
|---|---|---|---|---|
| **Administrator** | `admin` | `admin@example.com` | `admin123` | Full administrative control (Dashboard, Students, User Management, Messages) |
| **Staff Member** | `staff` | `staff@example.com` | `staff123` | Operational access (Dashboard, Students, Register Student, Messages) |

*You can also register new staff or administrator accounts at any time from [http://localhost/stud_reg_system/register.php](http://localhost/stud_reg_system/register.php).*

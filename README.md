# Student Complaint Management System

A fully functional, polished Student Complaint Management System built with PHP and MySQL. Designed with a modern, responsive UI and comprehensive feature set.

## Key Features

### 1. **Modern User Interface**
- **Premium Design**: Glass-morphism inspired dashboard, gradient sidebars, and soft-shadow cards.
- **Responsive**: Fully functional on desktop and mobile devices.
- **Interactive**: Hover effects, smooth transitions, and distinct status indicators.

### 2. **Authentication & Security**
- **Secure Login/Registration**: Password hashing (`password_hash`), session management with timeout (5 min).
- **Role-Based Access**: Strict separation between Admin and Student capabilities.
- **Auto-Block**: Inactive users are automatically prevented from logging in.
- **Remember Me**: Secure cookie-based username persistence.

### 3. **Smart Dashboard**
- **Live Statistics**: Real-time counters for Pending, In Progress, and Resolved complaints.
- **Notifications**: Integrated badge system alerting Admins of new complaints and Students of status updates.

### 4. **Complaint Management**
- **Lodge Complaints**: Students can easily file complaints with Title, Category, and Details.
- **Search & Filter**: Powerful search bar to find complaints by ID, Name, or Status.
- **Status Tracking**: Admins can update status (Pending -> In Progress -> Resolved), which triggers instant notifications to the student.

### 5. **Administration**
- **User Management**: Add, Edit, or Delete users. Reset passwords.
- **System Config**: Manage dynamic Categories and Departments.

## Installation Steps
1. **Clone/Download** the repository to your `htdocs` folder.
2. **Database Setup**:
   - Create a database `complaint_system`.
   - Import `database/complaint_system.sql`.
3. **Configuration**:
   - Verify `includes/db_connect.php` matches your MySQL credentials.
4. **Launch**:
   - Open `http://localhost/student-complaint-system/`.

## Login Credentials
- **Admin**:
  - Username: `admin`
  - Password: `admin123`
- **Student**:
  - Register a new account (Default role is Student).

## Troubleshooting
- **Assets 404**: The system auto-detects paths. If styles miss, check `includes/header.php`.
- **Upload Errors**: The system auto-creates `uploads/profiles/`. Ensure read/write permissions are set.

## Project Team
- Name: Omar Abdi Ashkir ID: IT22129132
- Name: Nuradin Mohamed Abdulahi : IT22129131
- Name: Omar Ibrahim hassan ID: IT22129133
- NAME: Hanad hayir mahamud  ID:25129199






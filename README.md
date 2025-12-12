# 🍃 Employee Leave Management System

A responsive web application developed to automate the employee leave management process. This system allows employees to apply for leave online and enables administrators to approve or reject requests efficiently.

## 🚀 Features

### Admin Panel
* **Dashboard:** View total employees, departments, and leave types.
* **Department Management:** Add, edit, or delete company departments.
* **Leave Type Management:** Configure different leave types (Casual, Sick, Earned).
* **Employee Management:** Register new employees and manage their profiles.
* **Leave Action:** Approve or reject leave applications with remarks.

### Employee Panel
* **Secure Login:** Individual accounts for every employee.
* **Apply for Leave:** Easy interface to select dates and leave type.
* **Leave History:** View past applications and their current status (Pending/Approved/Rejected).


## 🛠️ Tech Stack
* **Frontend:** HTML, CSS, JavaScript, Bootstrap 
* **Backend:** PHP
* **Database:** MySQL

## ⚙️ How to Run Locally

1.  **Download the Code:** Click `Code` > `Download ZIP` or clone this repo.
2.  **Move Files:** Extract the folder to your XAMPP/WAMP `htdocs` directory.
3.  **Database Setup:**
    * Open `phpMyAdmin` (http://localhost/phpmyadmin).
    * Create a new database named `elms` (or your database name).
    * Import the `database.sql` file located in the root folder.
4.  **Configure:**
    * Open `includes/config.php` (or your connection file).
    * Update the database credentials if necessary.
5.  **Run:** Open your browser and go to `http://localhost/leave-management-system`.

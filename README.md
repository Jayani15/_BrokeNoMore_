# PHP Expense Tracker

A simple web-based expense tracking application built with PHP and MySQL.

---

## Features

- User-friendly interface to add, edit, and delete expenses  
- Budget management and tracking  
- Responsive design for desktop and mobile  
- Secure database connection using environment variables  

---

## Technology Stack

- PHP  
- MySQL (phpMyAdmin)  
- HTML, CSS, JavaScript  
- Apache (XAMPP or similar local server)  

---

## Setup Instructions (Detailed)

### 1. Clone the repository

Open your terminal or command prompt and run:

```bash```
git clone https://github.com/Jayani15/my-php-expense-tracker.git
cd my-php-expense-tracker
This downloads the project files to your local machine.

### 2. Create a new MySQL database

Open phpMyAdmin by visiting http://localhost/phpmyadmin in your browser.

Click on Databases tab.

Enter a name for your database, for example: expense_tracker.

Click Create.

### 3. Import the database schema and data

In phpMyAdmin, select the database you just created (expense_tracker).

Click the Import tab.

Click Choose File and select the SQL dump file from the project folder, e.g.:
sql/database_dump.sql
(If you don’t have this file, you can export your current database or create one based on your tables.)

Click Go to import the tables and data.

### 4. Configure your environment variables

Copy the file .env.example to .env in your project root. This file stores sensitive information and should never be pushed to GitHub.

For example, in terminal (from project root):

bash
Copy
Edit
copy .env.example .env
Open .env in a text editor and update the database credentials to match your local setup:

ini
Copy
Edit
DB_HOST=localhost
DB_USER=root
DB_PASS=your_mysql_password
DB_NAME=expense_tracker
If you don’t use a password for root, leave DB_PASS empty.

### 5. Start your local server
Launch XAMPP, WAMP, or your preferred local server stack.

Ensure Apache and MySQL services are running.

Place your project inside the server’s web root folder (e.g., C:\xampp\htdocs\Phase2).

6. Access the application in your browser
Navigate to the frontend page, for example:

bash
Copy
Edit
http://localhost/Phase2/frontend/your_budget.html
You should see your expense tracker’s user interface.

Usage
Register or login to start tracking your expenses.

Add new budgets and expenses easily via the UI.

View summaries and reports of your spending habits.

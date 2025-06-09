# 💸 Expense Tracker Website

An easy-to-use personal finance management web app that lets users record and track their daily expenses. Built with core web technologies and hosted online for public use.

🔗 **Live Demo:** [https://jaii.ct.ws](https://jaii.ct.ws)

---

## 🛠 Tech Stack

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  
- **Server:** Apache (via InfinityFree Hosting)

---

## 🚀 Features

- User registration and login
- Secure password handling (optionally hashed with `password_hash`)
- Add, update, and delete expense entries
- View daily/monthly total expenses
- Responsive design for mobile and desktop
- Persistent data storage using MySQL

---

## 📁 Folder Structure

```plaintext
project/
│
├── css/
│   └── styles.css
├── js/
│   └── script.js
├── images/
│   └── icons, backgrounds, etc.
├── register.html
├── login.html
├── dashboard.html
├── expenses.html
├── add-expense.html
├── login.php
├── register.php
├── db.php
├── add_expense.php
├── delete_expense.php
└── README.md
🧑‍💻 Setup Instructions
Clone the repository or download the source code.

Set up a local server using XAMPP, MAMP, or host on InfinityFree.

Import the included SQL file into phpMyAdmin to set up the MySQL database.

Configure db.php with your database credentials:

php
Copy code
$conn = mysqli_connect("localhost", "username", "password", "database_name");
Launch the site in your browser by opening index.html or through your hosted domain.

📦 Deployment
The site is hosted using InfinityFree, a free web hosting platform:

Files uploaded using FTP (e.g., FileZilla)

Domain configured at https://jaii.ct.ws

MySQL database managed via InfinityFree's cPanel

⚠️ Known Issues / Limitations
No HTTPS (SSL) on free hosting

Session management can be improved

Minimal data validation on client-side

📌 Future Improvements
Implement budgeting features

Export expenses to Excel or PDF

Add category-wise charts (using Chart.js)

Two-factor authentication

Progressive Web App (PWA) support

🧑 Author
Developed by Jaii

If you liked this project, feel free to ⭐ the repo and share your feedback!

vbnet
Copy code

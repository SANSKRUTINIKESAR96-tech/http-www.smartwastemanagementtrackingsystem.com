# ♻️ Waste Management System — Beginner Setup Guide

A simple website to manage waste collection.
Built with **HTML, CSS, JavaScript, PHP, and MySQL**.

This guide will help you run the project on your computer in **6 easy steps**.
No coding experience needed!

---

## 🧰 What you need (install these first)

You need **one** of these "all-in-one" packages. They install PHP, MySQL, and Apache together.

| Your OS  | Download this (free) |
|----------|----------------------|
| Windows  | **XAMPP** → https://www.apachefriends.org/ |
| Mac      | **XAMPP** or **MAMP** → https://www.mamp.info/ |
| Linux    | **XAMPP** → https://www.apachefriends.org/ |

👉 Just download, double-click the installer, and click **Next → Next → Finish**.

---

## ▶️ Step 1 — Start Apache and MySQL

1. Open **XAMPP Control Panel** (or MAMP).
2. Click the **Start** button next to **Apache**.
3. Click the **Start** button next to **MySQL**.

Both should turn **green**. ✅

> If the green light doesn't appear, another program is using the port.
> Close Skype/Zoom and try again.

---

## 📁 Step 2 — Copy the project to the right folder

Move the **`WastManagementSystem`** folder into the web folder of your installation:

| If you installed... | Put the folder here |
|---------------------|---------------------|
| XAMPP on Windows    | `C:\xampp\htdocs\` |
| XAMPP on Mac/Linux  | `/Applications/XAMPP/htdocs/` |
| MAMP on Mac         | `/Applications/MAMP/htdocs/` |
| WAMP on Windows     | `C:\wamp64\www\` |

After copying, the folder path should look like this:
```
C:\xampp\htdocs\WastManagementSystem\
```

---

## 🗄️ Step 3 — Create the database

1. Open your browser and go to:
   ```
   http://localhost/phpmyadmin
   ```
2. Click the **Import** tab at the top.
3. Click **Choose File** and select:
   ```
   WastManagementSystem/database/schema.sql
   ```
4. Scroll down and click the **Import** (or **Go**) button.

✅ You should see a green message:
**"Import has been successfully finished"**

This creates a database called **`waste_management`** with all the tables.

---

## ⚙️ Step 4 — Check the database password (usually skip this)

Open this file in any text editor (Notepad, VS Code, etc.):
```
WastManagementSystem/config/database.php
```

You will see this near the top:
```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');          // <-- leave empty for XAMPP
define('DB_NAME', 'waste_management');
```

- **XAMPP / WAMP users** → leave `DB_PASS` empty `''`. Done. ✅
- **MAMP users** → change `DB_PASS` to `'root'`.
- **Custom MySQL** → put your MySQL password between the quotes.

Save the file.

---

## 🌱 Step 5 — Add sample data (one time only)

Open your browser and go to:
```
http://localhost/WastManagementSystem/install.php
```

You'll see a green box that says **"Installation ran successfully"**.
This adds 5 sample users so you can log in immediately.

⚠️  **For safety:** after this works, delete the `install.php` file.

---

## 🚀 Step 6 — Open the website and log in

Open your browser and go to:
```
http://localhost/WastManagementSystem/
```

You will see the landing page. Click **Login**.

Use any of these test accounts (password is the same for all):

| Type of user | Email                    | Password       |
|--------------|--------------------------|----------------|
| 👨‍💼 Admin     | `admin@wms.test`         | `Password@123` |
| 🚛 Collector | `collector@wms.test`     | `Password@123` |
| 👤 Resident  | `user@wms.test`          | `Password@123` |

🎉 **Done! The project is now running on your computer.**

---

## 🎯 What can each user do?

### 👤 Resident (regular user)
- View dashboard with charts
- Request waste pickup
- See list of their pickup requests
- File a complaint
- View personal reports

### 🚛 Collector
- See assigned pickup tasks
- Mark tasks as "Collected"
- View work history

### 👨‍💼 Admin
- Manage all users
- Assign collectors to pickups
- Reply to complaints
- Manage vehicles & recycling centers
- View charts and reports for everything

---

## ❓ Common Problems & Fixes

### Problem: "Database connection failed"
👉 You forgot Step 1 (start MySQL) **or** the password in `config/database.php` is wrong.

### Problem: "Page not found / 404"
👉 The folder name in the URL must match exactly.
The folder is `WastManagementSystem` (no space, capital W, capital M, capital S).

### Problem: Page shows raw PHP code instead of a webpage
👉 Apache is not running. Open XAMPP and click **Start** next to Apache.

### Problem: "Access denied for user 'root'@'localhost'"
👉 Your MySQL has a password. Open `config/database.php` and add it:
```php
define('DB_PASS', 'your_password_here');
```

### Problem: Login says "Invalid email or password"
👉 You skipped Step 5. Visit `http://localhost/WastManagementSystem/install.php` to seed the demo accounts.

### Problem: phpMyAdmin asks for username and password
👉 Username is `root`. Leave password empty (XAMPP/WAMP) or use `root` (MAMP).

---

## 📂 Project folder structure (for curious beginners)

```
WastManagementSystem/
│
├── index.php              ← Landing page (opens first)
├── login.php              ← Login page
├── register.php           ← Sign up page
├── logout.php             ← Logs the user out
├── install.php            ← One-time setup (delete after using)
│
├── user/                  ← Pages for residents
├── collector/             ← Pages for waste collectors
├── admin/                 ← Pages for admin
│
├── api/                   ← Backend code (handles form submissions)
├── includes/              ← Shared code (login, database helpers)
├── config/database.php    ← Database settings (edit this!)
├── database/schema.sql    ← Creates database tables
│
└── assets/
    ├── css/style.css      ← All the styling
    └── js/main.js         ← All the JavaScript
```

---

## 💡 Beginner Tips

- **Always start Apache + MySQL first** before opening the website.
- The website only works at `http://localhost/...`, not by double-clicking the `.php` files.
- To stop the project, just close XAMPP (click **Stop** for Apache and MySQL).
- To restart later, just open XAMPP and click **Start** again — your data stays saved.
- Want to reset everything? Re-import `schema.sql` and visit `install.php` again.

---

## 🆘 Still stuck?

1. Make sure **both Apache and MySQL** are green in XAMPP.
2. Make sure the folder is exactly inside `htdocs/`.
3. Try in a different browser (Chrome, Firefox, Edge).
4. Re-read the error message carefully — it usually tells you what's wrong.

Happy coding! 🌱

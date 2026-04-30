# ATP Manager 

A tennis tournament management system inspired by the ATP Tour built with PHP, MySQL, and Tailwind CSS.

---

## Folder Overview

| File / Folder | Purpose |
|---|---|
| `index.php` | Landing / home page |
| `login.php` | Player login |
| `signup.php` | New player registration |
| `logout.php` | Logs the user out |
| `404.php` | Custom "page not found" page |
| `database.sql` | Run this in phpMyAdmin to set up the database |
| `.htaccess` | Apache config (404 redirect, security) |
| `includes/db.php` | Database connection (edit your credentials here) |
| `includes/auth.php` | Login/session helper functions |
| `includes/header.php` | Top nav bar (included on every page) |
| `includes/footer.php` | Footer (included on every page) |
| `pages/dashboard.php` | Player home screen after login |
| `pages/tournaments.php` | Browse + register for tournaments |
| `pages/my-schedule.php` | Player's personal tournament calendar |
| `pages/rankings.php` | Full ATP rankings board |
| `pages/profile.php` | View + edit profile, change password |
| `admin/manage-tournaments.php` | Admin: add, edit, delete tournaments |


---

## Pages & Features

### Player Features
- **Dashboard** — Ranking, upcoming schedule, open tournaments
- **Tournaments** — Browse all 14 ATP season tournaments with filters (status, surface, category)
- **Register / Withdraw** — One-click tournament registration with slot tracking
- **My Schedule** — Personal calendar of registered tournaments
- **Rankings** — Full player leaderboard
- **Profile** — Edit name, country, and password

### Admin Features
- **Manage Tournaments** — Add, edit, delete any tournament
- Change tournament status (Upcoming → Open → Closed → Completed)
- Set player slot limits

---

## Security Features Implemented
- ✅ Passwords hashed with `password_hash()` (bcrypt)
- ✅ Prepared statements everywhere (no SQL injection)
- ✅ `htmlspecialchars()` on all output (no XSS)
- ✅ Session-based authentication
- ✅ Admin-only pages protected with `requireAdmin()`
- ✅ Basic CSRF mitigation using POST-only forms

---
## Setup Instructions

### Requirements
- XAMPP (Apache + MySQL)
- A web browser (Chrome, Firefox, etc.)

### Steps

1. **Install XAMPP**
   Download and install XAMPP. During installation make sure Apache and MySQL are selected.

2. **Copy the project files**
   Extract the project zip file. Copy the entire `atp-manager` folder into `C:\xampp\htdocs\atp-manager\` or wherever your XAMPP htdocs is located.

3. **Start XAMPP**
   Open the XAMPP Control Panel and click Start next to both Apache and MySQL. Both should turn green.

4. **Create the database**
   Open your browser and go to `http://localhost/phpmyadmin`. On the left sidebar click New, type `atp_manager` as the database name, and click Create.

5. **Import the database**
   With the `atp_manager` database selected, click the Import tab at the top. Click Choose File, navigate to the project folder, and select `database.sql`. Click Go. You should see a green success message.

6. **Configure the database connection**
   Open `includes/db.php` in a text editor and check these settings match your XAMPP setup:
```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'atp_manager');
   define('DB_USER', 'root');
   define('DB_PASS', '');       // leave empty for default XAMPP
   define('DB_PORT', '3307');   // change to 3306 if that is your MySQL port
```

7. **Open the application**
   Go to `http://localhost/atp-manager/` in your browser. The landing page should appear.

8. **Log in**
   Use any of the pre-loaded accounts to test the app. All accounts use the password: `password`. The admin account email is `admin@atpmanager.com`.

---
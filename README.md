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

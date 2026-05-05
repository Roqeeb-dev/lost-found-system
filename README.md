# 📍 Campus Lost & Found

Campus Lost & Found is a university-focused lost-and-found web application built with PHP, MySQL, and Tailwind CSS. It is designed to help students report lost items, post found items, and recover belongings through a verification workflow.

---

## 🚀 Current Status

This repository includes:

- `index.php`: marketing/home landing page
- `auth/login.php`: user login form and authentication logic
- `auth/register.php`: user signup form and registration logic
- `dashboard.php`: user dashboard UI with mock data
- `admin/dashboard.php`: admin dashboard UI with mock data
- `items/create.php`: item reporting form, image upload, and validation logic
- `config/db.php`: MySQL connection setup
- `templates/navbar.php` / `templates/footer.php`: common layout components

Parts still in progress or currently placeholder:

- `items/list.php`: browse items page (placeholder)
- `items/view.php`: item detail page (empty)
- `my-items.php`: user items page (placeholder)
- `admin/manage_claims.php`: admin claim review page (empty)
- `auth/logout.php`: logout handling (empty)
- `claims/` directory: no claim pages yet
- `items/create.php`: has a PDO usage bug and needs a consistent database layer

---

## 🛠️ Tech Stack

- Frontend: HTML, Tailwind CSS
- Backend: PHP
- Database: MySQL / MariaDB
- Image uploads: local `uploads/` directory

---

## 📂 Project Structure

```
/                   → Public entry points
/auth               → Authentication pages
/admin              → Admin dashboard pages
/claims             → Claim handling pages (empty)
/config             → Database configuration
/items              → Item reporting and item pages
/templates          → Shared navbar and footer
/uploads            → Uploaded item images
```

---

## ⚙️ Setup Instructions

1. Copy this repository into your XAMPP htdocs folder.
2. Start Apache and MySQL.
3. Create the project database:

```sql
CREATE DATABASE `lost-found-db`;
```

4. Create the required database tables.

### Suggested schema

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(100) NOT NULL,
  type ENUM('lost','found') NOT NULL,
  location VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  image_path VARCHAR(255),
  verification_question VARCHAR(255) NOT NULL,
  status ENUM('available','claimed','resolved') NOT NULL DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  user_id INT NOT NULL,
  answer TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES items(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

5. Update `config/db.php` with your connection credentials if needed.
6. Open the app at:

```
http://localhost/lost-found-system
```

---

## ✅ What Works Today

- User registration and login
- Landing page with product messaging
- Item reporting form with validation and image upload
- Basic dashboard UIs for user and admin
- Shared navbar and footer

---

## ⚠️ Known Issues

- `auth/logout.php` is empty; session logout is not implemented.
- `items/list.php` and `items/view.php` are not implemented.
- `my-items.php` is currently placeholder content.
- `admin/manage_claims.php` is empty.
- `claims/` folder has no pages yet.
- `items/create.php` uses `require_once '../config/db.php'` but then calls `$pdo->prepare(...)`; the config file currently provides `$conn` via mysqli.
- Dashboard pages use hardcoded mock data and do not fetch real database records.

---

## 📌 Recommended Next Steps

1. Standardize database access across the app (`mysqli` or `PDO`).
2. Implement logout functionality in `auth/logout.php`.
3. Build item browsing, filtering, and item detail pages.
4. Create claim submission and claim review flows.
5. Wire dashboard pages to real database data.
6. Harden security: prepared statements, session checks, input sanitization, and CSRF protection.

---

## 🎯 Future Improvements

- Add search and category filtering on item listings
- Add email confirmation or notification support
- Add pagination for items and claims
- Add roles and permissions for admin and user actions
- Add a dedicated admin item management page
- Add password recovery/reset flow

---

## 👤 Author

Built as a campus lost-and-found system for student communities.

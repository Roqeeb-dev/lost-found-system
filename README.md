# 📍 Campus Lost & Found

Campus Lost & Found is a university-focused lost-and-found web application built with PHP, MySQL, and Tailwind CSS. It is designed to help students report lost items, post found items, and recover belongings through a verification workflow.

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

## 👤 Author

Built as a campus lost-and-found system for student communities.

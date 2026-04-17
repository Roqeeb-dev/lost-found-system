# 📍 Campus Lost & Found

Campus Lost & Found is a web-based platform designed to help students easily report, find, and recover lost items within their campus community.

---

## 🚀 Overview

Losing personal belongings on campus can be frustrating. This platform simplifies the process by allowing users to:

- Report lost items
- Post found items
- Browse available items
- Submit claims to recover belongings

The system also includes basic verification and admin control to ensure secure and reliable item recovery.

---

## 🛠️ Tech Stack

- Frontend: HTML, Tailwind CSS
- Backend: PHP
- Database: MySQL

---

## ✨ Features

- 🔐 User authentication (Register & Login)
- 📦 Report lost or found items
- 🔎 Browse and search items
- 📬 Submit claims for items
- 👤 User dashboard
- 🛡️ Admin dashboard (manage users, items, and claims)
- 🔒 Secure password hashing

---

## 📂 Project Structure

```
/auth       → Authentication (login & register)
/admin      → Admin dashboard and controls
/items      → Item-related features
/claims     → Claim handling
/config     → Database configuration
/uploads    → Stored images/files
```

## ⚙️ Setup Instructions

1. Clone the repository:
   git clone https://github.com/Roqeeb-dev/lost-found-system.git

2. Move the project to your server directory (e.g. htdocs for XAMPP)

3. Create a MySQL database:
   CREATE DATABASE lost-found-db;

4. Import your tables or create required tables:
   - users
   - items
   - claims

5. Update your database connection in `/config`

6. Start your server and visit:
   http://localhost/lost-found-system

---

## 🔐 Default Roles

- User: Can report, browse, and claim items
- Admin: Can manage users, items, and claims

---

## 📌 Future Improvements

- Email notifications
- Advanced search & filters
- Real-time updates
- File/image optimization
- Enhanced verification system

---

## 👨‍💻 Author

Developed by Roqeeb as a school project

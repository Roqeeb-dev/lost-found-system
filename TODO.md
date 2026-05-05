# Campus L&F — TODO / Roadmap

## ✅ Already Done

- [x] Landing page (`index.html`) — hero, features, how it works, CTA, footer
- [x] `auth/register.php` — form, server-side validation, password_hash(), session, redirect
- [x] `auth/login.php` — form, credential check, role-based redirect, remember me stub
- [x] `dashboard.php` (student) — sidebar, stat cards, my items table, my claims table, quick actions
- [x] `admin/dashboard.php` — dark sidebar, pending claims table, recent items, platform overview, stat cards
- [x] `items/create.php` — full form, image upload with validation, DB insert, schema-aligned fields
- [x] `items/list.php` — grid layout, search, sidebar filters, mobile pills, active chips, pagination, mock fallback
- [x] `config/db.php` — PDO connection with error handling
- [x] Standardized on PDO across all pages
- [x] `password_hash()` on register, `password_verify()` on login
- [x] Server-side validation on register, login, and create item forms
- [x] File upload validation (type, size, unique filename, move_uploaded_file)
- [x] Prepared statements on register, login, create item, list page queries
- [x] Role-based session (`user` vs `admin`) set on login
- [x] Role guards on student dashboard and admin dashboard
- [x] Search and filter UI on items list (type, status, category, sort, keyword)
- [x] Flash message stub (`$_SESSION['flash']`) on create item

---

## 🔴 Priority 1 — Blockers (app cannot function without these)

- [ ] `auth/logout.php` — destroy session, delete remember cookie, redirect to `index.php`
- [ ] `items/view.php` — item detail page: full info, image, verification question form, claim button for logged-in users, edit/delete for item owner
- [ ] `claims/create.php` — receive item_id + answer, insert into `claims` table with status `pending`
- [ ] DB schema: create `claims` table

```sql
  CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_id INT NOT NULL,
    answer_given VARCHAR(255) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
  );
```

- [ ] Replace all mock data in student dashboard with real PDO queries
- [ ] Replace all mock data in admin dashboard with real PDO queries

---

## 🟠 Priority 2 — Core workflows (app is incomplete without these)

- [x] `claims.php` (student) — list user's own claims, status badges, link to item
- [x] `my-items.php` — list user's own posted items, edit and delete links
- [x] `items/edit.php` — pre-filled edit form, update DB row, restrict to item owner
- [x] `admin/claims.php` — full claims management: view question + given answer side by side, approve/reject buttons that update `claims.status` and `items.status`
- [x] `admin/items.php` — list all items, filter by status/type, edit, delete, mark resolved
- [x] Flash message display — read `$_SESSION['flash']` at top of dashboard/list pages and show a toast or banner, then unset it
- [x] Replace emojis in dashboards with standard icons/text

---

## 🟡 Priority 3 — Important but not blocking

- [ ] `admin/users.php` — list all users, view activity count, toggle role, deactivate account
- [ ] `profile.php` — view and update name/email, change password (verify current password first)
- [ ] `items/delete.php` — handle delete with ownership check, remove image file from disk
- [ ] `admin/settings.php` — basic admin config (site name, contact email, etc.)
- [ ] Role-aware navigation — show correct nav links based on `$_SESSION['user_role']` across all pages (e.g. hide "Report Item" for guests, show "Admin Panel" for admins)

---

## 🔵 Priority 4 — Security hardening

- [ ] CSRF tokens — generate token on each form load, verify on POST for all forms (register, login, create, edit, claim)
- [ ] Session timeout — if `$_SESSION['last_active']` is older than 30 minutes, destroy session and redirect to login
- [ ] Disable directory listing — add `Options -Indexes` to `.htaccess` in `uploads/` folder
- [ ] Restrict direct access to `uploads/` PHP execution — add `.htaccess` rule to deny `.php` files in uploads
- [ ] Input sanitization audit — review all `$_POST` reads and ensure `htmlspecialchars()` on output everywhere

---

## ⚪ Priority 5 — Polish & UX

- [ ] `auth/forgot_password.php` — password reset via email token (requires mail setup)
- [ ] Mobile navigation — hamburger menu for sidebar on small screens on both dashboards
- [ ] Responsive tables — horizontal scroll or card-stack on mobile for claims/items tables
- [ ] Empty states — meaningful empty state UI on `my-items.php`, `claims.php`, `admin/claims.php`
- [ ] Image lightbox on `items/view.php` — click image to view full size
- [ ] Confirmation dialog before delete actions (JS `confirm()` or a modal)
- [ ] Pagination on `my-items.php`, `claims.php`, and admin pages

---

## ⬜ Priority 6 — Testing & Deployment

- [ ] End-to-end test: Register → Login → Report item → Browse → Claim → Admin approves → Resolved
- [ ] Test image upload edge cases (too large, wrong type, no file)
- [ ] Test all filter/search combinations on `list.php`
- [ ] Test admin claim approval updates both `claims.status` and `items.status` correctly
- [ ] Seed file — create `database/seed.sql` with sample users, items, claims for demo/testing
- [ ] `README.md` — document folder structure, DB setup steps, how to run locally

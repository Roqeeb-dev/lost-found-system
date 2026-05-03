# TODO / Roadmap

## 1. Core features to implement

- [ ] Implement `auth/logout.php` to destroy the session and redirect to `index.php`.
- [ ] Standardize database access by choosing one driver:
  - Either convert `config/db.php` to provide a PDO object (`$pdo`) and update all pages
  - Or convert `items/create.php` to use the current mysqli connection (`$conn`).
- [ ] Build `items/list.php` so users can browse lost/found items.
- [ ] Build `items/view.php` to show item details and a claim button.
- [ ] Build `my-items.php` so users can view, edit, and manage their own item reports.
- [ ] Build claim submission flow and store claims in `claims` table.
- [ ] Build `admin/manage_claims.php` with claim approval and rejection.
- [ ] Add an admin item management page such as `admin/items.php` (list, edit, delete).
- [ ] Add an admin user management page such as `admin/users.php`.

## 2. Pages to create or complete

- [ ] `claims/submit.php` or `claims/create.php` — submit a claim for an item.
- [ ] `claims/list.php` — list user's claims and status.
- [ ] `items/edit.php` — allow users to update item details.
- [ ] `auth/forgot_password.php` — password recovery.
- [ ] `admin/settings.php` — admin config or dashboard settings.
- [ ] `profile.php` — add profile editing and password update.
- [ ] `admin/claims.php` — admin claim management and detail review.
- [ ] `admin/items.php` — admin item moderation.

## 3. Workflows to implement

- User onboarding: Register → Login → Dashboard.
- Item reporting: Report lost/found item → image upload → save to database.
- Browsing items: Search, filter by type/category, and view item details.
- Claim flow: User submits claim → admin reviews → approve/reject.
- Verification flow: item owner verification question answered before claim approval.
- Admin management: view pending claims, approve/reject, update item status.
- User account flow: view profile, update information, logout.

## 4. Database and backend tasks

- [ ] Create and seed the `users`, `items`, and `claims` tables.
- [ ] Add `role` field for admin/user separation.
- [ ] Add `status` field to `items` and `claims` to track progress.
- [ ] Add foreign key relations between `items.user_id`, `claims.item_id`, and `claims.user_id`.
- [ ] Replace mock dashboard values with real database queries.
- [ ] Add file upload validation and safe file handling.
- [ ] Add server-side validation to all form submissions.

## 5. Security and quality improvements

- [ ] Use prepared statements for every database query.
- [ ] Validate and sanitize all user input.
- [ ] Add CSRF protection for forms.
- [ ] Add session timeout or login expiry handling.
- [ ] Hash passwords securely using `password_hash()`.
- [ ] Disable directory listing on the web server for `uploads/`.

## 6. UX and polish

- [ ] Add flash messages for form success/error.
- [ ] Add search and filter UI for item listings.
- [ ] Improve landing page CTA flows for register/login.
- [ ] Add real user role-aware navigation links.
- [ ] Add accessible mobile navigation and responsive tables.

## 7. Testing and deployment

- [ ] Run the system in XAMPP and verify login/register flows.
- [ ] Test item upload and browse flows end to end.
- [ ] Test admin claim approval and user notifications.
- [ ] Document database setup and seed data in `README.md`.

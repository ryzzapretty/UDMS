# University Document Management System (UDMS)
PHP 8+ · MySQL/MariaDB · Bootstrap 5 · SweetAlert2 (loaded from CDN, so the browser needs internet access)

## Install (XAMPP / WAMP / LAMP)
1. Copy the `udms` folder into `htdocs` (or your web root).
2. Start Apache and MySQL. Edit DB settings at the top of `config.php` if needed.
3. Open `http://localhost/udms/setup.php` – creates the database, tables, sample data and demo accounts.
4. **Delete `setup.php`**, then open `http://localhost/udms/`.

| Role | Username | Password |
|---|---|---|
| Admin | admin | Admin@123 |
| Faculty | faculty1 | Faculty@123 |
| Student | student1 | Student@123 |

Change these passwords after first login. Requires PHP extensions: pdo_mysql, fileinfo, mbstring.

## Rules worth knowing
* New sign-ups are **Pending** until an Admin activates them (Users page).
* Only Admins review (approve / reject / request revision). Approved = stored in the repository.
* Visibility: owner, Admin and people a document was shared with; other users see **Approved** documents only, per access level (Public, Faculty Only, Department, Private). Enforced in SQL and in `can_view()`.
* Files live in `uploads/` with random names and are served only through `download.php` after a permission check. Keep the `.htaccess` there (Apache). On Nginx, deny direct access to `/uploads/`, or move `UPLOAD_DIR` outside the web root.
* Forgot password notifies Admins, who set a new password in User Management (no mail server needed).

# DriveSphere – Setup & Testing Guide (XAMPP)

## 1. Install
1. Ensure this project folder is inside `C:\xampp\htdocs\`. Note: `BASE_URL` is automatically and dynamically detected by `includes/config.php` based on your project folder name and host, so it works out of the box under `drivesphere_updated`, `drivesphere`, or any other directory name.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) → **Import** → choose `sql/drivesphere.sql` → Go.
   * Works on a brand-new database **and** on your existing `drivesphere` database (nothing is deleted).
4. If your MySQL root user has a password, edit `DB_PASS` in `includes/config.php`.

## 2. URLs (for folder `drivesphere_updated`)
| Page | URL |
|---|---|
| Public website | http://localhost/drivesphere_updated/ |
| Customer Login | http://localhost/drivesphere_updated/login.php |
| Customer Registration | http://localhost/drivesphere_updated/register.php |
| Customer Dashboard | http://localhost/drivesphere_updated/customer/dashboard.php |
| **Admin Login (hidden, not linked anywhere)** | http://localhost/drivesphere_updated/admin/login.php |
| **Admin Registration (needs setup key)** | http://localhost/drivesphere_updated/admin/register.php |
| Admin Dashboard | http://localhost/drivesphere_updated/admin/dashboard.php |

## 3. Demo accounts (password for all: `password123` – change after the demo)
* Customer: `carlos@drivesphere.ph`  (also `maria@drivesphere.ph`, `eduardo@drivesphere.ph`)
* Admin: `admin@drivesphere.ph`
* Admin setup key (for new admin registration): `DRIVESPHERE-ADMIN-2026` → change `ADMIN_SETUP_KEY` in `includes/config.php`.

## 4. How authentication works
* One `users` table with a `role` column (`customer` / `admin`). Passwords use `password_hash()` / `password_verify()`.
* Customer login (`actions/login_action.php`) only accepts `role = customer`. Admin login (`admin/login.php`) only accepts `role = admin`.
* Registration on the public site always creates a **customer**. Admin accounts can only be created at
  `admin/register.php` with the setup key.
* Every customer page includes `includes/customer_header.php` → `require_customer()`.
  Every admin page includes `includes/admin_header.php` → `require_admin()`; the admin action endpoint
  (`admin/action.php`) checks the role again. Customers/guests are sent to a login page.
* Customers only ever load bookings with `WHERE user_id = <their id>`.
* All SQL uses PDO prepared statements; every POST form has a CSRF token; `session_regenerate_id()` on login;
  logout destroys the session (`actions/logout.php`, `admin/logout.php`).
* There are no admin links anywhere on the public website or the customer dashboard.

## 5. Rental flow
Customer: Dashboard/Browse → Vehicle → Price Overview → **Request to Book** (status *Pending*)
→ Admin **Requests** → Approve (*Reserved*) → Rentals → **Dispatch** (*En Route*)
→ **Record Return** (*Returned*) → **Damage & Final Charges** → Confirm Settlement (*Completed*).
Customers get a notification at each step; they can cancel while Pending/Reserved.

## 6. Test checklist
1. Import SQL, open the public site, click the avatar menu → Log In / Register.
2. Register a new customer → you land on the Customer Dashboard. Log out → you return to the public site.
3. Log in as `carlos@…` → open Browse, a vehicle, pick dates, **Request to Book** → see the receipt.
4. Open `admin/login.php`, try `carlos@…` → rejected. Log in as `admin@…` → Admin Dashboard.
5. Requests → Approve Carlos' request → Rentals → Dispatch → Record Return → Settle.
6. Log in as customer again → Notifications and Rental History show every step.
7. While logged in as a customer, open `http://localhost/drivesphere_updated/admin/dashboard.php` → redirected to admin login.
8. Log out, open `customer/dashboard.php` → redirected to customer login.
9. Resize the browser / use phone view: menus collapse into the ☰ button.

## 7. Known limitations
* Not tested on a live server by the author of this change set – run the checklist above after importing.
* Not implemented (shown in the design board but out of project scope): profile photo upload, two-factor
  authentication, SMS/push alerts, online payment, QR gate pass, live GPS telemetry, 360° inspection map,
  photo upload of damage evidence, calendar widget.
* The public Fleet / About / Help / Policies pages keep their original static content (only navbar, footer and links changed).
* Vehicle images use external image URLs (as in the original project) so they need internet access.

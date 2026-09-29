# kinMel: Digital Shopping Platform

A BCA 4th semester project — web-based e-commerce platform built with **HTML, CSS, JavaScript, PHP and MySQL**, matching the ER diagram, DFDs, and modules described in the project documentation.

## Features
- Customer registration & login (bcrypt password hashing, role-based access)
- Product browsing by category, keyword search
- Shopping cart (add / update quantity / remove) via AJAX
- Wishlist
- Checkout with stock validation, order creation, and stock deduction
- Order history and order status tracking
- Admin dashboard: manage products (CRUD), categories (CRUD), orders (status updates), users

## Setup (XAMPP)
1. Copy the `kinmel` folder into `C:\xampp\htdocs\` (or `/opt/lampp/htdocs/` on Linux).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open `http://localhost/phpmyadmin`, create nothing manually — instead go to the **Import** tab and import `database.sql`. This creates the `kinmel` database, all tables, and sample data.
4. Visit `http://localhost/kinmel/index.php` in your browser.
5. Log in as admin using:
   - Email: `admin@kinmel.com`
   - Password: `admin123`
6. Register a new account to test the customer side.

## Folder Structure
```
kinmel/
├── admin/              Admin panel (dashboard, products, categories, orders, users)
├── css/style.css        Stylesheet
├── js/                  validation.js (form checks), cart.js (AJAX cart/wishlist)
├── includes/            db.php, functions.php, header.php, footer.php, admin_header/footer.php
├── uploads/              Product images uploaded through the admin panel
├── index.php             Homepage / product listing & search
├── product.php           Single product page
├── register.php / login.php / logout.php
├── cart.php, add_to_cart.php, update_cart.php, remove_from_cart.php
├── wishlist.php, add_to_wishlist.php, remove_from_wishlist.php
├── checkout.php, order_success.php
├── my_orders.php, track_order.php
└── database.sql          Full schema + sample data
```

## Notes
- Passwords are stored using PHP's `password_hash()` (bcrypt).
- All SQL queries use prepared statements to prevent SQL injection.
- Sessions enforce role-based access — customers cannot reach `/admin`, and admins are redirected there after login.
- This matches the three-tier architecture (Presentation / Application / Database) and Waterfall-based modules described in Chapter 3 and Chapter 4 of the project report.

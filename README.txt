=========================================================
KINMEL E-COMMERCE MANAGEMENT SYSTEM
=========================================================

WHAT'S INCLUDED NOW
---------------------------
- Loading splash screen (0% -> 100%) with "Developed by: Sobit Shrestha & Pukar Gurung"
- Separate Customer Login and Admin Login tabs on one login page
- Customer: register, login, logout, browse/search/filter products,
  add to cart, update quantity, remove from cart, checkout, view orders,
  edit profile, change password
- Admin: dashboard with stats (Total Products, Categories, Users, Orders,
  Sales) + recent orders, full Products CRUD with image upload, full
  Categories CRUD, Orders management (change status), Users list,
  Admin profile with change password
- Every CRUD action writes to MySQL - verify any change in phpMyAdmin

=========================================================
XAMPP SETUP INSTRUCTIONS
=========================================================

1. Start Apache and MySQL from the XAMPP Control Panel.

2. Copy the "kinmel" folder into htdocs:
   Windows:  C:\xampp\htdocs\kinmel
   Mac:      /Applications/XAMPP/htdocs/kinmel

3. Import the database:
   - Go to http://localhost/phpmyadmin
   - Click "Import" -> Choose File -> select kinmel/sql/kinmel_db.sql -> Go
   (Skip this step if you already imported it before - no schema changes
   were made in this update.)

4. Make sure kinmel/uploads/products/ is writable (it is by default on
   XAMPP/Windows/Mac). This is where uploaded product images are saved.

5. Visit: http://localhost/kinmel/

=========================================================
HOW THE SITE FLOWS NOW
=========================================================

1. http://localhost/kinmel/  -> loading splash screen, then auto-redirects
   to the login page.
2. Login page has two tabs: "Customer Login" and "Admin Login".
   - Customer demo: register your own account via the Register link.
   - Admin demo: admin@kinmel.com / admin123 (use the Admin tab)
3. After logging in:
   - Customers land on a simple Home page with a "Browse Products" button,
     and can use the nav bar: Home, Products, Categories, Cart, My Orders,
     Profile, Logout.
   - Admins land on the Admin Dashboard with a sidebar: Dashboard, Products,
     Categories, Orders, Users, Profile, Logout.

=========================================================
TESTING CHECKLIST
=========================================================

CUSTOMER SIDE
- Register a new account, then login using the Customer tab.
- Go to Products, search/filter by category, Add to Cart a couple of items.
- Go to Cart, increase/decrease quantity, remove an item, then Checkout.
- Go to My Orders - your new order should appear with status "Pending".
- Go to Profile - update your details, then try Change Password.
- Logout.

ADMIN SIDE
- Login using admin@kinmel.com / admin123 on the Admin tab.
- Dashboard should show real numbers matching your database.
- Go to Categories - add a new category, edit it, then delete a test one.
- Go to Products - add a new product with an image, edit an existing
  product's price/stock, delete a product. Check phpMyAdmin's products
  table to confirm the row actually changed.
- Go to Orders - change a customer's order status (e.g. Pending ->
  Processing) and confirm the badge color/text updates.
- Go to Users - confirm your registered customers appear here.
- Go to Profile - try Change Password.
- Logout.

WRONG-TAB TEST
- Try logging into the Admin tab using a customer account's email/password
  - you should see "This account is not an admin account."
- Try logging into the Customer tab using the admin account - you should
  see "This is an admin account. Please use the Admin tab."

=========================================================
NOTES
=========================================================
- Passwords are stored using password_hash() (bcrypt) and verified with
  password_verify() - never stored in plain text.
- All queries use prepared statements (mysqli bind_param) to prevent SQL
  injection.
- All displayed data is escaped with htmlspecialchars() via the clean()
  helper to prevent XSS.
- Product images are validated for file type (JPG/PNG/GIF/WEBP) and size
  (max 2MB) before being saved to kinmel/uploads/products/.
- Admin pages are protected by requireAdmin() - a logged-out user or a
  logged-in customer trying to open /admin/ pages is redirected to login.

Sample admin login: admin@kinmel.com / admin123

IF YOU IMPORTED AN OLDER VERSION OF kinmel_db.sql BEFORE:
The sample admin password hash in the older file was wrong. Fix it without
re-importing by running this in phpMyAdmin (SQL tab, database kinmel_db):

UPDATE users SET password = '$2y$10$72B1EgSqRjH7JCKYDRMJ0e6NAQRApql2KEp7ikHIpGC898S1qaS2m'
WHERE email = 'admin@kinmel.com';

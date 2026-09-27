# Shop Management System

A web-based shop management system built with PHP and MySQL.

## Overview

This project is a web-based shop management application for managing day-to-day store operations. It helps administrators maintain product inventory, record sales, keep employee records, and manage salary entries in a single dashboard-driven system. The application is designed around a simple admin workflow with secure session-based access and a local MySQL database.

## Current Features

### Admin Authentication

- Admin login
- Session-based authentication
- Logout
- Login validation
- Invalid credentials error handling

### Dashboard

- Dynamic product count
- Dynamic sales count
- Employee count
- Salary record count
- Sales overview for the last seven days
- Recent sales summary
- Quick actions for common tasks
- Admin profile information
- Total sales amount and average sale calculation

### Product Management

- Add products
- View products
- Edit products
- Delete products
- Restock products
- Product search
- Category filtering
- Price filtering
- Stock-status filtering
- Stock status values:
  - Available
  - Low Stock
  - Out of Stock

### Sales Management

- Add sales
- View sales
- Product selection
- Quantity validation
- Stock availability checking
- Automatic total calculation
- Automatic stock reduction after sale
- Sales search and filtering
- Product information display
- Transaction handling using database transaction logic during sale entry

### Employee Management

- Add employees
- View employees
- Edit employees
- Delete employees
- Employee search
- Position filtering
- Joining-date filtering
- Protection against deleting employees with related salary records

### Salary Management

- Add salary records
- View salary records
- Salary calculation
- Basic salary entry
- Bonus entry
- Deduction entry
- Net salary calculation
- Salary filtering by employee, month, and amount range

## User Role / Access

### Admin

The current system is designed for a single administrator. The admin can:

- Access the dashboard
- Manage products
- Record and review sales
- Manage employees
- Manage salary records
- Log out securely through the session system

There is no separate staff or cashier role implemented in the current project.

## Technologies Used

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- XAMPP
- phpMyAdmin
- Font Awesome

## Architecture

This project follows a straightforward server-side architecture:

- PHP handles the application logic, form validation, database queries, login checks, and session handling.
- MySQL stores the application data such as products, sales, employees, and salary records.
- HTML and CSS provide the user interface and dashboard layout.
- JavaScript is used for small client-side interactions such as password visibility toggling and short success-message hiding.
- XAMPP provides the local Apache and MySQL environment used to run the application.
- The shared database connection is set in `db.php`.
- Sessions are used to manage the admin login state and restrict access to protected pages.

This is not an MVC project; it is a PHP application with page-based modules and shared database access.

## Project Structure

```text
shop_management/
├── css/
│   └── style.css
├── Database-Backup/
│   └── shop_management.sql
├── employees/
│   ├── add.php
│   ├── delete.php
│   ├── edit.php
│   └── view.php
├── products/
│   ├── add.php
│   ├── delete.php
│   ├── edit.php
│   ├── restock.php
│   └── view.php
├── salary/
│   ├── add.php
│   └── view.php
├── sales/
│   ├── add.php
│   └── view.php
├── dashboard.php
├── db.php
├── index.php
├── logout.php
└── README.md
```

### Important files and folders

- `dashboard.php`: main dashboard with counts, sales overview, quick actions, and recent sales.
- `db.php`: database connection file used across the application.
- `index.php`: admin login page.
- `logout.php`: clears the session and logs out the admin.
- `products/`: product creation, viewing, editing, deleting, and restocking pages.
- `sales/`: sales recording and sales history pages.
- `employees/`: employee management pages.
- `salary/`: salary record addition and viewing pages.
- `Database-Backup/`: SQL backup of the project database.

## Application Workflows

### Admin Login Workflow

1. The admin opens the login page.
2. The admin enters a username and password.
3. PHP validates the entered credentials against the `admin` table.
4. If the credentials match, a session is created and the user is redirected to the dashboard.
5. If the credentials are invalid, an error message is shown.
6. The session is checked on each protected page before access is allowed.

### Product Workflow

The product workflow follows this sequence:

1. Add a new product
2. View the product list with filters
3. Edit product information
4. Restock inventory quantity
5. Delete a product only if no sales records exist for it

Stock quantity is increased during restocking and reduced automatically when a sale is recorded.

### Sales Workflow

1. Select a product.
2. Enter the quantity sold.
3. Validate that the quantity is a whole number greater than zero.
4. Check stock availability before saving the sale.
5. Calculate the total amount automatically.
6. Insert the sale record.
7. Reduce the product stock quantity.
8. Commit the transaction so the sale and stock update remain consistent.

### Employee Workflow

The employee workflow follows this sequence:

1. Add an employee
2. View employee records with filters
3. Edit employee information
4. Delete an employee

Employee deletion is blocked if salary records exist for that employee.

### Salary Workflow

1. Select an employee.
2. Enter the salary month.
3. Provide basic salary, bonus, and deduction values.
4. The system calculates the net salary automatically.
5. Save the salary record with the payment date.

## Database

The application uses the `shop_management` database.

### Tables

#### `admin`

Purpose: stores the administrator login credentials.

Columns:

- `id` - integer primary key
- `username` - admin username
- `password` - admin password stored in plain text

#### `products`

Purpose: stores product details and stock quantity.

Columns:

- `id` - integer primary key
- `product_name` - product name
- `category` - product category
- `price` - product price
- `quantity` - current stock quantity

#### `sales`

Purpose: stores completed sales records.

Columns:

- `id` - integer primary key
- `product_id` - product reference
- `quantity` - sold quantity
- `total_amount` - total amount for the sale
- `sale_date` - timestamp of sale creation

#### `employees`

Purpose: stores staff information.

Columns:

- `id` - integer primary key
- `name` - employee name
- `phone` - employee phone number
- `email` - employee email address
- `position` - employee role or position
- `joining_date` - employee join date

#### `salary`

Purpose: stores employee salary entries.

Columns:

- `id` - integer primary key
- `employee_id` - employee reference
- `salary_month` - month of the salary record
- `basic_salary` - basic monthly salary
- `bonus` - bonus amount
- `deduction` - deduction amount
- `net_salary` - calculated payable salary
- `payment_date` - date of salary payment

### Important Relationships

- `sales.product_id` references `products.id`
- `salary.employee_id` references `employees.id`

The application checks related records before deleting products and employees to prevent orphaned or inconsistent data.

## Database Relationship Overview

```text
admin
  |
  | authentication
  v
dashboard

products
  |
  | product_id
  v
sales

employees
  |
  | employee_id
  v
salary
```

## Installation and Setup

### Prerequisites

- XAMPP
- Apache
- MySQL
- PHP
- Web browser
- phpMyAdmin

### Installation Steps

1. Install XAMPP on your system.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Copy the project folder into `C:\xampp\htdocs\`.
4. Open phpMyAdmin in the browser.
5. Create a database named `shop_management`.
6. Import the SQL file from `Database-Backup/shop_management.sql`.
7. Verify the database connection inside `db.php`.
8. Open the application in a browser using the project URL.

The project is intended to run locally with the default XAMPP setup.

Example local access URL:

```text
http://localhost/shop_management/
```

## Configuration

The database configuration in `db.php` is as follows:

```php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "shop_management"
);
```

This configuration is for local development using XAMPP and the default MySQL setup. The database name is `shop_management`, the host is `localhost`, the MySQL user is `root`, and the password is empty.

## Admin Login

### Demo Login

Username:

```text
admin
```

Password:

```text
admin123
```

These are development/demo login credentials provided in the database.

## Security

The project includes the following implemented security features:

- Session-based authentication for protected pages
- Login validation against the `admin` table
- Prepared statements for SQL queries
- Input validation for forms and numeric fields
- Access checks before allowing dashboard and management pages
- Delete protection when related sales or salary records exist

### Current Security Limitations

The current application has some limitations:

- Passwords are stored as plain text in the `admin` table.
- There is no password hashing or password encryption.
- The application uses a single admin account rather than role-based access control.
- There is no production-ready security hardening or deployment configuration.

## Testing

This project does not include automated tests.

The application can be manually tested through the following flows:

- Login
- Product CRUD operations
- Product restocking
- Sales entry and validation
- Employee CRUD operations
- Salary management
- Search and filter functions
- Logout

## Known Limitations

- The application is intended for a local XAMPP environment.
- Admin passwords are stored as plain text.
- There is no external payment gateway integration.
- There is no advanced reporting or analytics module.
- There are no automated tests.
- There is no production deployment configuration.

## Future Improvements

The following are future improvements and are not currently part of this project:

- Password hashing and stronger authentication
- Role-based staff accounts
- Invoice and receipt generation
- Advanced sales reports and charts
- Inventory alert notifications
- Better dashboard analytics
- Database backup and restore tools
- Audit logs and change tracking
- Cloud deployment configuration

## Contributing

This project is suitable for a student or personal learning project. If you want to improve it, you can:

- Fork the repository
- Create a feature branch
- Make your changes
- Test the workflows locally
- Submit a pull request with a clear description of the update

## License

No license has currently been specified.

## Author

**Jithu Biju**
BCA Graduate
Project Author & Developer

## Connect

- Portfolio: https://jithu-portfolio-alpha.vercel.app/
- GitHub: https://github.com/jithu378
- LinkedIn: https://www.linkedin.com/in/jithu-biju-/

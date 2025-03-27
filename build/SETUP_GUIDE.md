# Setup Guide for E-commerce Web Application

This guide provides detailed steps to set up and run the e-commerce web application for CS306 Project Phase II.

## Prerequisites

- Web server with PHP support (Apache or Nginx)
- MySQL Database Server (5.7+ or MariaDB 10.2+)
- PHP 7.4 or higher
- Basic knowledge of database administration

## Step 1: Database Setup

1. Create a new database named `306_project`:
   ```sql
   CREATE DATABASE 306_project;
   ```

2. Import the database schema:
   ```bash
   mysql -u username -p 306_project < 306_project.sql
   ```

3. Import triggers, stored procedures, and sample data:
   ```bash
   # Import triggers
   mysql -u username -p 306_project < scripts/triggers/trigger_1/trigger_1.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_2/trigger_2.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_3/trigger_3.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_4/trigger_4.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_5/trigger_5.sql
   
   # Import stored procedures
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_1.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_2.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_3.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_4.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_5.sql
   
   # Import sample data (optional but recommended for testing)
   mysql -u username -p 306_project < sample_data.sql
   ```

## Step 2: Web Server Configuration

1. Copy all files from the `build` directory to your web server's document root or a subdirectory:
   ```bash
   # For Apache (XAMPP, WAMP, MAMP)
   cp -r build/* /path/to/htdocs/306-project/
   
   # For Nginx
   cp -r build/* /path/to/www/306-project/
   ```

2. Edit the database connection settings in `db_connection.php`:
   ```php
   $servername = "localhost"; // or your MySQL server address
   $username = "your_username";
   $password = "your_password";
   $dbname = "306_project";
   ```

3. Set appropriate permissions for the web server to read and execute PHP files:
   ```bash
   chmod -R 755 /path/to/web/directory
   ```

## Step 3: Access the Application

1. Open a web browser and navigate to the application URL:
   ```
   http://localhost/306-project/
   ```
   or if using a virtual host:
   ```
   http://your-domain.com/
   ```

2. You should see the home page with five module cards:
   - Order Placement
   - Wishlist Manager
   - Track Shipment
   - Review Submission
   - My Order History

## Step 4: Test the Application

1. **Testing User Login/Registration**
   - Navigate to the User Login module
   - Try registering a new user or logging in with sample data:
     - Email: alice@example.com
     - Password: password123

2. **Testing Order Placement**
   - Select a user (e.g., Alice Johnson)
   - The system will place an order using items in the user's cart
   - View the confirmation and recent orders list

3. **Testing Wishlist Management**
   - Select a user and product
   - Add the product to the wishlist
   - View the updated wishlist items

4. **Testing Order Tracking**
   - Enter an order ID (e.g., 2)
   - View shipment details and order items

5. **Testing Review Submission**
   - Select a user who has purchased a product
   - Select the product, rating, and enter a comment
   - Submit the review and see the confirmation

6. **Testing Order History**
   - Select a user
   - Optionally enter date range filters
   - View order history with summary statistics

## Troubleshooting

- **Database Connection Issues**
  - Verify your database credentials in `db_connection.php`
  - Ensure MySQL server is running and accessible

- **Missing Dependencies**
  - Ensure PHP is installed and properly configured
  - Check for required PHP extensions (mysqli)

- **Permission Issues**
  - Verify web server has proper permissions to access files
  - Check web server error logs for details

- **Stored Procedure Errors**
  - Ensure all stored procedures are properly imported
  - Check MySQL error log for details on procedure execution issues

## Example Users for Testing

| Name           | Email               | Password        |
|----------------|---------------------|-----------------|
| Alice Johnson  | alice@example.com   | password123     |
| Bob Smith      | bob@example.com     | securepass      |
| Charlie Davis  | charlie@example.com | charliepass     |
| Diana Martinez | diana@example.com   | dianasecret     |
| Ethan Brown    | ethan@example.com   | ethanpass       | 
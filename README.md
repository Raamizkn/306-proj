# CS306 E-Commerce Database Project

This project implements an e-commerce platform with a MySQL database backend and web access modules for various database operations.

## Project Structure

- **HTML Files**: User interface for web access modules
  - `index.html`: Main landing page with links to all modules
  - `order_placement.html`: For creating new orders
  - `track_shipment.html`: For tracking order shipments
  - `order_status.html`: For checking order status
  - `product_review.html`: For submitting product reviews
  - `wishlist_manager.html`: For managing wishlist items

- **PHP Connectors**: Handle API requests from HTML to database
  - `connect_order_placement.php`: API for order placement
  - `connect_track_shipment.php`: API for shipment tracking
  - `connect_order_status.php`: API for order status
  - `connect_product_review.php`: API for product reviews
  - `connect_wishlist_manager.php`: API for wishlist management

- **Database Scripts**:
  - `scripts/stored_procedures/`: Contains all stored procedures
  - `scripts/triggers/`: Contains database triggers
  - `build/db_connection.php`: Database connection configuration
  - `build/sample_data.sql`: Sample data to populate your database

- **Backend PHP Files** (in `build/` directory):
  - Full-featured PHP versions of the web modules
  - These can be used as alternatives to the HTML+connector approach

## Setup Instructions

### 1. Database Setup

1. Create a MySQL database named `306_project`:
   ```sql
   CREATE DATABASE 306_project;
   ```

2. Update database connection parameters in `build/db_connection.php`:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "your_mysql_password"; // Change this to your MySQL password
   $dbname = "306_project";
   ```

3. Import the database schema and sample data:
   ```bash
   mysql -u root -p 306_project < build/sample_data.sql
   ```

4. Import stored procedures:
   ```bash
   for file in scripts/stored_procedures/*.sql; do
     mysql -u root -p 306_project < "$file"
   done
   ```

5. Import triggers:
   ```bash
   for dir in scripts/triggers/*; do
     if [ -d "$dir" ]; then
       for file in "$dir"/*.sql; do
         mysql -u root -p 306_project < "$file"
       done
     fi
   done
   ```

### 2. Web Server Setup

1. Place all files in your web server directory (e.g., Apache's `htdocs` or `www` folder)

2. Make sure PHP is enabled in your web server

3. Start your web server (Apache, XAMPP, etc.)

4. Access the project by navigating to:
   ```
   http://localhost/[your-project-folder]/index.html
   ```

## Using the Web Modules

1. Start with the main page at `index.html`
2. Click on any module card to access that specific functionality
3. Each module provides:
   - An input form for data entry
   - A confirmation message showing the stored procedure call
   - A data view displaying the results from the database

## Alternative Implementation

If you prefer a more comprehensive PHP implementation, you can use the files in the `build/` directory directly:

```
http://localhost/[your-project-folder]/build/index.php
```

## Technical Details

- The project implements 5 stored procedures:
  1. `PlaceOrder`: Creates a new order for a user
  2. `TrackOrder`: Tracks shipment status for an order
  3. `OrderStatus`: Retrieves detailed order status
  4. `AddReview`: Adds a product review
  5. `AddToWishlist`: Adds a product to a user's wishlist

- Each stored procedure is called by its corresponding web module
- Triggers are implemented to handle various business rules
- The system includes proper error handling and input validation

## Screenshots

Screenshots of the application can be found in the `build/screenshots/` directory.

## Project Requirements

This project fulfills the CS306 requirements by implementing:

1. Database design with proper relationships and normalization
2. Stored procedures for data manipulation
3. Triggers for business rule enforcement
4. Web access modules for user interaction
5. Proper documentation and screenshots

## Troubleshooting

If you encounter any issues:

1. Check database connection parameters in `build/db_connection.php`
2. Ensure MySQL server is running
3. Verify that all stored procedures were imported correctly
4. Check web server logs for PHP errors
5. Make sure all file permissions are set correctly 
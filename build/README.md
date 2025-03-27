# E-commerce Web Module

This is a web application for an e-commerce system designed for CS306 Project Phase II, focusing on database triggers, stored procedures, and web access modules.

## Setup Instructions

### Database Setup

1. Import the database schema using the SQL file provided in the root directory:
   ```
   mysql -u username -p 306_project < 306_project.sql
   ```

2. Import triggers:
   ```
   mysql -u username -p 306_project < scripts/triggers/trigger_1/trigger_1.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_2/trigger_2.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_3/trigger_3.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_4/trigger_4.sql
   mysql -u username -p 306_project < scripts/triggers/trigger_5/trigger_5.sql
   ```

3. Import stored procedures:
   ```
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_1.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_2.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_3.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_4.sql
   mysql -u username -p 306_project < scripts/stored_procedures/stored_procedure_5.sql
   ```

4. Update database connection details:
   - Edit the `db_connection.php` file with your database credentials.

### Web Server Setup

1. Place the entire `build` directory on your web server.
2. Make sure PHP is enabled on your server (PHP 7.4+ recommended).
3. Ensure the web server has appropriate permissions to read and execute the PHP files.

### Running the Application

1. Access the application by navigating to the URL where you've hosted the files.
   - Example: `http://localhost/build/` or `http://your-domain.com/build/`
2. From the homepage, you can access the five web modules:
   - Order Placement
   - Wishlist Manager
   - Track Shipment
   - Review Submission
   - My Order History

## Web Modules

### 1. Order Placement
- Allows users to create an order from cart items
- Calls `PlaceOrder` stored procedure
- Displays recent orders with details

### 2. Wishlist Manager
- Enables users to add products to their wishlist
- Calls `AddToWishlist` stored procedure
- Shows all wishlist items with subtotal

### 3. Track Shipment
- Allows tracking of shipment status for a specific order
- Calls `TrackOrder` stored procedure
- Displays shipping details and order items

### 4. Review Submission
- Lets users submit product reviews with ratings
- Calls `WriteReview` stored procedure
- Shows the submitted review with star rating

### 5. My Order History
- Displays a user's order history with filtering by date range
- Calls `GetUserOrderHistory` stored procedure
- Shows summary statistics and category breakdown

## Database Triggers

The application utilizes the following database triggers:

1. `update_stock_after_order` - Automatically decreases product stock based on ordered quantity
2. `create_shipment_after_order` - Automatically creates a shipment record when an order is placed
3. `update_cart_subtotal` - Recalculates cart subtotal when items are added or updated
4. `set_default_review_rating` - Sets a default rating if none is provided in a review
5. `validate_review_purchase` - Prevents users from reviewing products they haven't purchased

## Stored Procedures

The application uses the following stored procedures:

1. `PlaceOrder` - Creates an order from cart items and updates inventory
2. `AddToWishlist` - Adds a product to a user's wishlist
3. `TrackOrder` - Retrieves shipping status and delivery information
4. `WriteReview` - Submits and validates product reviews
5. `GetUserOrderHistory` - Retrieves a user's order history with summary statistics 
-- Database setup for 306_project
USE `306_project`;

-- Users table
CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `address` TEXT,
  `phone` VARCHAR(20),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS `Products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `category` VARCHAR(50),
  `image_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS `Orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`)
);

-- OrderItems table
CREATE TABLE IF NOT EXISTS `OrderItems` (
  `item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `Orders`(`order_id`),
  FOREIGN KEY (`product_id`) REFERENCES `Products`(`product_id`)
);

-- Shipments table
CREATE TABLE IF NOT EXISTS `Shipments` (
  `shipment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `tracking_number` VARCHAR(50),
  `shipment_date` DATE,
  `estimated_delivery` DATE,
  `actual_delivery` DATE,
  `status` ENUM('Processing', 'In Transit', 'Delivered') DEFAULT 'Processing',
  FOREIGN KEY (`order_id`) REFERENCES `Orders`(`order_id`)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS `Reviews` (
  `review_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  `comment` TEXT,
  `review_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `Products`(`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`)
);

-- Wishlist table
CREATE TABLE IF NOT EXISTS `Wishlist` (
  `wishlist_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `added_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`),
  FOREIGN KEY (`product_id`) REFERENCES `Products`(`product_id`),
  UNIQUE KEY (`user_id`, `product_id`)
);

-- Stored Procedures
DELIMITER //

-- PlaceOrder procedure (simplified and fixed)
DROP PROCEDURE IF EXISTS `PlaceOrder`//
CREATE PROCEDURE `PlaceOrder`(
  IN p_user_id INT,
  IN p_products JSON,
  OUT p_order_id INT
)
BEGIN
  DECLARE total DECIMAL(10,2) DEFAULT 0;
  
  -- Calculate total (simplified approach)
  SELECT SUM(p.price * JSON_EXTRACT(items.item_data, '$.quantity')) INTO total
  FROM Products p
  JOIN JSON_TABLE(
    p_products,
    '$[*]' COLUMNS (
      product_id INT PATH '$.product_id',
      item_data JSON PATH '$'
    )
  ) as items ON p.product_id = items.product_id;
  
  -- Create order
  INSERT INTO Orders (user_id, total_amount)
  VALUES (p_user_id, total);
  
  SET p_order_id = LAST_INSERT_ID();
  
  -- Add order items
  INSERT INTO OrderItems (order_id, product_id, quantity, price)
  SELECT 
    p_order_id, 
    p.product_id, 
    JSON_EXTRACT(items.item_data, '$.quantity'), 
    p.price
  FROM Products p
  JOIN JSON_TABLE(
    p_products,
    '$[*]' COLUMNS (
      product_id INT PATH '$.product_id',
      item_data JSON PATH '$'
    )
  ) as items ON p.product_id = items.product_id;
  
  -- Update stock
  UPDATE Products p
  JOIN JSON_TABLE(
    p_products,
    '$[*]' COLUMNS (
      product_id INT PATH '$.product_id',
      quantity INT PATH '$.quantity'
    )
  ) as items ON p.product_id = items.product_id
  SET p.stock_quantity = p.stock_quantity - items.quantity;
END //

-- TrackOrder procedure
CREATE PROCEDURE IF NOT EXISTS `TrackOrder`(
  IN p_order_id INT
)
BEGIN
  SELECT o.order_id, o.order_date, o.status AS order_status,
         s.shipment_id, s.tracking_number, s.shipment_date, 
         s.estimated_delivery, s.status AS shipment_status
  FROM Orders o
  LEFT JOIN Shipments s ON o.order_id = s.order_id
  WHERE o.order_id = p_order_id;
END //

-- OrderStatus procedure
CREATE PROCEDURE IF NOT EXISTS `OrderStatus`(
  IN p_order_id INT
)
BEGIN
  SELECT o.order_id, o.status, o.order_date, o.total_amount,
         oi.product_id, oi.quantity, oi.price,
         p.name AS product_name
  FROM Orders o
  JOIN OrderItems oi ON o.order_id = oi.order_id
  JOIN Products p ON oi.product_id = p.product_id
  WHERE o.order_id = p_order_id;
END //

-- AddReview procedure
CREATE PROCEDURE IF NOT EXISTS `AddReview`(
  IN p_user_id INT,
  IN p_product_id INT,
  IN p_rating INT,
  IN p_comment TEXT
)
BEGIN
  INSERT INTO Reviews (user_id, product_id, rating, comment)
  VALUES (p_user_id, p_product_id, p_rating, p_comment);
END //

-- AddToWishlist procedure
CREATE PROCEDURE IF NOT EXISTS `AddToWishlist`(
  IN p_user_id INT,
  IN p_product_id INT
)
BEGIN
  INSERT INTO Wishlist (user_id, product_id)
  VALUES (p_user_id, p_product_id)
  ON DUPLICATE KEY UPDATE added_date = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- Insert Users
INSERT INTO Users (username, password, email, full_name, address, phone)
VALUES 
('user1', 'password1', 'user1@example.com', 'User One', '123 Street, City', '123-456-7890'),
('user2', 'password2', 'user2@example.com', 'User Two', '456 Avenue, Town', '234-567-8901'),
('user3', 'password3', 'user3@example.com', 'User Three', '789 Boulevard, Village', '345-678-9012');

-- Insert Products
INSERT INTO Products (name, description, price, stock_quantity, category, image_url)
VALUES 
('Summer Dress', 'Light cotton summer dress', 49.99, 100, 'Dresses', '/images/summer-dress.jpg'),
('Floral Blouse', 'Elegant floral pattern blouse', 29.99, 80, 'Tops', '/images/floral-blouse.jpg'),
('Skinny Jeans', 'Classic blue skinny jeans', 59.99, 90, 'Bottoms', '/images/skinny-jeans.jpg'),
('Maxi Skirt', 'Flowy maxi skirt for summer', 39.99, 60, 'Bottoms', '/images/maxi-skirt.jpg'),
('Cardigan', 'Soft knit cardigan for layering', 35.99, 70, 'Outerwear', '/images/cardigan.jpg'),
('Leather Jacket', 'Classic faux leather jacket', 89.99, 40, 'Outerwear', '/images/leather-jacket.jpg'),
('Ankle Boots', 'Stylish ankle boots with low heel', 69.99, 50, 'Footwear', '/images/ankle-boots.jpg'),
('Statement Necklace', 'Bold statement necklace', 19.99, 100, 'Accessories', '/images/necklace.jpg'),
('Structured Handbag', 'Elegant structured handbag', 79.99, 45, 'Accessories', '/images/handbag.jpg'),
('Sun Hat', 'Wide brim sun hat for summer', 24.99, 65, 'Accessories', '/images/sun-hat.jpg'); 
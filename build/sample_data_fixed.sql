-- Sample data for 306_project database
USE 306_project;

-- Disable foreign key checks while importing
SET FOREIGN_KEY_CHECKS = 0;

-- First drop the triggers to avoid conflicts during data import
DROP TRIGGER IF EXISTS update_stock_after_order;
DROP TRIGGER IF EXISTS create_shipment_after_order;

-- Insert users
INSERT INTO Users (user_id, name, email, password) VALUES
(1, 'Alice Johnson', 'alice@example.com', 'password123'),
(2, 'Bob Smith', 'bob@example.com', 'securepass'),
(3, 'Charlie Davis', 'charlie@example.com', 'charliepass'),
(4, 'Diana Martinez', 'diana@example.com', 'dianasecret'),
(5, 'Ethan Brown', 'ethan@example.com', 'ethanpass'),
(6, 'Fiona Clark', 'fiona@example.com', 'fionakey'),
(7, 'George Wilson', 'george@example.com', 'georgepassproducts'),
(8, 'Hannah Lee', 'hannah@example.com', 'hannahsafe'),
(9, 'Ian Taylor', 'ian@example.com', 'iantopsecret'),
(10, 'Julia White', 'julia@example.com', 'juliapass');

-- Insert products
INSERT INTO Products (product_id, name, description, stock, price) VALUES
(1, 'Leather Handbag', 'Elegant leather handbag with gold chain strap.', 15, 79.99),
(2, 'Tote Bag', 'Spacious tote bag with modern design.', 20, 49.99),
(3, 'Crossbody Bag', 'Small crossbody bag for daily use.', 25, 59.99),
(4, 'Clutch Bag', 'Chic clutch bag perfect for evening wear.', 18, 39.99),
(5, 'Backpack', 'Trendy mini backpack for casual wear.', 22, 69.99),
(6, 'Satchel Bag', 'Practical satchel for work and formal events.', 17, 89.99),
(7, 'Mini Bag', 'Stylish mini bag with for daily use.', 19, 54.99),
(8, 'Wristlet', 'Small wristlet purse for essentials.', 30, 29.99),
(9, 'Shoulder Bag', 'Classic shoulder bag for everyday use.', 21, 74.99),
(10, 'Evening Bag', 'Luxury evening bag with rhinestone details.', 14, 99.99),

(11, 'Gold Hoop Earrings', '18K gold hoop earrings for a timeless look.', 25, 149.99),
(12, 'Silver Pendant Necklace', 'Sterling silver necklace with gemstone pendant.', 30, 119.99),
(13, 'Charm Bracelet', 'Elegant charm bracelet with customizable charms.', 20, 89.99),
(14, 'Stud Earrings', 'Classic diamond stud earrings.', 15, 199.99),
(15, 'Pearl Necklace', 'Beautiful pearl necklace for formal occasions.', 18, 249.99),
(16, 'Bangle Bracelet', 'Gold-plated bangle bracelet.', 22, 79.99),
(17, 'Layered Necklace', 'Trendy layered necklace set.', 27, 69.99),
(18, 'Promise Ring', 'Pink heart gem ring.', 19, 99.99),
(19, 'Anklet', 'Dainty silver anklet with heart charms.', 24, 59.99),
(20, 'Tennis Bracelet', 'Round white gold diamond bracelet.', 16, 109.99),

(21, 'Silk Blouse', 'Luxury silk blouse.', 25, 59.99),
(22, 'Denim Flare Jeans', 'Stretchy high-waisted flare jeans.', 30, 69.99),
(23, 'Maxi Dress', 'Elegant flowy maxi dress.', 18, 89.99),
(24, 'Blazer', 'Tailored blazer for a polished look.', 22, 99.99),
(25, 'Chiffon mini Skirt', 'Soft chiffon mini skirt.', 20, 49.99),
(26, 'Denim Jacket', 'Trendy cropped denim jacket.', 15, 119.99),
(27, 'Jumpsuit', 'Casual and comfortable jumpsuit.', 28, 79.99),
(28, 'Floral Summer Dress', 'Breathable floral summer dress.', 24, 69.99),
(29, 'Velvet Sweater', 'Warm and cozy sweater.', 19, 89.99),
(30, 'Wool Cardigan', 'Soft wool cardigan.', 12, 149.99),

(31, 'Running Sneakers', 'Comfortable and stylish running sneakers.', 20, 99.99),
(32, 'Heeled Sandals', 'Chic high-heeled sandals for parties.', 18, 89.99),
(33, 'Ankle Boots', 'Trendy ankle boots with suede finish.', 16, 129.99),
(34, 'Ballet Flats', 'Elegant and comfortable ballet flats.', 25, 59.99),
(35, 'Wedge Sandals', 'Casual wedge sandals for summer.', 21, 69.99),
(36, 'Loafers', 'Stylish loafers for work.', 22, 79.99),
(37, 'White Sneakers', 'Classic everyday sneakers.', 17, 99.99),
(38, 'Stiletto Heels', 'Elegant high-heeled stilettos for formal events.', 17, 109.99),
(39, 'Slippers', 'Cozy indoor slippers with faux fur.', 30, 49.99),
(40, 'Knee-High Boots', 'Elegant leather knee-high boots.', 14, 159.99);

-- Insert category data
INSERT INTO Bags (product_id, color, type, size) VALUES
(1, 'Black', 'Leather Handbag', 'Medium'),
(2, 'Pink', 'Tote Bag', 'Large'),
(3, 'Brown', 'Crossbody Bag', 'Small'),
(4, 'Burgundy', 'Clutch Bag', 'Small'),
(5, 'Blue', 'Backpack', 'Medium'),
(6, 'White', 'Satchel Bag', 'Medium'),
(7, 'Beige', 'Mini Bag', 'Small'),
(8, 'Gold', 'Wristlet', 'Small'),
(9, 'Navy blue', 'Shoulder Bag', 'Medium'),
(10, 'Silver', 'Evening Bag', 'Small');

INSERT INTO Jewellery (product_id, color, type, metal) VALUES
(11, 'Gold', 'Hoop Earrings', 'Gold'),
(12, 'Silver', 'Pendant Necklace', 'Silver'),
(13, 'Rose Gold', 'Charm Bracelet', 'Gold'),
(14, 'White', 'Stud Earrings', 'Platinum'),
(15, 'Pearl', 'Pearl Necklace', 'Pearl'),
(16, 'Gold', 'Bangle Bracelet', 'Gold'),
(17, 'Silver', 'Layered Necklace', 'Silver'),
(18, 'Pink', 'Promise Ring', 'Gold'),
(19, 'Silver', 'Anklet', 'Sterling Silver'),
(20, 'White Gold', 'Tennis Bracelet', 'Gold');

INSERT INTO Clothes (product_id, size, color, material, type) VALUES
(21, 'Medium', 'White', 'Silk', 'Blouse'),
(22, 'Small', 'Blue', 'Denim', 'Flare Jeans'),
(23, 'Large', 'Red', 'Cotton', 'Maxi Dress'),
(24, 'Medium', 'Black', 'Polyester', 'Blazer'),
(25, 'Small', 'Pink', 'Chiffon', 'Mini Skirt'),
(26, 'Medium', 'Blue', 'Denim', 'Jacket'),
(27, 'Large', 'Green', 'Linen', 'Jumpsuit'),
(28, 'Small', 'Yellow', 'Cotton', 'Floral Summer Dress'),
(29, 'Medium', 'Lavender', 'Velvet', 'Sweater'),
(30, 'Large', 'Beige', 'Wool', 'Cardigan');

INSERT INTO Shoes (product_id, type, color, size) VALUES
(31, 'Running Sneakers', 'Pink', '38'),
(32, 'Heeled Sandals', 'Gold', '39'),
(33, 'Ankle Boots', 'Brown', '40'),
(34, 'Ballet Flats', 'Beige', '37'),
(35, 'Wedge Sandals', 'White', '36'),
(36, 'Loafers', 'Black', '38'),
(37, 'White Sneakers', 'White', '39'),
(38, 'Stiletto Heels', 'Red', '38'),
(39, 'Slippers', 'Blue', '37'),
(40, 'Knee-High Boots', 'Tan', '40');

-- Insert orders
INSERT INTO Orders (order_id, user_id, order_date, order_status, total, city) VALUES
(1, 1, '2025-02-20 10:30:00', 'Pending', 120.00, 'New York'),
(2, 2, '2025-02-21 11:15:00', 'Shipped', 180.00, 'Los Angeles'),
(3, 3, '2025-02-22 12:45:00', 'Delivered', 95.00, 'Chicago'),
(4, 4, '2025-02-23 14:00:00', 'Cancelled', 80.00, 'Houston'),
(5, 5, '2025-02-24 15:20:00', 'Pending', 230.00, 'San Francisco'),
(6, 6, '2025-02-25 16:35:00', 'Shipped', 160.00, 'Seattle'),
(7, 7, '2025-02-26 18:00:00', 'Delivered', 280.00, 'Boston'),
(8, 8, '2025-02-27 19:10:00', 'Pending', 150.00, 'Miami'),
(9, 9, '2025-02-28 20:25:00', 'Shipped', 210.00, 'Dallas'),
(10, 10, '2025-02-28 21:40:00', 'Cancelled', 110.00, 'Denver');

-- Insert order_contains
INSERT INTO Order_Contains (order_id, product_id, quantity) VALUES
(1, 31, 1),
(2, 33, 2),
(3, 35, 1),
(4, 32, 1),
(5, 34, 2),
(6, 36, 1),
(7, 37, 1),
(8, 38, 1),
(9, 39, 2),
(10, 40, 1);

-- Insert shipment data
INSERT INTO Shipment (shipment_id, order_id, shipment_status, shipment_date, delivery_date) VALUES
(1, 1, 'Processing', NULL, NULL),
(2, 2, 'Shipped', '2025-02-21 12:00:00', '2025-02-24'),
(3, 3, 'Delivered', '2025-02-22 13:00:00', '2025-02-25'),
(4, 4, 'Cancelled', NULL, NULL),
(5, 5, 'Processing', '2025-02-24 14:30:00', '2025-02-27'),
(6, 6, 'Shipped', '2025-02-25 15:45:00', '2025-02-28'),
(7, 7, 'Delivered', '2025-02-26 16:50:00', '2025-02-28'),
(8, 8, 'Processing', '2025-02-27 17:10:00', '2025-03-02'),
(9, 9, 'Shipped', '2025-02-28 18:20:00', '2025-03-03'),
(10, 10, 'Cancelled', NULL, NULL);

-- Insert payment data
INSERT INTO Payment (payment_id, order_id, amount, payment_method, payment_status) VALUES
(1, 1, 120.00, 'Credit Card', 'Pending'),
(2, 2, 180.00, 'Cash on Delivery', 'Completed'),
(3, 3, 95.00, 'Credit Card', 'Completed'),
(4, 4, 80.00, 'Credit Card', 'Failed'),
(5, 5, 230.00, 'Cash on Delivery', 'Pending'),
(6, 6, 160.00, 'Credit Card', 'Completed'),
(7, 7, 280.00, 'Credit Card', 'Completed'),
(8, 8, 150.00, 'Cash on Delivery', 'Pending'),
(9, 9, 210.00, 'Credit Card', 'Completed'),
(10, 10, 110.00, 'Credit Card', 'Failed');

-- Insert wishlist data
INSERT INTO Wishlist (wishlist_id, user_id, subtotal) VALUES
(1, 1, 50.00),
(2, 2, 120.00),
(3, 3, 75.50),
(4, 4, 90.00),
(5, 5, 30.25),
(6, 6, 60.40),
(7, 7, 100.00),
(8, 8, 45.75),
(9, 9, 80.90),
(10, 10, 150.00);

-- Insert wishlist_contains data
INSERT INTO Wishlist_Contains (wishlist_id, product_id, quantity) VALUES
(1, 1, 2),
(2, 3, 1),
(3, 2, 4),
(4, 5, 3),
(5, 6, 1),
(6, 8, 2),
(7, 9, 5),
(8, 4, 3),
(9, 7, 1),
(10, 10, 2);

-- Insert cart data
INSERT INTO Cart (cart_id, user_id, subtotal) VALUES
(1, 1, 80.00),
(2, 2, 40.50),
(3, 3, 55.75),
(4, 4, 90.00),
(5, 5, 120.30),
(6, 6, 30.20),
(7, 7, 100.00),
(8, 8, 75.60),
(9, 9, 50.90),
(10, 10, 60.75);

-- Insert cart_contains data
INSERT INTO Cart_Contains (cart_id, product_id, quantity) VALUES
(1, 11, 1),
(2, 12, 1),
(3, 13, 1),
(4, 14, 1),
(5, 15, 1),
(6, 16, 1),
(7, 17, 1),
(8, 18, 1),
(9, 19, 1),
(10, 20, 1);

-- Insert reviews
INSERT INTO Reviews (review_id, user_id, product_id, comment, rating, review_date) VALUES
(1, 1, 31, 'Very comfortable for daily use. Love the design!', 5, '2025-01-15 08:30:00'),
(2, 2, 33, 'Good quality but runs a bit small.', 4, '2025-01-16 09:45:00'),
(3, 3, 35, 'Perfect for summer! Comfortable and stylish.', 5, '2025-01-17 10:20:00'),
(4, 4, 32, 'Nice but not as durable as I expected.', 3, '2025-01-18 11:15:00'),
(5, 7, 37, 'Clean design, goes with everything!', 5, '2025-01-19 12:30:00'),
(6, 6, 36, 'Professional look, perfect for office.', 4, '2025-01-20 13:45:00'),
(7, 9, 39, 'Super cozy! Worth every penny.', 5, '2025-01-21 14:20:00'),
(8, 5, 34, 'Elegant and comfortable for all-day wear.', 4, '2025-01-22 15:10:00'),
(9, 8, 38, 'Beautiful but hard to walk in for long periods.', 3, '2025-01-23 16:05:00'),
(10, 10, 40, 'Amazing quality and very stylish!', 5, '2025-01-24 17:30:00');

-- Set the AUTO_INCREMENT values to continue from the last ID
ALTER TABLE Users AUTO_INCREMENT = 11;
ALTER TABLE Products AUTO_INCREMENT = 41;
ALTER TABLE Orders AUTO_INCREMENT = 11;
ALTER TABLE Shipment AUTO_INCREMENT = 11;
ALTER TABLE Payment AUTO_INCREMENT = 11;
ALTER TABLE Wishlist AUTO_INCREMENT = 11;
ALTER TABLE Cart AUTO_INCREMENT = 11;
ALTER TABLE Reviews AUTO_INCREMENT = 11;

-- Recreate triggers
DELIMITER //

CREATE TRIGGER update_stock_after_order
AFTER INSERT ON Order_Contains
FOR EACH ROW
BEGIN
    UPDATE Products
    SET stock = stock - NEW.quantity
    WHERE product_id = NEW.product_id;
END //

CREATE TRIGGER create_shipment_after_order
AFTER INSERT ON Orders
FOR EACH ROW
BEGIN
    -- Only create a shipment if one doesn't already exist for this order
    IF NOT EXISTS (SELECT * FROM Shipment WHERE order_id = NEW.order_id) THEN
        INSERT INTO Shipment (order_id, shipment_status)
        VALUES (NEW.order_id, 'Processing');
    END IF;
END //

DELIMITER ;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1; 
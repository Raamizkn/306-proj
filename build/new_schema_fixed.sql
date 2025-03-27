-- Fixed schema for 306_project database
USE 306_project;

-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Reviews;
DROP TABLE IF EXISTS Writes_Review;
DROP TABLE IF EXISTS Cart_Contains;
DROP TABLE IF EXISTS Cart;
DROP TABLE IF EXISTS Wishlist_Contains;
DROP TABLE IF EXISTS Wishlist;
DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS Shipment;
DROP TABLE IF EXISTS Order_Contains;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS Shoes;
DROP TABLE IF EXISTS Clothes;
DROP TABLE IF EXISTS Jewellery;
DROP TABLE IF EXISTS Bags;
DROP TABLE IF EXISTS Products;
DROP TABLE IF EXISTS Users;

-- Create Users table
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE Products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    stock INT,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0)
);

CREATE TABLE Bags (
    product_id INT PRIMARY KEY,
    color VARCHAR(50),
    type VARCHAR(100),
    size VARCHAR(50),
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Jewellery (
    product_id INT PRIMARY KEY,
    color VARCHAR(50),
    type VARCHAR(100),
    metal VARCHAR(100),
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Clothes (
    product_id INT PRIMARY KEY,
    size VARCHAR(50),
    color VARCHAR(50),
    material VARCHAR(100),
    type VARCHAR(100),
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Shoes (
    product_id INT PRIMARY KEY,
    type VARCHAR(100),
    color VARCHAR(50),
    size VARCHAR(50),
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    order_status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    total DECIMAL(10,2) NOT NULL,
    city VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Order_Contains (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Shipment (
    shipment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    shipment_status ENUM('Pending', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Processing',
    shipment_date DATETIME,
    delivery_date DATE,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE
);

CREATE TABLE Payment (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE
);

CREATE TABLE Wishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Wishlist_Contains (
    wishlist_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (wishlist_id, product_id),
    FOREIGN KEY (wishlist_id) REFERENCES Wishlist(wishlist_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

CREATE TABLE Cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Cart_Contains (
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (cart_id, product_id),
    FOREIGN KEY (cart_id) REFERENCES Cart(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

-- Create Reviews table (combining with Writes_Review to match the sample data)
CREATE TABLE Reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    comment TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
);

-- Create triggers for product stock updates when an order is placed
DELIMITER //

CREATE TRIGGER update_stock_after_order
AFTER INSERT ON Order_Contains
FOR EACH ROW
BEGIN
    UPDATE Products
    SET stock = stock - NEW.quantity
    WHERE product_id = NEW.product_id;
END //

-- Create triggers for shipment creation when an order is placed
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

SET FOREIGN_KEY_CHECKS = 1; 
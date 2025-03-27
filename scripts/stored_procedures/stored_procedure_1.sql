DELIMITER //

CREATE PROCEDURE PlaceOrder(IN p_user_id INT)
BEGIN
    DECLARE v_cart_id INT;
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_order_id INT;
    DECLARE v_city VARCHAR(100);
    
    -- Set the city (for simplicity in this example)
    SET v_city = 'Istanbul';
    
    -- Get the cart ID for the user
    SELECT cart_id, subtotal INTO v_cart_id, v_total
    FROM Cart
    WHERE user_id = p_user_id;
    
    -- Create a new order
    INSERT INTO Orders (user_id, total, city)
    VALUES (p_user_id, v_total, v_city);
    
    -- Get the last inserted order ID
    SET v_order_id = LAST_INSERT_ID();
    
    -- Copy items from cart to order_contains
    INSERT INTO Order_Contains (order_id, product_id, quantity)
    SELECT v_order_id, product_id, quantity
    FROM Cart_Contains
    WHERE cart_id = v_cart_id;
    
    -- Clear the cart
    DELETE FROM Cart_Contains WHERE cart_id = v_cart_id;
    
    -- Reset cart subtotal
    UPDATE Cart SET subtotal = 0 WHERE cart_id = v_cart_id;
    
    -- Return the order_id for confirmation
    SELECT v_order_id AS order_id, v_total AS total, NOW() AS order_date;
END//

DELIMITER ; 
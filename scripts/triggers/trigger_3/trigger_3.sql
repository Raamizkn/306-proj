DELIMITER //

CREATE TRIGGER update_cart_subtotal
AFTER INSERT ON Cart_Contains
FOR EACH ROW
BEGIN
    DECLARE total_price DECIMAL(10,2);
    
    -- Calculate the new subtotal for the cart
    SELECT SUM(p.price * cc.quantity) INTO total_price
    FROM Cart_Contains cc
    JOIN Products p ON cc.product_id = p.product_id
    WHERE cc.cart_id = NEW.cart_id;
    
    -- Update the cart's subtotal
    UPDATE Cart
    SET subtotal = total_price
    WHERE cart_id = NEW.cart_id;
END//

DELIMITER ;

-- Also create a trigger for AFTER UPDATE
DELIMITER //

CREATE TRIGGER update_cart_subtotal_after_update
AFTER UPDATE ON Cart_Contains
FOR EACH ROW
BEGIN
    DECLARE total_price DECIMAL(10,2);
    
    -- Calculate the new subtotal for the cart
    SELECT SUM(p.price * cc.quantity) INTO total_price
    FROM Cart_Contains cc
    JOIN Products p ON cc.product_id = p.product_id
    WHERE cc.cart_id = NEW.cart_id;
    
    -- Update the cart's subtotal
    UPDATE Cart
    SET subtotal = total_price
    WHERE cart_id = NEW.cart_id;
END//

DELIMITER ; 
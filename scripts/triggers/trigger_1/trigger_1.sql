DELIMITER //

CREATE TRIGGER update_stock_after_order
AFTER INSERT ON Order_Contains
FOR EACH ROW
BEGIN
    -- Decrease product stock based on ordered quantity
    UPDATE Products
    SET stock = stock - NEW.quantity
    WHERE product_id = NEW.product_id;
END//

DELIMITER ; 
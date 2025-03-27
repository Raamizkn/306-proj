DELIMITER //

CREATE TRIGGER validate_review_purchase
BEFORE INSERT ON Writes_Review
FOR EACH ROW
BEGIN
    DECLARE has_purchased INT;
    
    -- Check if the user has purchased the product
    SELECT COUNT(*) INTO has_purchased
    FROM Orders o
    JOIN Order_Contains oc ON o.order_id = oc.order_id
    WHERE o.user_id = NEW.user_id AND oc.product_id = NEW.product_id;
    
    -- If the user hasn't purchased the product, prevent the review
    IF has_purchased = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Cannot review a product that has not been purchased';
    END IF;
END//

DELIMITER ; 
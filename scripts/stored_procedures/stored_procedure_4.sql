DELIMITER //

CREATE PROCEDURE WriteReview(
    IN p_user_id INT, 
    IN p_product_id INT, 
    IN p_rating INT, 
    IN p_comment TEXT
)
BEGIN
    DECLARE v_has_purchased INT;
    
    -- Check if user has purchased the product
    SELECT COUNT(*) INTO v_has_purchased
    FROM Orders o
    JOIN Order_Contains oc ON o.order_id = oc.order_id
    WHERE o.user_id = p_user_id AND oc.product_id = p_product_id;
    
    -- If user has purchased the product, add the review
    IF v_has_purchased > 0 THEN
        INSERT INTO Writes_Review (user_id, product_id, rating, comment)
        VALUES (p_user_id, p_product_id, p_rating, p_comment);
        
        SELECT 'Review submitted successfully' AS message;
        
        -- Return the submitted review details
        SELECT 
            wr.review_id, 
            wr.rating, 
            wr.comment, 
            p.name AS product_name,
            u.name AS user_name
        FROM 
            Writes_Review wr
        JOIN 
            Products p ON wr.product_id = p.product_id
        JOIN 
            Users u ON wr.user_id = u.user_id
        WHERE 
            wr.review_id = LAST_INSERT_ID();
    ELSE
        SELECT 'Cannot review a product that has not been purchased' AS message;
    END IF;
END//

DELIMITER ; 
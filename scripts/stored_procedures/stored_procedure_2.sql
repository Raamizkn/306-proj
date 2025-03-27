DELIMITER //

CREATE PROCEDURE AddToWishlist(
    IN p_user_id INT,
    IN p_product_id INT
)
BEGIN
    DECLARE v_wishlist_id INT DEFAULT NULL;
    
    SELECT wishlist_id INTO v_wishlist_id FROM Wishlist WHERE user_id = p_user_id LIMIT 1;
    
    IF v_wishlist_id IS NULL THEN
        INSERT INTO Wishlist (user_id, subtotal) VALUES (p_user_id, 0);
        SET v_wishlist_id = LAST_INSERT_ID();
    END IF;
    
    INSERT INTO Wishlist_Contains (wishlist_id, product_id, quantity) 
    VALUES (v_wishlist_id, p_product_id, 1) 
    ON DUPLICATE KEY UPDATE quantity = quantity + 1;
    
    UPDATE Wishlist w 
    SET w.subtotal = (
        SELECT SUM(p.price * wc.quantity) 
        FROM Wishlist_Contains wc 
        JOIN Products p ON wc.product_id = p.product_id 
        WHERE wc.wishlist_id = w.wishlist_id
    )
    WHERE w.wishlist_id = v_wishlist_id;
    
    -- Return just the wishlist_id to match what the PHP code expects
    SELECT v_wishlist_id AS wishlist_id;
END//

DELIMITER ; 
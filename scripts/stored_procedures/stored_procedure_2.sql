DELIMITER //

CREATE PROCEDURE AddToWishlist(IN p_user_id INT, IN p_product_id INT)
BEGIN
    DECLARE v_wishlist_id INT;
    DECLARE v_exists INT;
    DECLARE v_product_price DECIMAL(10,2);
    
    -- Get product price
    SELECT price INTO v_product_price
    FROM Products
    WHERE product_id = p_product_id;
    
    -- Check if wishlist exists for user
    SELECT COUNT(*) INTO v_exists
    FROM Wishlist
    WHERE user_id = p_user_id;
    
    -- If no wishlist exists, create one
    IF v_exists = 0 THEN
        INSERT INTO Wishlist (user_id, subtotal)
        VALUES (p_user_id, 0);
    END IF;
    
    -- Get the wishlist ID
    SELECT wishlist_id INTO v_wishlist_id
    FROM Wishlist
    WHERE user_id = p_user_id;
    
    -- Check if product already in wishlist
    SELECT COUNT(*) INTO v_exists
    FROM Wishlist_Contains
    WHERE wishlist_id = v_wishlist_id AND product_id = p_product_id;
    
    -- If product not in wishlist, add it
    IF v_exists = 0 THEN
        INSERT INTO Wishlist_Contains (wishlist_id, product_id, quantity)
        VALUES (v_wishlist_id, p_product_id, 1);
        
        -- Update wishlist subtotal
        UPDATE Wishlist
        SET subtotal = subtotal + v_product_price
        WHERE wishlist_id = v_wishlist_id;
        
        SELECT 'Product added to wishlist' AS message;
    ELSE
        SELECT 'Product already in wishlist' AS message;
    END IF;
    
    -- Return wishlist details
    SELECT w.wishlist_id, w.subtotal, p.name, p.price
    FROM Wishlist w
    JOIN Wishlist_Contains wc ON w.wishlist_id = wc.wishlist_id
    JOIN Products p ON wc.product_id = p.product_id
    WHERE w.user_id = p_user_id;
END//

DELIMITER ; 
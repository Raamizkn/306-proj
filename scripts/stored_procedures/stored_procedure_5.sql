DELIMITER //

CREATE PROCEDURE OrderStatus(
    IN p_user_id INT, 
    IN p_start_date DATE, 
    IN p_end_date DATE
)
BEGIN
    -- Get all orders for the user within the date range
    SELECT 
        o.order_id,
        o.order_date,
        o.total,
        o.order_status,
        s.shipment_status,
        s.delivery_date
    FROM 
        Orders o
    LEFT JOIN 
        Shipment s ON o.order_id = s.order_id
    WHERE 
        o.user_id = p_user_id
        AND (p_start_date IS NULL OR o.order_date >= p_start_date)
        AND (p_end_date IS NULL OR o.order_date <= p_end_date)
    ORDER BY 
        o.order_date DESC;
        
    -- Calculate summary statistics
    SELECT 
        COUNT(*) AS total_orders,
        SUM(o.total) AS total_spent,
        MIN(o.order_date) AS first_order_date,
        MAX(o.order_date) AS last_order_date
    FROM 
        Orders o
    WHERE 
        o.user_id = p_user_id
        AND (p_start_date IS NULL OR o.order_date >= p_start_date)
        AND (p_end_date IS NULL OR o.order_date <= p_end_date);
    
    -- Get product categories breakdown
    SELECT 
        CASE
            WHEN b.product_id IS NOT NULL THEN 'Bags'
            WHEN j.product_id IS NOT NULL THEN 'Jewellery'
            WHEN c.product_id IS NOT NULL THEN 'Clothes'
            WHEN s.product_id IS NOT NULL THEN 'Shoes'
            ELSE 'Other'
        END AS category,
        COUNT(*) AS item_count,
        SUM(oc.quantity) AS total_quantity,
        SUM(p.price * oc.quantity) AS category_total
    FROM 
        Orders o
    JOIN 
        Order_Contains oc ON o.order_id = oc.order_id
    JOIN 
        Products p ON oc.product_id = p.product_id
    LEFT JOIN 
        Bags b ON p.product_id = b.product_id
    LEFT JOIN 
        Jewellery j ON p.product_id = j.product_id
    LEFT JOIN 
        Clothes c ON p.product_id = c.product_id
    LEFT JOIN 
        Shoes s ON p.product_id = s.product_id
    WHERE 
        o.user_id = p_user_id
        AND (p_start_date IS NULL OR o.order_date >= p_start_date)
        AND (p_end_date IS NULL OR o.order_date <= p_end_date)
    GROUP BY 
        category;
END//

DELIMITER ; 
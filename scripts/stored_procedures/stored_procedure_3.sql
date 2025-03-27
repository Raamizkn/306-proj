DELIMITER //

CREATE PROCEDURE TrackOrder(IN p_order_id INT)
BEGIN
    -- Get order and shipment details
    SELECT 
        o.order_id, 
        o.order_date, 
        o.order_status, 
        s.shipment_id, 
        s.shipment_status, 
        s.shipment_date, 
        s.delivery_date
    FROM 
        Orders o
    LEFT JOIN 
        Shipment s ON o.order_id = s.order_id
    WHERE 
        o.order_id = p_order_id;
        
    -- Get product details for the order
    SELECT 
        p.name, 
        p.product_id, 
        oc.quantity,
        p.price,
        (p.price * oc.quantity) AS subtotal
    FROM 
        Order_Contains oc
    JOIN 
        Products p ON oc.product_id = p.product_id
    WHERE 
        oc.order_id = p_order_id;
END//

DELIMITER ; 
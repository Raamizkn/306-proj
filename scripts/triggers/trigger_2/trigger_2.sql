DELIMITER //

CREATE TRIGGER create_shipment_after_order
AFTER INSERT ON Orders
FOR EACH ROW
BEGIN
    -- Create a new shipment record with 'Processing' status
    INSERT INTO Shipment (order_id, shipment_status)
    VALUES (NEW.order_id, 'Processing');
END//

DELIMITER ; 
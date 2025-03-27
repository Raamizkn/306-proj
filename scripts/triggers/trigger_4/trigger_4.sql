DELIMITER //

CREATE TRIGGER set_default_review_rating
BEFORE INSERT ON Writes_Review
FOR EACH ROW
BEGIN
    -- Set default rating to 5 if rating is NULL
    IF NEW.rating IS NULL THEN
        SET NEW.rating = 5;
    END IF;
END//

DELIMITER ; 
-- Install all 5 stored procedures
-- Execute this script to create or replace all required stored procedures

-- Drop existing procedures first to avoid errors
DROP PROCEDURE IF EXISTS PlaceOrder;
DROP PROCEDURE IF EXISTS AddToWishlist;
DROP PROCEDURE IF EXISTS TrackOrder;
DROP PROCEDURE IF EXISTS AddReview;
DROP PROCEDURE IF EXISTS OrderStatus;

-- Include the contents of all five procedure files
source scripts/stored_procedures/stored_procedure_1.sql;
source scripts/stored_procedures/stored_procedure_2.sql;
source scripts/stored_procedures/stored_procedure_3.sql;
source scripts/stored_procedures/stored_procedure_4.sql;
source scripts/stored_procedures/stored_procedure_5.sql;

-- Display success message
SELECT 'All 5 stored procedures have been installed successfully.' AS message; 
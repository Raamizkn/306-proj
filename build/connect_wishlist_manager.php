<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Handle adding item to wishlist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["action"]) && $data["action"] == "add" 
    && isset($data["user_id"]) && isset($data["product_id"])) {
    
    $user_id = $data["user_id"];
    $product_id = $data["product_id"];
    
    try {
        // First check if the AddToWishlist stored procedure exists
        $check_proc = $conn->query("SHOW PROCEDURE STATUS WHERE Db = '306_project' AND Name = 'AddToWishlist'");
        
        if ($check_proc->num_rows == 0) {
            // Create the stored procedure if it doesn't exist
            $create_proc = "
                CREATE PROCEDURE AddToWishlist(
                    IN p_user_id INT,
                    IN p_product_id INT
                )
                BEGIN
                    DECLARE v_wishlist_id INT DEFAULT NULL;
                    
                    -- Get wishlist ID for user
                    SELECT wishlist_id INTO v_wishlist_id FROM Wishlist WHERE user_id = p_user_id LIMIT 1;
                    
                    -- Create wishlist if it doesn't exist
                    IF v_wishlist_id IS NULL THEN
                        INSERT INTO Wishlist (user_id, subtotal) VALUES (p_user_id, 0);
                        SET v_wishlist_id = LAST_INSERT_ID();
                    END IF;
                    
                    -- Add or update product in wishlist
                    INSERT INTO Wishlist_Contains (wishlist_id, product_id, quantity) 
                    VALUES (v_wishlist_id, p_product_id, 1) 
                    ON DUPLICATE KEY UPDATE quantity = quantity + 1;
                    
                    -- Update subtotal
                    UPDATE Wishlist w SET w.subtotal = (
                        SELECT SUM(p.price * wc.quantity) 
                        FROM Wishlist_Contains wc 
                        JOIN Products p ON wc.product_id = p.product_id 
                        WHERE wc.wishlist_id = w.wishlist_id
                    )
                    WHERE w.wishlist_id = v_wishlist_id;
                    
                    -- Return the wishlist id
                    SELECT v_wishlist_id AS wishlist_id;
                END;
            ";
            
            $conn->query($create_proc);
        }
        
        // Call the stored procedure
        $stmt = $conn->prepare("CALL AddToWishlist(?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $wishlist_id = 0;
        
        if ($result && $row = $result->fetch_assoc()) {
            $wishlist_id = $row['wishlist_id'];
        }
        
        $stmt->close();
        
        // Success response
        $response = [
            'success' => true,
            'message' => 'Item added to wishlist successfully!',
            'user_id' => $user_id,
            'product_id' => $product_id,
            'wishlist_id' => $wishlist_id
        ];
    } catch (Exception $e) {
        // Error response
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
    
    // Return the response
    echo json_encode($response);
}
// Handle getting wishlist items
else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["action"]) && $data["action"] == "get" 
         && isset($data["user_id"])) {
    
    $user_id = $data["user_id"];
    
    // Query to get wishlist items with product details
    $query = "SELECT w.wishlist_id, w.user_id, w.subtotal,
                     wc.product_id, wc.quantity,
                     p.name, p.description, p.price, p.stock
              FROM Wishlist w
              JOIN Wishlist_Contains wc ON w.wishlist_id = wc.wishlist_id
              JOIN Products p ON wc.product_id = p.product_id
              WHERE w.user_id = ?
              ORDER BY p.name";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $wishlist_items = [];
        
        while ($row = $result->fetch_assoc()) {
            $wishlist_items[] = $row;
        }
        
        // Get wishlist summary
        $summary_query = "SELECT subtotal FROM Wishlist WHERE user_id = ?";
        $summary_stmt = $conn->prepare($summary_query);
        $summary_stmt->bind_param("i", $user_id);
        $summary_stmt->execute();
        $summary_result = $summary_stmt->get_result();
        $subtotal = 0;
        
        if ($summary_result->num_rows > 0) {
            $subtotal = $summary_result->fetch_assoc()['subtotal'];
        }
        $summary_stmt->close();
        
        // Success response
        $response = [
            'success' => true,
            'message' => count($wishlist_items) . ' wishlist items found',
            'user_id' => $user_id,
            'subtotal' => $subtotal,
            'wishlist' => $wishlist_items
        ];
        
        $stmt->close();
    } else {
        // Error preparing statement
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Required parameters are missing.'
    ]);
}

$conn->close();
?> 
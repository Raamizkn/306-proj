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
    $quantity = $data["quantity"] ?? 1;
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Check if user has a wishlist
        $wishlist_query = "SELECT wishlist_id FROM Wishlist WHERE user_id = ?";
        $wishlist_stmt = $conn->prepare($wishlist_query);
        $wishlist_stmt->bind_param("i", $user_id);
        $wishlist_stmt->execute();
        $wishlist_result = $wishlist_stmt->get_result();
        
        if ($wishlist_result->num_rows > 0) {
            // User already has a wishlist
            $wishlist_id = $wishlist_result->fetch_assoc()['wishlist_id'];
        } else {
            // Create a new wishlist for the user
            $create_stmt = $conn->prepare("INSERT INTO Wishlist (user_id, subtotal) VALUES (?, 0)");
            $create_stmt->bind_param("i", $user_id);
            $create_stmt->execute();
            $wishlist_id = $conn->insert_id;
            $create_stmt->close();
        }
        $wishlist_stmt->close();
        
        // Get product price
        $price_query = "SELECT price FROM Products WHERE product_id = ?";
        $price_stmt = $conn->prepare($price_query);
        $price_stmt->bind_param("i", $product_id);
        $price_stmt->execute();
        $price_result = $price_stmt->get_result();
        $product_price = 0;
        
        if ($price_result->num_rows > 0) {
            $product_price = $price_result->fetch_assoc()['price'];
        }
        $price_stmt->close();
        
        // Add or update product in wishlist
        $upsert_query = "INSERT INTO Wishlist_Contains (wishlist_id, product_id, quantity) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)";
        $upsert_stmt = $conn->prepare($upsert_query);
        $upsert_stmt->bind_param("iii", $wishlist_id, $product_id, $quantity);
        $upsert_stmt->execute();
        $upsert_stmt->close();
        
        // Update subtotal
        $update_subtotal = "UPDATE Wishlist w SET w.subtotal = (
                            SELECT SUM(p.price * wc.quantity) 
                            FROM Wishlist_Contains wc 
                            JOIN Products p ON wc.product_id = p.product_id 
                            WHERE wc.wishlist_id = w.wishlist_id
                            )
                            WHERE w.wishlist_id = ?";
        $subtotal_stmt = $conn->prepare($update_subtotal);
        $subtotal_stmt->bind_param("i", $wishlist_id);
        $subtotal_stmt->execute();
        $subtotal_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Success response
        $response = [
            'success' => true,
            'message' => 'Item added to wishlist successfully!',
            'user_id' => $user_id,
            'product_id' => $product_id,
            'wishlist_id' => $wishlist_id
        ];
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
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
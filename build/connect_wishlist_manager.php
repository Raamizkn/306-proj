<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("WISHLIST MANAGER - Starting stored procedure test");

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Handle adding item to wishlist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["action"]) && $data["action"] == "add" 
    && isset($data["user_id"]) && isset($data["product_id"])) {
    
    $user_id = $data["user_id"];
    $product_id = $data["product_id"];
    
    error_log("WISHLIST MANAGER - About to call AddToWishlist($user_id, $product_id)");
    
    try {
        // Call the AddToWishlist stored procedure
        $stmt = $conn->prepare("CALL AddToWishlist(?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        
        error_log("WISHLIST MANAGER - AddToWishlist executed successfully");
        
        $result = $stmt->get_result();
        $wishlist_id = 0;
        
        if ($result && $row = $result->fetch_assoc()) {
            $wishlist_id = $row['wishlist_id'];
            error_log("WISHLIST MANAGER - Retrieved wishlist_id: $wishlist_id");
        } else {
            error_log("WISHLIST MANAGER - No result from stored procedure");
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
        error_log("WISHLIST MANAGER - Exception: " . $e->getMessage());
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
    error_log("WISHLIST MANAGER - Getting wishlist items for user_id: $user_id");
    
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
        
        error_log("WISHLIST MANAGER - Retrieved " . count($wishlist_items) . " wishlist items");
        
        // Get wishlist summary
        $summary_query = "SELECT subtotal FROM Wishlist WHERE user_id = ?";
        $summary_stmt = $conn->prepare($summary_query);
        $summary_stmt->bind_param("i", $user_id);
        $summary_stmt->execute();
        $summary_result = $summary_stmt->get_result();
        $subtotal = 0;
        
        if ($summary_result->num_rows > 0) {
            $subtotal = $summary_result->fetch_assoc()['subtotal'];
            error_log("WISHLIST MANAGER - Retrieved subtotal: $subtotal");
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
        error_log("WISHLIST MANAGER - Failed to prepare statement: " . $conn->error);
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    error_log("WISHLIST MANAGER - Invalid request, missing required parameters");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Required parameters are missing.'
    ]);
}

error_log("WISHLIST MANAGER - Completed processing");
$conn->close();
?> 
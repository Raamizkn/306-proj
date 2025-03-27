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
    
    // Using direct SQL instead of stored procedure
    try {
        // Insert or update the wishlist record
        $stmt = $conn->prepare("INSERT INTO Wishlist (user_id, product_id) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE added_date = CURRENT_TIMESTAMP");
        
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $wishlist_id = $stmt->insert_id ?: 0;  // If updated, insert_id will be 0
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
    $query = "SELECT w.wishlist_id, w.user_id, w.product_id, w.added_date, 
              p.name, p.description, p.price, p.category, p.image_url
              FROM Wishlist w
              JOIN Products p ON w.product_id = p.product_id
              WHERE w.user_id = ?
              ORDER BY w.added_date DESC";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $wishlist_items = [];
        
        while ($row = $result->fetch_assoc()) {
            $wishlist_items[] = $row;
        }
        
        // Success response
        $response = [
            'success' => true,
            'message' => count($wishlist_items) . ' wishlist items found',
            'user_id' => $user_id,
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
<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Handle adding a review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["user_id"]) && isset($data["product_id"]) && isset($data["rating"]) && isset($data["review_text"])) {
    
    $user_id = $data["user_id"];
    $product_id = $data["product_id"];
    $rating = $data["rating"];
    $comment = $data["review_text"];
    
    try {
        // Insert the review directly with SQL
        $stmt = $conn->prepare("INSERT INTO Reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
        $stmt->execute();
        $review_id = $stmt->insert_id;
        
        // Success response
        $response = [
            'success' => true,
            'message' => 'Review added successfully!',
            'review_id' => $review_id,
            'user_id' => $user_id,
            'product_id' => $product_id,
            'rating' => $rating
        ];
        
        $stmt->close();
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
// Handle getting product reviews with "action": "get"
else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["action"]) && $data["action"] == "get" 
         && isset($data["product_id"])) {
    
    $product_id = $data["product_id"];
    
    // Query to get reviews for a product
    $query = "SELECT r.review_id, r.product_id, r.user_id, r.rating, r.comment, r.review_date,
              u.name as username, p.name as product_name
              FROM Reviews r
              JOIN Users u ON r.user_id = u.user_id
              JOIN Products p ON r.product_id = p.product_id
              WHERE r.product_id = ?
              ORDER BY r.review_date DESC";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $reviews = [];
        
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        
        // Get product info
        $productQuery = "SELECT * FROM Products WHERE product_id = ?";
        $productStmt = $conn->prepare($productQuery);
        $productStmt->bind_param("i", $product_id);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $product = $productResult->fetch_assoc();
        
        // Calculate average rating
        $avgRating = 0;
        if (count($reviews) > 0) {
            $sum = 0;
            foreach ($reviews as $review) {
                $sum += $review['rating'];
            }
            $avgRating = $sum / count($reviews);
        }
        
        // Success response
        $response = [
            'success' => true,
            'message' => count($reviews) . ' reviews found',
            'product_id' => $product_id,
            'product' => $product,
            'reviews' => $reviews,
            'average_rating' => $avgRating
        ];
        
        $stmt->close();
        $productStmt->close();
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
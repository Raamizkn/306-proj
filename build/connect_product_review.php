<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("PRODUCT REVIEW - Starting stored procedure test");

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Handle adding a review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["user_id"]) && isset($data["product_id"]) && isset($data["rating"]) && isset($data["review_text"])) {
    
    $user_id = $data["user_id"];
    $product_id = $data["product_id"];
    $rating = $data["rating"];
    $comment = $data["review_text"];
    
    error_log("PRODUCT REVIEW - About to call AddReview($user_id, $product_id, $rating)");
    
    try {
        // Call the AddReview stored procedure
        $stmt = $conn->prepare("CALL AddReview(?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
        $stmt->execute();
        
        error_log("PRODUCT REVIEW - AddReview executed successfully");
        
        $result = $stmt->get_result();
        
        if ($result) {
            $message = $result->fetch_assoc()['message'];
            error_log("PRODUCT REVIEW - First result set retrieved: $message");
            
            // Check if there's a review details result set
            if ($stmt->more_results()) {
                $stmt->next_result();
                $review_result = $stmt->get_result();
                $review_data = $review_result->fetch_assoc();
                
                error_log("PRODUCT REVIEW - Second result set retrieved with review details");
                
                // Success response with review details
                $response = [
                    'success' => true,
                    'message' => $message,
                    'review_data' => $review_data
                ];
            } else {
                error_log("PRODUCT REVIEW - No second result set available");
                
                // Success response with just the message
                $response = [
                    'success' => ($message === 'Review submitted successfully'),
                    'message' => $message
                ];
            }
        } else {
            // No result from the stored procedure
            error_log("PRODUCT REVIEW - No result from stored procedure");
            $response = [
                'success' => false,
                'message' => 'Error submitting review: No response from procedure'
            ];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // Error response
        error_log("PRODUCT REVIEW - Exception: " . $e->getMessage());
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
    error_log("PRODUCT REVIEW - Invalid request, missing required parameters");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Required parameters are missing.'
    ]);
}

error_log("PRODUCT REVIEW - Completed processing");
$conn->close();
?> 
<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("ORDER PLACEMENT - Starting stored procedure implementation");

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["user_id"])) {
    $user_id = $data["user_id"];
    
    try {
        error_log("ORDER PLACEMENT - Debug information: Preparing to call PlaceOrder stored procedure");
        
        // Prepare the call to the stored procedure with p_user_id and p_products parameters
        $stmt = $conn->prepare("CALL PlaceOrder(?, ?)");
        
        if (!$stmt) {
            error_log("ORDER PLACEMENT - Debug information: Statement preparation failed: " . $conn->error);
            throw new Exception("Statement preparation failed: " . $conn->error);
        }
        
        error_log("ORDER PLACEMENT - Debug information: Statement prepared successfully");
        
        // Create JSON array of products for second parameter
        // This would normally come from the cart but for simplicity, we'll create sample data
        $products_json = json_encode([
            ["product_id" => 1, "quantity" => 1]
        ]);
        
        error_log("ORDER PLACEMENT - Debug information: JSON data: " . $products_json);
        
        // Bind parameters - user_id as integer, products_json as string
        $stmt->bind_param("is", $user_id, $products_json);
        error_log("ORDER PLACEMENT - Debug information: Parameters bound successfully");
        
        // Execute the stored procedure
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("ORDER PLACEMENT - Debug information: Execution failed: " . $stmt->error);
            throw new Exception("Execution failed: " . $stmt->error);
        }
        
        error_log("ORDER PLACEMENT - PlaceOrder executed successfully");
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $order_id = $row['order_id'];
            $total = $row['total'];
            $order_date = $row['order_date'];
            
            error_log("ORDER PLACEMENT - Retrieved data: order_id=$order_id, total=$total");
            
            // Success response
            $response = [
                'success' => true,
                'message' => 'Order placed successfully!',
                'details' => [
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                    'total' => $total,
                    'order_date' => $order_date
                ]
            ];
        } else {
            // No result from stored procedure
            error_log("ORDER PLACEMENT - No result from stored procedure");
            $response = [
                'success' => false,
                'message' => 'Failed to place order. No result from stored procedure.'
            ];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // Error response
        error_log("ORDER PLACEMENT - Exception: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    error_log("ORDER PLACEMENT - Invalid request, missing user_id");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. User ID is required.'
    ]);
}

error_log("ORDER PLACEMENT - Completed processing");
$conn->close();
?> 
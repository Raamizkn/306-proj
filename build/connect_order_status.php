<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("ORDER HISTORY - Starting stored procedure test");

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["user_id"])) {
    $user_id = $data["user_id"];
    $start_date = $data["start_date"] ?? null;
    $end_date = $data["end_date"] ?? null;
    
    error_log("ORDER HISTORY - About to call OrderStatus($user_id, " . ($start_date ?: 'NULL') . ", " . ($end_date ?: 'NULL') . ")");
    
    try {
        // Call the OrderStatus stored procedure
        $stmt = $conn->prepare("CALL OrderStatus(?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $start_date, $end_date);
        $stmt->execute();
        
        error_log("ORDER HISTORY - OrderStatus executed successfully");
        
        // Get orders list
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        error_log("ORDER HISTORY - Result set retrieved, orders count: " . count($orders));
        
        // Success response
        $response = [
            'success' => true,
            'message' => count($orders) . ' orders found',
            'user_id' => $user_id,
            'orders' => $orders
        ];
        
        $stmt->close();
        
    } catch (Exception $e) {
        // Error in database operations
        error_log("ORDER HISTORY - Exception: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'user_id' => $user_id
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    error_log("ORDER HISTORY - Invalid request, missing user_id");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. User ID is required.'
    ]);
}

error_log("ORDER HISTORY - Completed processing");
$conn->close();
?> 
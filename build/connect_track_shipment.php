<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("TRACK SHIPMENT - Starting stored procedure test");

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["order_id"])) {
    $order_id = $data["order_id"];
    
    error_log("TRACK SHIPMENT - About to call TrackOrder($order_id)");
    
    try {
        // Call the TrackOrder stored procedure
        $stmt = $conn->prepare("CALL TrackOrder(?)");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        error_log("TRACK SHIPMENT - TrackOrder executed successfully");
        
        // Get order and shipment details
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $shipment_data = $result->fetch_assoc();
            error_log("TRACK SHIPMENT - First result set retrieved successfully");
            
            // Get next result set (product details for the order)
            $stmt->next_result();
            $items_result = $stmt->get_result();
            $order_items = [];
            
            while ($item = $items_result->fetch_assoc()) {
                $order_items[] = $item;
            }
            
            error_log("TRACK SHIPMENT - Second result set retrieved successfully, items count: " . count($order_items));
            
            // Success response
            $response = [
                'success' => true,
                'message' => 'Order tracking information found!',
                'order_id' => $order_id,
                'shipment' => $shipment_data,
                'items' => $order_items
            ];
        } else {
            // No order found
            error_log("TRACK SHIPMENT - No shipment information found for order_id: $order_id");
            $response = [
                'success' => false,
                'message' => 'Order not found or has no shipment information.',
                'order_id' => $order_id
            ];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // Error response
        error_log("TRACK SHIPMENT - Exception: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'order_id' => $order_id
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    error_log("TRACK SHIPMENT - Invalid request, missing order_id");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Order ID is required.'
    ]);
}

error_log("TRACK SHIPMENT - Completed processing");
$conn->close();
?> 
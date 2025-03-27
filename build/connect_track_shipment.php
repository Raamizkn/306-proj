<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["order_id"])) {
    $order_id = $data["order_id"];
    
    // Query the order and shipment information directly instead of using a stored procedure
    $query = "SELECT o.order_id, o.order_date, o.order_status, 
                     s.shipment_id, s.shipment_status, s.shipment_date, s.delivery_date,
                     (SELECT COUNT(*) FROM Order_Contains oc WHERE oc.order_id = o.order_id) as item_count
              FROM Orders o
              LEFT JOIN Shipment s ON o.order_id = s.order_id
              WHERE o.order_id = ?";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $shipment_data = $result->fetch_assoc();
            
            // Success response
            $response = [
                'success' => true,
                'message' => 'Order found!',
                'order_id' => $order_id,
                'shipment' => $shipment_data
            ];
        } else {
            // No order found
            $response = [
                'success' => false,
                'message' => 'Order not found or has no shipment information.',
                'order_id' => $order_id
            ];
        }
        
        $stmt->close();
    } else {
        // Error preparing statement
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $conn->error,
            'order_id' => $order_id
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Order ID is required.'
    ]);
}

$conn->close();
?> 
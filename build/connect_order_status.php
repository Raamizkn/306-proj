<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["order_id"])) {
    $order_id = $data["order_id"];
    
    // Call the OrderStatus stored procedure
    $stmt = $conn->prepare("CALL OrderStatus(?)");
    
    if ($stmt) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $order_items = [];
            $total_amount = 0;
            $order_status = '';
            $order_date = '';
            
            // Gather all items from the result
            while ($row = $result->fetch_assoc()) {
                $order_items[] = [
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'subtotal' => $row['price'] * $row['quantity']
                ];
                
                // Get order details from first row
                if (empty($order_status)) {
                    $order_status = $row['status'];
                    $order_date = $row['order_date'];
                    $total_amount = $row['total_amount'];
                }
            }
            
            // Success response
            $response = [
                'success' => true,
                'message' => 'Order found!',
                'order_id' => $order_id,
                'order_status' => $order_status,
                'order_date' => $order_date,
                'total_amount' => $total_amount,
                'items' => $order_items
            ];
        } else {
            // No order found
            $response = [
                'success' => false,
                'message' => 'Order not found',
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
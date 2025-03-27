<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["order_id"])) {
    $order_id = $data["order_id"];
    
    try {
        // Query order details
        $order_query = "SELECT o.order_id, o.order_status, o.order_date, o.total 
                        FROM Orders o 
                        WHERE o.order_id = ?";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        
        if ($order_result && $order_result->num_rows > 0) {
            $order_data = $order_result->fetch_assoc();
            $order_stmt->close();
            
            // Query order items with product details
            $items_query = "SELECT oc.product_id, p.name as product_name, oc.quantity, p.price
                            FROM Order_Contains oc
                            JOIN Products p ON oc.product_id = p.product_id
                            WHERE oc.order_id = ?";
            $items_stmt = $conn->prepare($items_query);
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            
            $order_items = [];
            
            if ($items_result && $items_result->num_rows > 0) {
                while ($row = $items_result->fetch_assoc()) {
                    $subtotal = $row['price'] * $row['quantity'];
                    $order_items[] = [
                        'product_id' => $row['product_id'],
                        'product_name' => $row['product_name'],
                        'quantity' => $row['quantity'],
                        'price' => $row['price'],
                        'subtotal' => $subtotal
                    ];
                }
            }
            $items_stmt->close();
            
            // Success response
            $response = [
                'success' => true,
                'message' => 'Order found!',
                'order_id' => $order_id,
                'order_status' => $order_data['order_status'],
                'order_date' => $order_data['order_date'],
                'total_amount' => $order_data['total'],
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
    } catch (Exception $e) {
        // Error in database operations
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
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Order ID is required.'
    ]);
}

$conn->close();
?> 
<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($data["user_id"])) {
    $user_id = $data["user_id"];
    $product_id = $data["product_id"] ?? 1;
    $quantity = $data["quantity"] ?? 1;
    
    // Instead of using the stored procedure which has conflicts,
    // Let's directly create the order using regular queries
    
    // Get product price
    $product_query = "SELECT price FROM Products WHERE product_id = ?";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $product = $product_result->fetch_assoc();
    $price = $product['price'] ?? 0;
    $product_stmt->close();
    
    // Calculate total
    $total_amount = $price * $quantity;
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert into Orders with city parameter for new schema
        $order_stmt = $conn->prepare("INSERT INTO Orders (user_id, total, city) VALUES (?, ?, 'Online Order')");
        $order_stmt->bind_param("id", $user_id, $total_amount);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();
        
        // Insert into Order_Contains (changed from OrderItems)
        $item_stmt = $conn->prepare("INSERT INTO Order_Contains (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $item_stmt->bind_param("iii", $order_id, $product_id, $quantity);
        $item_stmt->execute();
        $item_stmt->close();
        
        // No need to manually update stock - we have a trigger for that now
        
        // Commit the transaction
        $conn->commit();
        
        // Get the order date from the database for accuracy
        $date_query = "SELECT order_date FROM Orders WHERE order_id = ?";
        $date_stmt = $conn->prepare($date_query);
        $date_stmt->bind_param("i", $order_id);
        $date_stmt->execute();
        $date_result = $date_stmt->get_result();
        $order_date = $date_result->fetch_assoc()['order_date'] ?? date('Y-m-d H:i:s');
        $date_stmt->close();
        
        // Success response
        $response = [
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'details' => [
                'order_id' => $order_id,
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total_amount,
                'order_date' => $order_date
            ]
        ];
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $conn->rollback();
        
        // Error response
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
    
    // Return the response
    echo json_encode($response);
} else {
    // If not a valid request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. User ID is required.'
    ]);
}

$conn->close();
?> 
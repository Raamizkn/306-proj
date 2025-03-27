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
        // Insert into Orders
        $order_stmt = $conn->prepare("INSERT INTO Orders (user_id, total_amount) VALUES (?, ?)");
        $order_stmt->bind_param("id", $user_id, $total_amount);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();
        
        // Insert into OrderItems
        $item_stmt = $conn->prepare("INSERT INTO OrderItems (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $item_stmt->execute();
        $item_stmt->close();
        
        // Update stock (careful with this if triggers exist)
        $stock_stmt = $conn->prepare("UPDATE Products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stock_stmt->bind_param("ii", $quantity, $product_id);
        $stock_stmt->execute();
        $stock_stmt->close();
        
        // Commit the transaction
        $conn->commit();
        
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
                'order_date' => date('Y-m-d H:i:s')
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
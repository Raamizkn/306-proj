<?php
// Database connectivity test
require_once 'db_connection.php';

// Output as JSON
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'tables' => [],
    'user' => 'clothes_app'
];

if (!$conn->connect_error) {
    $response['success'] = true;
    $response['message'] = 'Database connection successful!';
    
    // Get tables
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $response['tables'][] = $row[0];
        }
    }
    
    // Test a simple query to the Products table
    $product_result = $conn->query("SELECT * FROM Products LIMIT 3");
    if ($product_result) {
        $products = [];
        while ($row = $product_result->fetch_assoc()) {
            $products[] = [
                'id' => $row['product_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'category' => $row['category']
            ];
        }
        $response['products'] = $products;
    }
    
    // Test a stored procedure call
    $stmt = $conn->prepare("CALL OrderStatus(?)");
    if ($stmt) {
        $order_id = 1;
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order_data = [];
        while ($row = $result->fetch_assoc()) {
            $order_data[] = $row;
        }
        $response['order_status'] = !empty($order_data) ? $order_data : "No order with ID: $order_id";
        $stmt->close();
    } else {
        $response['procedure_error'] = "Couldn't prepare statement: " . $conn->error;
    }
    
} else {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
}

echo json_encode($response, JSON_PRETTY_PRINT);
$conn->close();
?> 
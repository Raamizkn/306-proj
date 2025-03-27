<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get products from database
$query = "SELECT * FROM Products ORDER BY name";
$result = $conn->query($query);

if ($result) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => count($products) . ' products found',
        'products' => $products
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving products: ' . $conn->error
    ]);
}

$conn->close();
?> 
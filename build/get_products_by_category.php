<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get category from URL parameter
$category = isset($_GET['category']) ? $_GET['category'] : '';

if (empty($category)) {
    echo json_encode([
        'success' => false,
        'message' => 'Category parameter is required'
    ]);
    exit;
}

// Validate category to prevent SQL injection
$valid_categories = ['clothes', 'bags', 'jewellery', 'shoes'];
if (!in_array($category, $valid_categories)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category'
    ]);
    exit;
}

// Map category to table name
$table_map = [
    'clothes' => 'Clothes',
    'bags' => 'Bags',
    'jewellery' => 'Jewellery',
    'shoes' => 'Shoes'
];

$table = $table_map[$category];

// Query to get products by category
$query = "SELECT p.*, c.* FROM Products p 
          JOIN {$table} c ON p.product_id = c.product_id
          ORDER BY p.name";

$result = $conn->query($query);

if ($result) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => count($products) . ' products found in ' . ucfirst($category),
        'category' => $category,
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
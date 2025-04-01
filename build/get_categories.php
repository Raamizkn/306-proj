<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Define the categories based on the database schema
$categories = [
    [
        'id' => 'clothes',
        'name' => 'Clothing',
        'icon' => 'fas fa-tshirt',
        'table' => 'Clothes'
    ],
    [
        'id' => 'bags',
        'name' => 'Bags',
        'icon' => 'fas fa-shopping-bag',
        'table' => 'Bags'
    ],
    [
        'id' => 'jewellery',
        'name' => 'Jewelry',
        'icon' => 'fas fa-gem',
        'table' => 'Jewellery'
    ],
    [
        'id' => 'shoes',
        'name' => 'Shoes',
        'icon' => 'fas fa-shoe-prints',
        'table' => 'Shoes'
    ]
];

// Count products in each category
foreach ($categories as &$category) {
    $query = "SELECT COUNT(*) as count FROM {$category['table']}";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $category['count'] = $row['count'];
    } else {
        $category['count'] = 0;
    }
}

echo json_encode([
    'success' => true,
    'categories' => $categories
]);

$conn->close();
?> 
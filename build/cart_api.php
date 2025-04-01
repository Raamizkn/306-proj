<?php
header('Content-Type: application/json');
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Return the cart contents
        echo json_encode([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'cart' => $_SESSION['cart']
        ]);
        break;
    
    case 'POST':
        // Get data from request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request data'
            ]);
            exit;
        }
        
        // Check if we're adding, updating, or removing
        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'add':
                    // Validate required fields
                    if (!isset($data['product_id']) || !isset($data['quantity'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Missing required fields (product_id, quantity)'
                        ]);
                        exit;
                    }
                    
                    // Check if product already exists in cart
                    $found = false;
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['product_id'] == $data['product_id']) {
                            // Update quantity
                            $item['quantity'] += intval($data['quantity']);
                            $found = true;
                            break;
                        }
                    }
                    
                    // Add new item if not found
                    if (!$found) {
                        $_SESSION['cart'][] = [
                            'product_id' => $data['product_id'],
                            'quantity' => intval($data['quantity'])
                        ];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product added to cart',
                        'cart' => $_SESSION['cart']
                    ]);
                    break;
                
                case 'update':
                    // Validate required fields
                    if (!isset($data['product_id']) || !isset($data['quantity'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Missing required fields (product_id, quantity)'
                        ]);
                        exit;
                    }
                    
                    // Update quantity or remove if quantity is 0
                    $index = -1;
                    foreach ($_SESSION['cart'] as $i => $item) {
                        if ($item['product_id'] == $data['product_id']) {
                            $index = $i;
                            break;
                        }
                    }
                    
                    if ($index >= 0) {
                        if (intval($data['quantity']) <= 0) {
                            // Remove item if quantity is 0 or negative
                            array_splice($_SESSION['cart'], $index, 1);
                        } else {
                            // Update quantity
                            $_SESSION['cart'][$index]['quantity'] = intval($data['quantity']);
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Cart updated successfully',
                            'cart' => $_SESSION['cart']
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Product not found in cart'
                        ]);
                    }
                    break;
                
                case 'remove':
                    // Validate required fields
                    if (!isset($data['product_id'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Missing required field (product_id)'
                        ]);
                        exit;
                    }
                    
                    // Find and remove the item
                    $index = -1;
                    foreach ($_SESSION['cart'] as $i => $item) {
                        if ($item['product_id'] == $data['product_id']) {
                            $index = $i;
                            break;
                        }
                    }
                    
                    if ($index >= 0) {
                        array_splice($_SESSION['cart'], $index, 1);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Product removed from cart',
                            'cart' => $_SESSION['cart']
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Product not found in cart'
                        ]);
                    }
                    break;
                
                case 'clear':
                    // Clear the entire cart
                    $_SESSION['cart'] = [];
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cart cleared successfully',
                        'cart' => $_SESSION['cart']
                    ]);
                    break;
                
                default:
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid action'
                    ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Missing action parameter'
            ]);
        }
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
}
?> 
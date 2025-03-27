<?php
// Utility script to add items to user carts for testing purposes
require_once 'db_connection.php';

// Check if the script is explicitly called (not just included)
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    echo "<h1>Cart Item Utility</h1>";
    echo "<p>This utility adds sample items to user carts for testing.</p>";
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["populateCarts"])) {
        populateUserCarts();
        echo "<div style='color: green; margin-top: 20px;'>Cart items added successfully!</div>";
    }
    
    // Display the form
    echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' style='margin-top: 20px;'>";
    echo "<button type='submit' name='populateCarts' style='padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer;'>Populate User Carts</button>";
    echo "</form>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px; background-color: #6c757d; color: white; text-decoration: none;'>Back to Home</a>";
    echo "</div>";
}

/**
 * Populates user carts with sample items for testing
 */
function populateUserCarts() {
    global $conn;
    
    // Clear existing cart items first
    $conn->query("DELETE FROM Cart_Contains");
    
    // Reset cart subtotals
    $conn->query("UPDATE Cart SET subtotal = 0");
    
    // Sample cart items for different users
    $cartItems = [
        // User 1 (Alice)
        ['user_id' => 1, 'items' => [
            ['product_id' => 1, 'quantity' => 1],  // Leather Handbag
            ['product_id' => 11, 'quantity' => 1], // Gold Hoop Earrings
        ]],
        // User 2 (Bob)
        ['user_id' => 2, 'items' => [
            ['product_id' => 21, 'quantity' => 1], // Silk Blouse
            ['product_id' => 31, 'quantity' => 1], // Running Sneakers
        ]],
        // User 3 (Charlie)
        ['user_id' => 3, 'items' => [
            ['product_id' => 5, 'quantity' => 1],  // Backpack
            ['product_id' => 15, 'quantity' => 1], // Pearl Necklace
        ]],
        // User 4 (Diana)
        ['user_id' => 4, 'items' => [
            ['product_id' => 25, 'quantity' => 2], // Chiffon Mini Skirt
            ['product_id' => 35, 'quantity' => 1], // Wedge Sandals
        ]],
        // User 5 (Ethan)
        ['user_id' => 5, 'items' => [
            ['product_id' => 9, 'quantity' => 1],  // Shoulder Bag
            ['product_id' => 19, 'quantity' => 1], // Anklet
        ]],
        // User 6 (Fiona)
        ['user_id' => 6, 'items' => [
            ['product_id' => 23, 'quantity' => 1], // Maxi Dress
            ['product_id' => 33, 'quantity' => 1], // Ankle Boots
        ]],
        // User 7 (George)
        ['user_id' => 7, 'items' => [
            ['product_id' => 7, 'quantity' => 2],  // Mini Bag
            ['product_id' => 27, 'quantity' => 1], // Jumpsuit
        ]],
        // User 8 (Hannah)
        ['user_id' => 8, 'items' => [
            ['product_id' => 17, 'quantity' => 1], // Layered Necklace
            ['product_id' => 37, 'quantity' => 1], // White Sneakers
        ]],
        // User 9 (Ian)
        ['user_id' => 9, 'items' => [
            ['product_id' => 3, 'quantity' => 1],  // Crossbody Bag
            ['product_id' => 29, 'quantity' => 1], // Velvet Sweater
        ]],
        // User 10 (Julia)
        ['user_id' => 10, 'items' => [
            ['product_id' => 13, 'quantity' => 1], // Charm Bracelet
            ['product_id' => 39, 'quantity' => 2], // Slippers
        ]],
    ];
    
    // Insert the items and update subtotals
    foreach ($cartItems as $userCart) {
        $user_id = $userCart['user_id'];
        $items = $userCart['items'];
        
        // Get the cart_id for this user
        $cartResult = $conn->query("SELECT cart_id FROM Cart WHERE user_id = $user_id");
        
        if ($cartResult && $cartResult->num_rows > 0) {
            $cartRow = $cartResult->fetch_assoc();
            $cart_id = $cartRow['cart_id'];
            
            // Insert each item into the cart
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                
                // Insert the cart item
                $conn->query("INSERT INTO Cart_Contains (cart_id, product_id, quantity) 
                             VALUES ($cart_id, $product_id, $quantity)");
            }
            
            // Calculate and update the cart subtotal
            updateCartSubtotal($cart_id);
        }
    }
}

/**
 * Update the subtotal for a cart
 */
function updateCartSubtotal($cart_id) {
    global $conn;
    
    // Calculate the new subtotal for the cart
    $subtotalQuery = "SELECT SUM(p.price * cc.quantity) as total
                     FROM Cart_Contains cc
                     JOIN Products p ON cc.product_id = p.product_id
                     WHERE cc.cart_id = $cart_id";
    
    $subtotalResult = $conn->query($subtotalQuery);
    
    if ($subtotalResult && $subtotalResult->num_rows > 0) {
        $subtotalRow = $subtotalResult->fetch_assoc();
        $subtotal = $subtotalRow['total'] ?: 0;
        
        // Update the cart subtotal
        $conn->query("UPDATE Cart SET subtotal = $subtotal WHERE cart_id = $cart_id");
    }
}
?> 
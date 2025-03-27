<?php
require_once 'db_connection.php';

$message = '';
$orders = [];

// Get a list of users for the form dropdown
$userQuery = "SELECT user_id, name FROM Users";
$userResult = $conn->query($userQuery);

// If form is submitted, call the stored procedure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL PlaceOrder(?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $orderDetails = $result->fetch_assoc();
        $message = "Order #" . $orderDetails['order_id'] . " placed successfully! Total: $" . $orderDetails['total'];
    } else {
        $message = "Error placing order or no items in cart.";
    }
    
    $stmt->close();
    
    // Get recent orders for display
    $orderQuery = "SELECT o.order_id, o.order_date, o.total, u.name as user_name 
                  FROM Orders o 
                  JOIN Users u ON o.user_id = u.user_id 
                  ORDER BY o.order_date DESC 
                  LIMIT 5";
    $orderResult = $conn->query($orderQuery);
    
    if ($orderResult && $orderResult->num_rows > 0) {
        while ($row = $orderResult->fetch_assoc()) {
            $orders[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .results-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #d63384;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>Order Placement</h1>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="form-container">
                    <h2 class="mb-4">Place New Order</h2>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select User:</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">-- Select User --</option>
                                <?php
                                if ($userResult && $userResult->num_rows > 0) {
                                    while ($row = $userResult->fetch_assoc()) {
                                        echo "<option value='" . $row['user_id'] . "'>" . $row['name'] . " (ID: " . $row['user_id'] . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </div>
                    </form>
                    
                    <?php if ($message): ?>
                        <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?> mt-3">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($orders)): ?>
        <div class="row">
            <div class="col-12">
                <div class="results-container">
                    <h2 class="mb-4">Recent Orders</h2>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_id']; ?></td>
                                    <td><?php echo $order['user_name']; ?></td>
                                    <td><?php echo $order['order_date']; ?></td>
                                    <td>$<?php echo $order['total']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
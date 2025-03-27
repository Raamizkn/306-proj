<?php
require_once 'db_connection.php';

$message = '';
$orderDetails = null;
$orderItems = null;

// If form is submitted, call the stored procedure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"])) {
    $order_id = $_POST["order_id"];
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL TrackOrder(?)");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $orderDetails = $result->fetch_assoc();
        
        // Get the second result set which contains order items
        $stmt->next_result();
        $itemsResult = $stmt->get_result();
        
        if ($itemsResult && $itemsResult->num_rows > 0) {
            $orderItems = [];
            while ($row = $itemsResult->fetch_assoc()) {
                $orderItems[] = $row;
            }
            $message = "Order found!";
        }
    } else {
        $message = "Order not found.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment</title>
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
        .shipment-status {
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-processing {
            background-color: #ffe0e0;
            color: #d9534f;
        }
        .status-shipped {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-out-for-delivery {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>Track Shipment</h1>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="form-container">
                    <h2 class="mb-4">Enter Order ID</h2>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="order_id" class="form-label">Order ID:</label>
                            <input type="number" class="form-control" id="order_id" name="order_id" required min="1">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Track Order</button>
                        </div>
                    </form>
                    
                    <?php if ($message && !$orderDetails): ?>
                        <div class="alert alert-danger mt-3">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($orderDetails && $orderItems): ?>
        <div class="row">
            <div class="col-12">
                <div class="results-container">
                    <h2 class="mb-4">Order #<?php echo $orderDetails['order_id']; ?> Details</h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Order Information</h4>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Order Date:
                                    <span><?php echo $orderDetails['order_date']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Order Status:
                                    <span class="badge bg-primary rounded-pill"><?php echo $orderDetails['order_status']; ?></span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h4>Shipment Information</h4>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Shipment ID:
                                    <span>#<?php echo $orderDetails['shipment_id']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Shipment Status:
                                    <span class="shipment-status status-<?php echo strtolower(str_replace(' ', '-', $orderDetails['shipment_status'])); ?>">
                                        <?php echo $orderDetails['shipment_status']; ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Shipment Date:
                                    <span><?php echo $orderDetails['shipment_date']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Estimated Delivery:
                                    <span><?php echo $orderDetails['delivery_date'] ? $orderDetails['delivery_date'] : 'Not available'; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <h4>Order Items</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orderItems as $item): ?>
                                <tr>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo $item['price']; ?></td>
                                    <td>$<?php echo $item['subtotal']; ?></td>
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
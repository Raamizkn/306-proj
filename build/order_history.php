<?php
require_once 'db_connection.php';

$message = '';
$orders = [];
$summary = null;
$categories = [];

// Get a list of users for the form dropdown
$userQuery = "SELECT user_id, name FROM Users";
$userResult = $conn->query($userQuery);

// If form is submitted, call the stored procedure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
    $start_date = !empty($_POST["start_date"]) ? $_POST["start_date"] : null;
    $end_date = !empty($_POST["end_date"]) ? $_POST["end_date"] : null;
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL GetUserOrderHistory(?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $message = count($orders) . " orders found";
        
        // Get the second result set which contains summary statistics
        $stmt->next_result();
        $summaryResult = $stmt->get_result();
        
        if ($summaryResult && $summaryResult->num_rows > 0) {
            $summary = $summaryResult->fetch_assoc();
            
            // Get the third result set which contains category breakdown
            $stmt->next_result();
            $categoryResult = $stmt->get_result();
            
            if ($categoryResult && $categoryResult->num_rows > 0) {
                while ($row = $categoryResult->fetch_assoc()) {
                    $categories[] = $row;
                }
            }
        }
    } else {
        $message = "No orders found for this user in the selected date range.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            margin-bottom: 30px;
        }
        h1, h2, h3 {
            color: #d63384;
        }
        .alert {
            margin-top: 20px;
        }
        .category-pills .badge {
            font-size: 0.9rem;
            padding: 8px 15px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .shipment-status {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.8rem;
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
            <h1>My Order History</h1>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="form-container">
                    <h2 class="mb-4">View Your Order History</h2>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="user_id" class="form-label">Select User:</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">-- Select User --</option>
                                    <?php
                                    if ($userResult && $userResult->num_rows > 0) {
                                        $userResult->data_seek(0); // Reset result pointer
                                        while ($row = $userResult->fetch_assoc()) {
                                            echo "<option value='" . $row['user_id'] . "'>" . $row['name'] . " (ID: " . $row['user_id'] . ")</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Start Date:</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label">End Date:</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">View Order History</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php if ($message && empty($orders)): ?>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="alert alert-info">
                    <?php echo $message; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($orders) && $summary): ?>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="results-container">
                    <h2 class="mb-4">Order Summary</h2>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Orders Overview</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total Orders
                                            <span class="badge bg-primary rounded-pill"><?php echo $summary['total_orders']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total Spent
                                            <span class="badge bg-success rounded-pill">$<?php echo number_format($summary['total_spent'], 2); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            First Order
                                            <span class="badge bg-info rounded-pill"><?php echo date('M d, Y', strtotime($summary['first_order_date'])); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Last Order
                                            <span class="badge bg-info rounded-pill"><?php echo date('M d, Y', strtotime($summary['last_order_date'])); ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Category Breakdown</h5>
                                    
                                    <div class="category-pills mt-3">
                                        <?php foreach($categories as $category): ?>
                                            <span class="badge bg-secondary">
                                                <?php echo $category['category']; ?>: 
                                                <?php echo $category['total_quantity']; ?> items
                                                ($<?php echo number_format($category['category_total'], 2); ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="mt-4 mb-3">Order List</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Shipment</th>
                                    <th>Delivery Date</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $order['order_status'] == 'Delivered' ? 'success' : ($order['order_status'] == 'Cancelled' ? 'danger' : 'primary'); ?>">
                                            <?php echo $order['order_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="shipment-status status-<?php echo strtolower(str_replace(' ', '-', $order['shipment_status'])); ?>">
                                            <?php echo $order['shipment_status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $order['delivery_date'] ? date('M d, Y', strtotime($order['delivery_date'])) : 'Not set'; ?></td>
                                    <td>$<?php echo number_format($order['total'], 2); ?></td>
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
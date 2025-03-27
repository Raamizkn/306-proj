<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's Fashion E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .module-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .module-card:hover {
            transform: translateY(-5px);
        }
        .module-title {
            color: #d63384;
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 30px;
            padding: 20px;
            text-align: center;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .utility-links a {
            margin: 0 10px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h1 class="display-4">Women's Fashion E-commerce</h1>
            <p class="lead">Web Access Modules for CS306 Project</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="module-card">
                    <h3 class="module-title">Order Placement</h3>
                    <p>Create an order from your cart items.</p>
                    <a href="order_placement.php" class="btn btn-primary">Go to Module</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="module-card">
                    <h3 class="module-title">Wishlist Manager</h3>
                    <p>Add products to your wishlist for later.</p>
                    <a href="wishlist_manager.php" class="btn btn-primary">Go to Module</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="module-card">
                    <h3 class="module-title">Track Shipment</h3>
                    <p>Track the status of your order.</p>
                    <a href="track_shipment.php" class="btn btn-primary">Go to Module</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="module-card">
                    <h3 class="module-title">Review Submission</h3>
                    <p>Share your thoughts on purchased products.</p>
                    <a href="review_submission.php" class="btn btn-primary">Go to Module</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="module-card">
                    <h3 class="module-title">My Order History</h3>
                    <p>View your past orders and purchase history.</p>
                    <a href="order_history.php" class="btn btn-primary">Go to Module</a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="module-card">
                    <h3 class="module-title">User Login</h3>
                    <p>Login to your account or create a new one.</p>
                    <a href="login.php" class="btn btn-primary">Go to Module</a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <h4>Utility Links</h4>
            <div class="utility-links">
                <a href="add_cart_items.php" class="btn btn-outline-secondary">Populate Carts</a>
                <a href="SETUP_GUIDE.md" class="btn btn-outline-info" target="_blank">Setup Guide</a>
                <a href="README.md" class="btn btn-outline-dark" target="_blank">README</a>
            </div>
            <p class="mt-3 text-muted">CS306 Project Phase II - Database Triggers, Stored Procedures, and Web Access Modules</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
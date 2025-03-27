<?php
require_once 'db_connection.php';

$message = '';

// If form is submitted for login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id, name, email, password FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // In a real application, you would use password_verify() to check hashed passwords
        // For this demo, we're just comparing the raw password (not secure)
        if ($password == $user['password']) {
            $message = "Login successful! Welcome, " . $user['name'] . "!";
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "User not found.";
    }
    
    $stmt->close();
}

// If form is submitted for registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $message = "Email already registered.";
    } else {
        // Insert new user
        // In a real application, you would use password_hash() to hash the password
        $stmt = $conn->prepare("INSERT INTO Users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            
            // Create a cart for the new user
            $cartStmt = $conn->prepare("INSERT INTO Cart (user_id, subtotal) VALUES (?, 0)");
            $cartStmt->bind_param("i", $new_user_id);
            $cartStmt->execute();
            $cartStmt->close();
            
            // Create an empty wishlist for the new user
            $wishlistStmt = $conn->prepare("INSERT INTO Wishlist (user_id, subtotal) VALUES (?, 0)");
            $wishlistStmt->bind_param("i", $new_user_id);
            $wishlistStmt->execute();
            $wishlistStmt->close();
            
            $message = "Registration successful! You can now log in.";
        } else {
            $message = "Error registering user: " . $stmt->error;
        }
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
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
        h1, h2 {
            color: #d63384;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>User Account</h1>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="form-container">
                    
                    <?php if ($message): ?>
                        <div class="alert <?php echo strpos($message, 'successful') !== false ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-tabs" id="accountTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-tab-pane" type="button" role="tab" aria-controls="login-tab-pane" aria-selected="true">Login</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-tab-pane" type="button" role="tab" aria-controls="register-tab-pane" aria-selected="false">Register</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="accountTabsContent">
                        <div class="tab-pane fade show active" id="login-tab-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                            <h2 class="mb-4 mt-4">Login</h2>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="mb-3">
                                    <label for="login-email" class="form-label">Email:</label>
                                    <input type="email" class="form-control" id="login-email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="login-password" class="form-label">Password:</label>
                                    <input type="password" class="form-control" id="login-password" name="password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="register-tab-pane" role="tabpanel" aria-labelledby="register-tab" tabindex="0">
                            <h2 class="mb-4 mt-4">Register</h2>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="mb-3">
                                    <label for="register-name" class="form-label">Name:</label>
                                    <input type="text" class="form-control" id="register-name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="register-email" class="form-label">Email:</label>
                                    <input type="email" class="form-control" id="register-email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="register-password" class="form-label">Password:</label>
                                    <input type="password" class="form-control" id="register-password" name="password" required minlength="6">
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="register" class="btn btn-success">Register</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
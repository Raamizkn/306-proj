<?php
require_once 'db_connection.php';

$message = '';
$reviewDetails = null;

// Get a list of users for the form dropdown
$userQuery = "SELECT user_id, name FROM Users";
$userResult = $conn->query($userQuery);

// Get a list of products for the form dropdown
$productQuery = "SELECT product_id, name FROM Products";
$productResult = $conn->query($productQuery);

// If form is submitted, call the stored procedure
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"]) && isset($_POST["product_id"])) {
    $user_id = $_POST["user_id"];
    $product_id = $_POST["product_id"];
    $rating = $_POST["rating"];
    $comment = $_POST["comment"];
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL WriteReview(?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $messageRow = $result->fetch_assoc();
        $message = $messageRow['message'];
        
        // Get the second result set with review details if successful
        if (strpos($message, 'successfully') !== false) {
            $stmt->next_result();
            $reviewResult = $stmt->get_result();
            
            if ($reviewResult && $reviewResult->num_rows > 0) {
                $reviewDetails = $reviewResult->fetch_assoc();
            }
        }
    } else {
        $message = "Error submitting review.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submission</title>
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
        }
        h1, h2 {
            color: #d63384;
        }
        .alert {
            margin-top: 20px;
        }
        .star-rating {
            color: #ffc107;
            font-size: 1.5rem;
        }
        .review-card {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>Review Submission</h1>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="form-container">
                    <h2 class="mb-4">Write a Review</h2>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
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
                        
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Select Product:</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">-- Select Product --</option>
                                <?php
                                if ($productResult && $productResult->num_rows > 0) {
                                    while ($row = $productResult->fetch_assoc()) {
                                        echo "<option value='" . $row['product_id'] . "'>" . $row['name'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating:</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment:</label>
                            <textarea class="form-control" id="comment" name="comment" rows="5" required></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </div>
                    </form>
                    
                    <?php if ($message && !$reviewDetails): ?>
                        <div class="alert <?php echo strpos($message, 'Error') !== false || strpos($message, 'Cannot') !== false ? 'alert-danger' : 'alert-success'; ?> mt-3">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($reviewDetails): ?>
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="results-container">
                    <h2 class="mb-4">Review Submitted</h2>
                    <div class="alert alert-success">
                        <?php echo $message; ?>
                    </div>
                    
                    <div class="review-card">
                        <h5><?php echo $reviewDetails['product_name']; ?></h5>
                        <div class="star-rating mb-2">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $reviewDetails['rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                            <span class="ms-2 text-muted"><?php echo $reviewDetails['rating']; ?>/5</span>
                        </div>
                        <p class="mb-2"><em>"<?php echo $reviewDetails['comment']; ?>"</em></p>
                        <small class="text-muted">By <?php echo $reviewDetails['user_name']; ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
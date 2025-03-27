<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

try {
    // Query users
    $user_query = "SELECT user_id, username AS name, email FROM Users";
    $result = $conn->query($user_query);
    
    if ($result && $result->num_rows > 0) {
        $users = array();
        
        while ($row = $result->fetch_assoc()) {
            $users[] = array(
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'email' => $row['email']
            );
        }
        
        // Success response
        $response = array(
            'success' => true,
            'message' => 'Users retrieved successfully',
            'users' => $users
        );
    } else {
        // No users found - return some sample users as fallback
        $response = array(
            'success' => false,
            'message' => 'No users found',
            'users' => array(
                array('user_id' => 1, 'name' => 'User One', 'email' => 'user1@example.com'),
                array('user_id' => 2, 'name' => 'User Two', 'email' => 'user2@example.com'),
                array('user_id' => 3, 'name' => 'User Three', 'email' => 'user3@example.com')
            )
        );
    }
} catch (Exception $e) {
    // Error in database operations
    $response = array(
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'users' => array(
            array('user_id' => 1, 'name' => 'User One', 'email' => 'user1@example.com'),
            array('user_id' => 2, 'name' => 'User Two', 'email' => 'user2@example.com'),
            array('user_id' => 3, 'name' => 'User Three', 'email' => 'user3@example.com')
        )
    );
}

// Return the response
echo json_encode($response);

$conn->close();
?> 
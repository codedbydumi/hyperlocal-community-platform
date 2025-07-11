<?php
// get_notification_count.php
// This file handles AJAX requests to get the current notification count

session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "final";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Only handle POST requests for getting notification count
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_unread_count') {
    
    // Check if user is logged in
    if (!isset($_SESSION['email'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        $conn->close();
        exit();
    }
    
    $userEmail = $_SESSION['email'];
    
    // Count unread notifications from order_confirmations table
    $count_sql = "SELECT COUNT(*) as unread_count FROM order_confirmations 
                  WHERE renter_email = ? AND status = 'pending'";
    $count_stmt = $conn->prepare($count_sql);
    
    if ($count_stmt) {
        $count_stmt->bind_param("s", $userEmail);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'unread_count' => intval($count_row['unread_count'])
        ]);
        $count_stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Count query preparation failed']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?>
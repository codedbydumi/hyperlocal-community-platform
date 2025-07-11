<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get POST data
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate inputs
if ($group_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid group ID']);
    exit();
}

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

// Check if user is member of the group
$userEmail = $_SESSION['email'];
$checkMembershipQuery = "SELECT * FROM group_memberships WHERE user_email = ? AND group_id = ?";
$stmt = $conn->prepare($checkMembershipQuery);
$stmt->bind_param("si", $userEmail, $group_id);
$stmt->execute();
$membershipResult = $stmt->get_result();

if ($membershipResult->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not a member of this group']);
    exit();
}

// Insert the message
$insertMessageQuery = "INSERT INTO group_chat (group_id, user_email, message, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($insertMessageQuery);
$stmt->bind_param("iss", $group_id, $userEmail, $message);

if ($stmt->execute()) {
    $message_id = $conn->insert_id;
    
    // Return success with message data
    echo json_encode([
        'success' => true,
        'message' => [
            'id' => $message_id,
            'user_email' => $userEmail,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'upvotes' => 0,
            'downvotes' => 0
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to send message']);
}

$conn->close();
?>
<?php
session_start(); // Start the session to access user data

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Get the user's email from the session
$userEmail = $_SESSION['email'];

// Check if required parameters are set
if (!isset($_POST['group_id']) || !isset($_POST['message']) || empty($_POST['message'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

// Get the parameters
$group_id = $_POST['group_id'];
$message = $_POST['message'];

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if user is a member of the group
$checkMembershipQuery = "SELECT * FROM group_memberships WHERE user_email = ? AND group_id = ?";
$stmt = $conn->prepare($checkMembershipQuery);
$stmt->bind_param("si", $userEmail, $group_id);
$stmt->execute();
$membershipResult = $stmt->get_result();

if ($membershipResult->num_rows == 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'You are not a member of this group']);
    exit();
}

// Insert message into the database
$insertMessageQuery = "INSERT INTO group_chat (group_id, user_email, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insertMessageQuery);
$stmt->bind_param("iss", $group_id, $userEmail, $message);

// Execute the query
if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message: ' . $stmt->error]);
}

// Close the database connection
$conn->close();
?>
<?php
session_start(); // Start the session to access user data

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";  // Update with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in (check if email is set in session)
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the user's email from the session
$userEmail = $_SESSION['email'];

// Check if group_id is passed in the URL
if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];

    // Check if the user is already a member of the group
    $checkQuery = "SELECT * FROM group_memberships WHERE user_email = ? AND group_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $userEmail, $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User is already a member of the group
        echo "You are already a member of this group.";
    } else {
        // Add the user to the group using their email
        $insertQuery = "INSERT INTO group_memberships (user_email, group_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("si", $userEmail, $group_id);
        if ($stmt->execute()) {
            // After successfully joining the group, redirect to the group details page
            header("Location: group_details.php?group_id=" . $group_id . "&joined=true");
            exit();
        } else {
            echo "Error joining group: " . $stmt->error;
        }
    }
} else {
    echo "Group ID not provided.";
}

// Close the connection
$conn->close();
?>

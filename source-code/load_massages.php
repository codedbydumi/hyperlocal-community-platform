<?php
session_start(); // Start the session to access user data

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo "You must be logged in to view messages.";
    exit();
}

// Check if group_id is passed in the URL
if (!isset($_GET['group_id'])) {
    echo "Group ID not provided.";
    exit();
}

$group_id = $_GET['group_id'];

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

// Fetch all messages for the group
$getMessagesQuery = "SELECT * FROM group_chat WHERE group_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($getMessagesQuery);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$messagesResult = $stmt->get_result();

// Display messages
while ($message = $messagesResult->fetch_assoc()) {
    $message_id = $message['id'];

    // Fetch upvotes and downvotes for the message
    $upvotesQuery = "SELECT COUNT(*) FROM message_votes WHERE message_id = ? AND vote_type = 'upvote'";
    $downvotesQuery = "SELECT COUNT(*) FROM message_votes WHERE message_id = ? AND vote_type = 'downvote'";

    // Prepare and execute upvotes query
    $stmt_upvotes = $conn->prepare($upvotesQuery);
    $stmt_upvotes->bind_param("i", $message_id);
    $stmt_upvotes->execute();
    $upvotesResult = $stmt_upvotes->get_result();
    $upvotes = $upvotesResult->fetch_row()[0];

    // Prepare and execute downvotes query
    $stmt_downvotes = $conn->prepare($downvotesQuery);
    $stmt_downvotes->bind_param("i", $message_id);
    $stmt_downvotes->execute();
    $downvotesResult = $stmt_downvotes->get_result();
    $downvotes = $downvotesResult->fetch_row()[0];

    echo "<div class='message'>";
    echo "<strong>" . $message['user_email'] . "</strong>: " . $message['message'];
    echo "<br><small>" . $message['created_at'] . "</small>";

    echo "<div class='vote-section'>";
    echo "<button class='vote-button upvote' onclick='voteMessage($message_id, \"upvote\")'>Upvote</button>";
    echo "<span class='vote-count'>" . $upvotes . " Upvotes</span>";
    echo "<button class='vote-button downvote' onclick='voteMessage($message_id, \"downvote\")'>Downvote</button>";
    echo "<span class='vote-count'>" . $downvotes . " Downvotes</span>";
    echo "</div>";

    echo "</div>";
}

// Close the database connection
$conn->close();
?>
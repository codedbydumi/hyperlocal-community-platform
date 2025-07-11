<?php
session_start();

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

if (isset($_POST['message_id'], $_POST['vote_type'])) {
    $message_id = $_POST['message_id'];
    $vote_type = $_POST['vote_type'];
    $userEmail = $_SESSION['email'];

    // Check if the user has already voted
    $checkVoteQuery = "SELECT * FROM message_votes WHERE message_id = ? AND user_email = ?";
    $stmt = $conn->prepare($checkVoteQuery);
    $stmt->bind_param("is", $message_id, $userEmail);
    $stmt->execute();
    $voteResult = $stmt->get_result();

    if ($voteResult->num_rows > 0) {
        // If the user has already voted, we update the vote type
        $updateVoteQuery = "UPDATE message_votes SET vote_type = ? WHERE message_id = ? AND user_email = ?";
        $stmt = $conn->prepare($updateVoteQuery);
        $stmt->bind_param("sis", $vote_type, $message_id, $userEmail);
        $stmt->execute();
    } else {
        // If the user hasn't voted, we insert a new vote
        $insertVoteQuery = "INSERT INTO message_votes (message_id, user_email, vote_type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertVoteQuery);
        $stmt->bind_param("iss", $message_id, $userEmail, $vote_type);
        $stmt->execute();
    }
}

$conn->close();
?>

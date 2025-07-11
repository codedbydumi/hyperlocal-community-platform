<?php
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

if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];

    // Fetch all messages for the group
    $sql = "SELECT * FROM group_chat WHERE group_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($message = $result->fetch_assoc()) {
        echo "<div class='message'>";
        echo "<strong>" . $message['user_email'] . "</strong>: " . $message['message'];
        echo "<br><small>" . $message['created_at'] . "</small>";
        echo "</div>";
    }
}

$conn->close();
?>

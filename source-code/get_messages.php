<?php
session_start();

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

// Get group_id from GET parameter
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid group ID']);
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

// Fetch messages with vote counts
$getMessagesQuery = "
    SELECT 
        gc.*,
        COALESCE(upvotes.count, 0) as upvotes,
        COALESCE(downvotes.count, 0) as downvotes
    FROM group_chat gc
    LEFT JOIN (
        SELECT message_id, COUNT(*) as count 
        FROM message_votes 
        WHERE vote_type = 'upvote' 
        GROUP BY message_id
    ) upvotes ON gc.id = upvotes.message_id
    LEFT JOIN (
        SELECT message_id, COUNT(*) as count 
        FROM message_votes 
        WHERE vote_type = 'downvote' 
        GROUP BY message_id
    ) downvotes ON gc.id = downvotes.message_id
    WHERE gc.group_id = ? 
    ORDER BY gc.created_at ASC
";

$stmt = $conn->prepare($getMessagesQuery);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'user_email' => htmlspecialchars($row['user_email']),
        'message' => htmlspecialchars($row['message']),
        'created_at' => $row['created_at'],
        'upvotes' => intval($row['upvotes']),
        'downvotes' => intval($row['downvotes'])
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'messages' => $messages
]);

$conn->close();
?>
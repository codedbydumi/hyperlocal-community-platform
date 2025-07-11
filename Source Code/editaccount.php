<?php
// Start the session
session_start();

// Database connection
include 'db.php'; // Assuming this is the connection file

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to login page
    header("Location: index.php");
    exit();
}

// Get user details
$username = $_SESSION['username']; // Assuming you store the username in the session
$query = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($query);

// Check if the user exists in the database
if ($result->num_rows > 0) {
    // Fetch the user data
    $user = $result->fetch_assoc();
} else {
    echo "Error: User not found.";
    exit();
}

// Handle form submission to update user details
if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the email is already registered by another user
    $email_check = "SELECT * FROM users WHERE email='$email' AND username != '$username'";
    $email_result = $conn->query($email_check);

    if ($email_result->num_rows > 0) {
        echo "Error: This email is already registered by another user.";
    } elseif ($password !== $confirm_password) {
        echo "Error: Passwords do not match.";
    } else {
        // Hash the password if it's being updated
        if (!empty($password)) {
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $update_query = "UPDATE users SET username='$username', email='$email', password='$password_hashed' WHERE username='$username'";
        } else {
            // If the password is not updated, exclude the password field from the query
            $update_query = "UPDATE users SET username='$username', email='$email' WHERE username='$username'";
        }

        if ($conn->query($update_query) === TRUE) {
            echo "Account updated successfully!";
            // Optionally, you can refresh the session variable if the username or email changes
            $_SESSION['username'] = $username;
            header("Location: userdashboard.php"); // Redirect to the dashboard or another page
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
    <link rel="stylesheet" href="css/c.css">
</head>
<body>

    <div class="container">
        <h2>Edit Account</h2>
        <form action="editaccount.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave empty if not changing):</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit" name="update">Update Account</button>
        </form>
        <p><a href="userdashboard.php">Back to Dashboard</a></p>
    </div>

    <script src="scr/app.js"></script>
</body>
</html>

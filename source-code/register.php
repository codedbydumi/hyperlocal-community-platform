<?php
// Database connection

include 'db.php'; // Assuming this is the connection file

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $email_check = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($email_check);

    if ($result->num_rows > 0) {
        echo "Error: This email is already registered. Please choose a different one.";
    } else {
        $password_hashed = password_hash($password, PASSWORD_BCRYPT); 

        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password_hashed')";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
            header("Location: login.php"); 
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
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
    <title>Register</title>
    <link rel="stylesheet" href="css/mo.css">
</head>
<body>

    <div class="container">
        <h2>Register</h2>
        <form id="register-form" action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script src="scr/app.js"></script>
</body>
</html>

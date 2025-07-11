<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    die("You must be logged in to place an order.");
}

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

// Get order details
$productId = $_POST['product_id'];
$userEmail = $_POST['user_email'];
$quantity = $_POST['quantity'];
$phone = $_POST['phone'];
$date = $_POST['date'];

// Insert order into orders table
$sql = "INSERT INTO orders (product_id, user_email, quantity, phone, rent_date) 
        VALUES ('$productId', '$userEmail', '$quantity', '$phone', '$date')";

if ($conn->query($sql) === TRUE) {
    echo "Order placed successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

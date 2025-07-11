<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID parameter exists
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get the image path from the database
    $sql = "SELECT image FROM listings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();
    
    if ($imagePath) {
        // Check if it's a file path or BLOB data
        if (file_exists($imagePath)) {
            // It's a file path, serve the image
            $imageInfo = getimagesize($imagePath);
            header("Content-Type: " . $imageInfo['mime']);
            readfile($imagePath);
        } else {
            // It might be BLOB data stored in the database
            // Display a default image
            header("Location: default_image.jpg");
        }
    } else {
        // No image found, display default
        header("Location: default_image.jpg");
    }
} else {
    // No ID parameter, display default
    header("Location: default_image.jpg");
}

$conn->close();
?>
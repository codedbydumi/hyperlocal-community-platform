<?php
session_start(); // Start the session to access cart items

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty.</p>";
    exit;
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";  // Database name
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = '';

// Calculate the total price before form submission
$totalPrice = 0;  // Initialize total price
foreach ($_SESSION['cart'] as $productId => $cartItem) {
    $sql = "SELECT price_per_day FROM listings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $pricePerDay = $product['price_per_day'];
        $quantity = $cartItem['quantity'];
        $totalItemPrice = $pricePerDay * $quantity;
        $totalPrice += $totalItemPrice;
    }
    $stmt->close();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the renter email from the form or session (if logged in)
    $renterEmail = $_POST['renter_email']; // User's email (get from form or session)
    $rentDate = $_POST['rent_date']; // Rent date from the form
    
    // Insert each product from the cart into the sales table
    foreach ($_SESSION['cart'] as $productId => $cartItem) {
        // Fetch product details from the database using prepared statements
        $sql = "SELECT name, price_per_day, item_type, user_email FROM listings WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();

            // Prepare the insert query for the sales table
            $listerEmail = $product['user_email']; // Lister email
            $itemName = $product['name']; // Product name
            $itemType = $product['item_type']; // Product type
            $price = $product['price_per_day']; // Price per day
            $quantity = $cartItem['quantity']; // Quantity of the item
            $totalPriceForItem = $price * $quantity; // Calculate total price for the item

            // Insert into the sales table using prepared statements
            $insertSql = "INSERT INTO sales (renter_email, lister_email, item_name, item_type, price, quantity, rent_date, total_price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssssdssd", $renterEmail, $listerEmail, $itemName, $itemType, $price, $quantity, $rentDate, $totalPriceForItem);
            
            if (!$insertStmt->execute()) {
                echo "Error: " . $insertStmt->error;
            }
            $insertStmt->close();
        }
        $stmt->close();
    }

    // Clear the cart after successful purchase
    unset($_SESSION['cart']);

    // Set success message
    $successMessage = "Your rental has been successfully placed!";

    // Close the database connection
    $conn->close();

    // Redirect to the home page (index.php)
    header("Location: index.php");
    exit;  // Don't forget to call exit after header to stop further execution
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>

<h2>Checkout</h2>

<?php if ($successMessage): ?>
    <div class="response"><?php echo $successMessage; ?></div>
<?php else: ?>
    <!-- Checkout form -->
    <form action="checkout.php" method="POST">
        <label for="renter_email">Your Email:</label>
        <input type="email" id="renter_email" name="renter_email" required>
        
        <label for="rent_date">Rent Date:</label>
        <input type="date" id="rent_date" name="rent_date" required>
        
        <input type="hidden" name="total_price" value="<?php echo $totalPrice; ?>">

        <h3>Your Cart</h3>
        <table>
            <tr><th>Product Name</th><th>Price (USD)</th><th>Quantity</th><th>Total Price</th></tr>

            <?php
            foreach ($_SESSION['cart'] as $productId => $cartItem) {
                $sql = "SELECT name, price_per_day FROM listings WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $productId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $productName = $product['name'];
                    $pricePerDay = $product['price_per_day'];
                    $quantity = $cartItem['quantity'];
                    $totalItemPrice = $pricePerDay * $quantity;

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($productName) . "</td>";
                    echo "<td>$" . number_format($pricePerDay, 2) . "</td>";
                    echo "<td>" . $quantity . "</td>";
                    echo "<td>$" . number_format($totalItemPrice, 2) . "</td>";
                    echo "</tr>";
                }
                $stmt->close();
            }
            ?>

            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td>$<?php echo number_format($totalPrice, 2); ?></td>
            </tr>
        </table>

        <button type="submit">Confirm and Checkout</button>
    </form>

<?php endif; ?>

</body>
</html>
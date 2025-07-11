<?php
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

if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}


// Initialize filter variables
$districtFilter = isset($_GET['district']) ? $_GET['district'] : '';
$cityFilter = isset($_GET['city']) ? $_GET['city'] : '';
$suburbFilter = isset($_GET['suburb']) ? $_GET['suburb'] : '';
$itemTypeFilter = isset($_GET['item_type']) ? $_GET['item_type'] : ''; // Fix: Initialize the item_type filter

// Build the SQL query based on filter parameters
$sql = "SELECT * FROM listings WHERE 1=1";

if (!empty($districtFilter)) {
    $sql .= " AND district = '" . $conn->real_escape_string($districtFilter) . "'";
}

if (!empty($cityFilter)) {
    $sql .= " AND city = '" . $conn->real_escape_string($cityFilter) . "'";
}

if (!empty($suburbFilter)) {
    $sql .= " AND suburb = '" . $conn->real_escape_string($suburbFilter) . "'";
}

if (!empty($itemTypeFilter)) {  // Add filter for item type
    $sql .= " AND item_type = '" . $conn->real_escape_string($itemTypeFilter) . "'";
}

$result = $conn->query($sql);

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])) {
    $listingId = $_POST['listing_id'];
    $userId = $_SESSION['user_id'];
    $phoneNumber = $_POST['phone_number'];
    $quantity = $_POST['quantity'];
    $rentalDuration = $_POST['rental_duration']; // Assuming it's a date range or number of days
    
    // Get user's email from session (assuming it's stored in session)
    $userEmail = $_SESSION['email'];

    // Insert order into database (you should sanitize inputs here)
    $sqlOrder = "INSERT INTO orders (listing_id, user_id, email, phone_number, quantity, rental_duration) 
                 VALUES ('$listingId', '$userId', '$userEmail', '$phoneNumber', '$quantity', '$rentalDuration')";
    if ($conn->query($sqlOrder) === TRUE) {
        echo "<p>Order placed successfully!</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Listings</title>
    <link rel="stylesheet" href="css/explore.css">
</head>
<body>

<!-- Sidebar with Filter Form -->
<div class="sidebar">
    <a href="index.php">back</a>
    <h2>Filter Listings</h2>
    <form method="GET" action="">

        <!-- Item or Service Filter -->
        <div class="form-group">
            <label for="item_type">Select Listing Type</label>
            <select id="item_type" name="item_type">
                <option value="">--All Types--</option>
                <option value="item" <?php if ($itemTypeFilter == 'item') echo 'selected'; ?>>Item</option>
                <option value="service" <?php if ($itemTypeFilter == 'service') echo 'selected'; ?>>Service</option>
            </select>
        </div>

        <div class="form-group">
            <label for="district">Select District</label>
            <select id="district" name="district" onchange="updateCities()">
                <option value="">--All Districts--</option>
                <option value="gampaha" <?php if ($districtFilter == 'gampaha') echo 'selected'; ?>>Gampaha</option>
                <option value="colombo" <?php if ($districtFilter == 'colombo') echo 'selected'; ?>>Colombo</option>
            </select>
        </div>

        <!-- Dynamic Cities based on District -->
        <div class="form-group" id="city-group">
            <label for="city">Select City</label>
            <select id="city" name="city" onchange="updateSuburbs()">
                <option value="">--All Cities--</option>
                <?php
                if ($districtFilter == 'gampaha') {
                    echo '<option value="gampaha" '.($cityFilter == 'gampaha' ? 'selected' : '').'>Gampaha</option>';
                    echo '<option value="negombo" '.($cityFilter == 'negombo' ? 'selected' : '').'>Negombo</option>';
                    echo '<option value="kelaniya" '.($cityFilter == 'kelaniya' ? 'selected' : '').'>Kelaniya</option>';
                } elseif ($districtFilter == 'colombo') {
                    echo '<option value="colombo" '.($cityFilter == 'colombo' ? 'selected' : '').'>Colombo</option>';
                    echo '<option value="mount_lavinia" '.($cityFilter == 'mount_lavinia' ? 'selected' : '').'>Mount Lavinia</option>';
                    echo '<option value="moratuwa" '.($cityFilter == 'moratuwa' ? 'selected' : '').'>Moratuwa</option>';
                }
                ?>
            </select>
        </div>

        <!-- Dynamic Suburbs based on City -->
        <div class="form-group" id="suburb-group">
            <label for="suburb">Select Suburb</label>
            <select id="suburb" name="suburb">
                <option value="">--All Suburbs--</option>
                <?php
                // Similar suburb population as before
                ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit">Filter Listings</button>
        </div>
    </form>
</div>

<!-- Main Content for Listings -->
<div class="main-content">
    <h2>Available Listings</h2>

    <!-- Listings Table -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Item/Service</th>
                <th>Price (USD)</th>
                <th>Quantity</th>
                <th>Location</th>
                <th>Order</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . ucfirst($row['item_type']) . "</td>";
                    echo "<td>" . number_format($row['price_per_day'], 2) . "</td>";
                    echo "<td>" . ($row['quantity'] ? $row['quantity'] : 'N/A') . "</td>";
                    
                    // Format location data correctly
                    $location = [];
                    if (!empty($row['suburb'])) $location[] = htmlspecialchars($row['suburb']);
                    if (!empty($row['city'])) $location[] = htmlspecialchars(str_replace('_', ' ', $row['city']));
                    if (!empty($row['district'])) $location[] = htmlspecialchars(ucfirst($row['district']));
                    
                    echo "<td>" . implode(", ", $location) . "</td>";
                    echo "<td><form method='POST' action=''>
                              <input type='hidden' name='listing_id' value='" . $row['id'] . "'>
                              <button type='submit' name='order'>Order</button>
                          </form></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No listings found for your search.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal for Order Form -->
<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])): ?>
    <div class="order-modal">
        <h3>Place Your Order</h3>
        <form method="POST" action="">
            <input type="hidden" name="listing_id" value="<?php echo $_POST['listing_id']; ?>">
            
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required>
            
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" required>
            
            <label for="rental_duration">Rental Duration:</label>
            <input type="date" id="rental_duration" name="rental_duration" required>
            
            <button type="submit" name="order">Place Order</button>
        </form>
    </div>
<?php endif; ?>

<script src="js/explore.js"></script>

</body>
</html>

<?php
// Close connection
$conn->close();
?>

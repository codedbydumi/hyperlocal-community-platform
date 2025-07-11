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

// Session start to check if user is logged in
session_start();
$logged_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$logged_user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get listing ID from URL
$listing_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($listing_id <= 0) {
    die("Invalid listing ID");
}

// Fetch listing details
$sql = "SELECT * FROM listings WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Listing not found");
}

$listing = $result->fetch_assoc();

// Format location data
$location = [];
if (!empty($listing['suburb'])) $location[] = htmlspecialchars($listing['suburb']);
if (!empty($listing['city'])) $location[] = htmlspecialchars(str_replace('_', ' ', $listing['city']));
if (!empty($listing['district'])) $location[] = htmlspecialchars(ucfirst($listing['district']));
$location_str = implode(", ", $location);

// Get owner details
$owner = null;
if (!empty($listing['user_id'])) {
    $owner_sql = "SELECT * FROM users WHERE id = ?";
    $owner_stmt = $conn->prepare($owner_sql);
    
    if (!$owner_stmt) {
        error_log("Error preparing owner statement: " . $conn->error);
    } else {
        $owner_stmt->bind_param("i", $listing['user_id']);
        $owner_stmt->execute();
        $owner_result = $owner_stmt->get_result();
        $owner = $owner_result->num_rows > 0 ? $owner_result->fetch_assoc() : null;
        $owner_stmt->close();
    }
}

// Handle rental form submission
$rental_success = false;
$rental_error = '';
$rental_id = 0;
$start_date = '';
$end_date = '';
$delivery_method = '';
$total_price = 0;
$renter_email = '';
$phone_number = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rent_now'])) {
    $renter_email = $logged_user_email;
    
    if (empty($renter_email) && !empty($_POST['renter_email'])) {
        $renter_email = trim($_POST['renter_email']);
    }
    
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $rental_time = trim($_POST['rental_time']);
    $phone_number = trim($_POST['phone_number']);
    $delivery_method = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : '';
    
    // Validation
    if (empty($renter_email)) {
        $rental_error = 'Please provide your email';
    } elseif (!filter_var($renter_email, FILTER_VALIDATE_EMAIL)) {
        $rental_error = 'Please enter a valid email address';
    } elseif (empty($start_date)) {
        $rental_error = 'Please select a start date';
    } elseif (empty($end_date)) {
        $rental_error = 'Please select an end date';
    } elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $rental_error = 'Start date cannot be in the past';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $rental_error = 'End date must be after start date';
    } elseif (empty($rental_time)) {
        $rental_error = 'Please select a rental time';
    } elseif (empty($phone_number)) {
        $rental_error = 'Please provide your phone number';
    } elseif (empty($delivery_method)) {
        $rental_error = 'Please select pickup or delivery';
    } else {
        // Calculate rental duration in days
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $rental_days = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24)) + 1;
        
        // Calculate total price
        $total_price = $listing['price_per_day'] * $rental_days;
        
        // Debug: Print the listing data to see what columns exist
        error_log("Listing data: " . print_r($listing, true));
        
        // Get lister's email - check multiple possible column names
        $lister_email = '';
        
        // Try different possible column names for lister email in listings table
        if (isset($listing['lister_email']) && !empty($listing['lister_email'])) {
            $lister_email = $listing['lister_email'];
        } elseif (isset($listing['email']) && !empty($listing['email'])) {
            $lister_email = $listing['email'];
        } elseif (isset($listing['user_email']) && !empty($listing['user_email'])) {
            $lister_email = $listing['user_email'];
        } elseif (isset($listing['owner_email']) && !empty($listing['owner_email'])) {
            $lister_email = $listing['owner_email'];
        }
        
        // If still no email found, get it from users table using user_id
        if (empty($lister_email) && !empty($listing['user_id'])) {
            $lister_sql = "SELECT email FROM users WHERE id = ?";
            $lister_stmt = $conn->prepare($lister_sql);
            if ($lister_stmt) {
                $lister_stmt->bind_param("i", $listing['user_id']);
                $lister_stmt->execute();
                $lister_result = $lister_stmt->get_result();
                if ($lister_result->num_rows > 0) {
                    $lister = $lister_result->fetch_assoc();
                    $lister_email = $lister['email'];
                }
                $lister_stmt->close();
            }
        }
        
        // Debug: Check what lister_email we got
        error_log("Lister email retrieved: " . $lister_email);
        
        // Validate that we have a lister email
        if (empty($lister_email)) {
            $rental_error = 'Unable to find lister email. Please contact support.';
        } else {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Debug: Log the values being inserted
                error_log("Inserting into sales - Renter: $renter_email, Lister: $lister_email");
                
                // Insert rental information into the sales table
                $rental_sql = "INSERT INTO sales (renter_email, lister_email, item_name, item_type, price, rent_date, start_date, end_date, rental_time, phone_number, delivery_method, total_price, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
                $rental_stmt = $conn->prepare($rental_sql);
                
                if (!$rental_stmt) {
                    throw new Exception('Database error: ' . $conn->error);
                }
                
                $rent_date = date('Y-m-d');
                
                $rental_stmt->bind_param("ssssdssssssd", 
                    $renter_email, 
                    $lister_email, 
                    $listing['name'], 
                    $listing['item_type'], 
                    $listing['price_per_day'], 
                    $rent_date, 
                    $start_date,
                    $end_date,
                    $rental_time,
                    $phone_number,
                    $delivery_method,
                    $total_price
                );
                
                if (!$rental_stmt->execute()) {
                    throw new Exception('Failed to process rental: ' . $rental_stmt->error);
                }
                
                $rental_id = $rental_stmt->insert_id;
                error_log("Sales record created with ID: " . $rental_id);
                $rental_stmt->close();
                
                // Debug: Log the values being inserted into order_confirmations
                error_log("Inserting into order_confirmations - Sale ID: $rental_id, Renter: $renter_email, Lister: $lister_email");
                
                // Insert into order_confirmations table
                $confirm_sql = "INSERT INTO order_confirmations (sale_id, renter_email, lister_email, item_name, delivery_method, status) 
                              VALUES (?, ?, ?, ?, ?, 'pending')";
                $confirm_stmt = $conn->prepare($confirm_sql);
                
                if (!$confirm_stmt) {
                    throw new Exception('Database error: ' . $conn->error);
                }
                
                $confirm_stmt->bind_param("issss", $rental_id, $renter_email, $lister_email, $listing['name'], $delivery_method);
                
                if (!$confirm_stmt->execute()) {
                    throw new Exception('Failed to create order confirmation: ' . $confirm_stmt->error);
                }
                
                error_log("Order confirmation created successfully");
                $confirm_stmt->close();
                
                // Commit transaction
                $conn->commit();
                $rental_success = true;
                
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                $rental_error = $e->getMessage();
                error_log("Transaction failed: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing['name']); ?> - Details</title>
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #166088;
            --accent-color: #4fc3dc;
            --background-color: #f5f7fa;
            --text-color: #333;
            --light-gray: #eaeef2;
            --dark-gray: #666;
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        .back-link:before {
            content: "←";
            margin-right: 5px;
        }

        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-details {
            padding: 30px;
        }

        .product-type {
            display: inline-block;
            background-color: var(--light-gray);
            color: var(--secondary-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 15px;
            text-transform: capitalize;
        }

        .product-title {
            font-size: 28px;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .product-info {
            margin-bottom: 25px;
        }

        .info-item {
            display: flex;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: 600;
            width: 120px;
            color: var(--dark-gray);
        }

        .info-value {
            flex: 1;
        }

        .product-description {
            margin-bottom: 25px;
            color: var(--text-color);
            line-height: 1.7;
        }

        .cta-button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            background-color: var(--secondary-color);
        }

        .cta-button.secondary {
            background-color: var(--light-gray);
            color: var(--text-color);
        }

        .cta-button.secondary:hover {
            background-color: #d8dde4;
        }

        .owner-section {
            margin-top: 40px;
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--text-color);
            position: relative;
            padding-bottom: 10px;
        }

        .section-title:after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--accent-color);
        }

        .owner-info {
            display: flex;
            align-items: center;
        }

        .owner-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--light-gray);
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary-color);
        }

        .owner-details h4 {
            margin-bottom: 5px;
            font-size: 18px;
        }

        .contact-info {
            color: var(--dark-gray);
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            padding: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .modal-close:hover {
            transform: scale(1.1);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 111, 165, 0.2);
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: var(--success-color);
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: var(--error-color);
        }

        .rental-summary {
            background-color: var(--light-gray);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .rental-summary h4 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .rental-summary p {
            margin-bottom: 5px;
        }

        .price-calculation {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .price-calculation h5 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .price-total {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 18px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            color: white;
        }

        .status-pending {
            background-color: var(--warning-color);
        }

        .status-confirmed {
            background-color: var(--success-color);
        }

        .status-cancelled {
            background-color: var(--error-color);
        }

        .delivery-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .delivery-option {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .delivery-option:hover {
            border-color: var(--primary-color);
        }

        .delivery-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(74, 111, 165, 0.1);
        }

        .delivery-option input[type="radio"] {
            margin-bottom: 8px;
        }

        .delivery-option .option-icon {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }

        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                height: 300px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .delivery-options {
                grid-template-columns: 1fr;
            }
            
            .modal {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">RentNow</a>
        </div>
    </header>

    <div class="container">
        <a href="explore.php" class="back-link">Back to listings</a>
        
        <div class="product-container">
            <div class="image-container">
                <?php if ($listing['image']): ?>
                    <img src="image.php?id=<?php echo $listing['id']; ?>" alt="<?php echo htmlspecialchars($listing['name']); ?>" class="product-image">
                <?php else: ?>
                    <div class="product-image" style="background-color: var(--light-gray); display: flex; align-items: center; justify-content: center;">
                        <span style="color: var(--dark-gray);">No image available</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-details">
                <span class="product-type"><?php echo ucfirst($listing['item_type']); ?></span>
                <h1 class="product-title"><?php echo htmlspecialchars($listing['name']); ?></h1>
                <div class="product-price">$<?php echo number_format($listing['price_per_day'], 2); ?> per day</div>
                
                <div class="product-info">
                    <div class="info-item">
                        <div class="info-label">Location:</div>
                        <div class="info-value"><?php echo $location_str; ?></div>
                    </div>
                    
                    <?php if (!empty($listing['condition'])): ?>
                    <div class="info-item">
                        <div class="info-label">Condition:</div>
                        <div class="info-value"><?php echo htmlspecialchars(ucfirst($listing['condition'])); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($listing['availability'])): ?>
                    <div class="info-item">
                        <div class="info-label">Availability:</div>
                        <div class="info-value"><?php echo htmlspecialchars($listing['availability']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($listing['description'])): ?>
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="product-actions">
                    <button type="button" class="cta-button" id="rentNowBtn">Rent Now</button>
                </div>
            </div>
        </div>
        
        <?php if ($owner): ?>
        <div class="owner-section">
            <h3 class="section-title">About the Owner</h3>
            <div class="owner-info">
                <div class="owner-avatar">
                    <?php echo strtoupper(substr($owner['username'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="owner-details">
                    <h4><?php echo htmlspecialchars($owner['username'] ?? 'Unknown'); ?></h4>
                    <?php if (!empty($owner['email'])): ?>
                    <div class="contact-info">
                        <span>Member since: <?php echo isset($owner['created_at']) ? date('F Y', strtotime($owner['created_at'])) : 'Unknown'; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Rent Now Modal -->
    <div class="modal-overlay" id="rentModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Rent: <?php echo htmlspecialchars($listing['name']); ?></h3>
                <button type="button" class="modal-close" id="closeRentModal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if ($rental_success): ?>
                    <div class="alert alert-success">
                        <strong>Success!</strong> Your rental request has been successfully processed!
                    </div>
                    
                    <div class="rental-summary">
                        <h4>Rental Confirmation</h4>
                        <p><strong>Rental ID:</strong> #<?php echo $rental_id; ?></p>
                        <p><strong>Item:</strong> <?php echo htmlspecialchars($listing['name']); ?></p>
                        <p><strong>Rental Period:</strong> <?php echo date('M j, Y', strtotime($start_date)) . ' - ' . date('M j, Y', strtotime($end_date)); ?></p>
                        <p><strong>Delivery Method:</strong> <?php echo ucfirst($delivery_method); ?></p>
                        <p><strong>Total Price:</strong> $<?php echo number_format($total_price, 2); ?></p>
                      
                    </div>
                <center><p><strong></strong> <span class="status-badge status-pending">Pending </span></p></center>
                    <br>
                    <p>&nbsp;The owner will contact you at <strong><?php echo htmlspecialchars($renter_email); ?></strong> or &nbsp;<strong><?php echo htmlspecialchars($phone_number); ?></strong>&nbsp;to confirm the order and arrange <?php echo $delivery_method; ?> details.</p>
                    
                <?php elseif ($rental_error): ?>
                    <div class="alert alert-error">
                        <strong>Error:</strong> <?php echo htmlspecialchars($rental_error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$rental_success): ?>
                    <div class="rental-summary">
                        <h4>Rental Details</h4>
                        <p><strong>Item:</strong> <?php echo htmlspecialchars($listing['name']); ?></p>
                        <p><strong>Price:</strong> $<?php echo number_format($listing['price_per_day'], 2); ?> per day</p>
                    </div>
                    
                    <form method="post" action="" id="rentalForm">
                        <div class="form-group">
                            <label for="renter_email">Your Email *</label>
                            <input type="email" class="form-control" id="renter_email" name="renter_email" 
                                   value="<?php echo htmlspecialchars($logged_user_email); ?>" 
                                   <?php echo !empty($logged_user_email) ? 'readonly' : 'required'; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone_number">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                   value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>" 
                                   required placeholder="Enter your phone number">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>" 
                                       required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>" 
                                       required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="rental_time">Preferred Time *</label>
                            <input type="time" class="form-control" id="rental_time" name="rental_time" 
                                   value="<?php echo isset($_POST['rental_time']) ? $_POST['rental_time'] : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Delivery Method *</label>
                            <div class="delivery-options">
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_method" value="pickup" required>
                                    <span class="option-icon">🚗</span>
                                    <span>Pickup</span>
                                </label>
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_method" value="delivery" required>
                                    <span class="option-icon">📦</span>
                                    <span>Delivery</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="price-calculation" id="priceCalculation" style="display: none;">
                            <h5>Price Calculation</h5>
                            <div class="price-row">
                                <span>Price per day:</span>
                                <span>$<?php echo number_format($listing['price_per_day'], 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Number of days:</span>
                                <span id="calcDays">1</span>
                            </div>
                            <div class="price-row price-total">
                                <span>Total Price:</span>
                                <span id="calcTotal">$<?php echo number_format($listing['price_per_day'], 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="cta-button secondary" id="cancelRental">Cancel</button>
                            <button type="submit" name="rent_now" class="cta-button">Confirm Rental</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <?php if ($rental_success): ?>
                <div class="modal-footer">
                    <button type="button" class="cta-button" id="closeSuccessBtn">Close</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const rentModal = document.getElementById('rentModal');
        const rentBtn = document.getElementById('rentNowBtn');
        const closeRentBtn = document.getElementById('closeRentModal');
        const closeSuccessBtn = document.getElementById('closeSuccessBtn');
        const cancelRentalBtn = document.getElementById('cancelRental');

        // Price calculation elements
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const priceCalculation = document.getElementById('priceCalculation');
        const calcDays = document.getElementById('calcDays');
        const calcTotal = document.getElementById('calcTotal');
        
        const pricePerDay = <?php echo $listing['price_per_day']; ?>;

        // Open modal
        rentBtn.addEventListener('click', function() {
            rentModal.style.display = 'flex';
        });

        // Close modal handlers
        if (closeRentBtn) {
            closeRentBtn.addEventListener('click', function() {
                rentModal.style.display = 'none';
            });
        }

        if (closeSuccessBtn) {
            closeSuccessBtn.addEventListener('click', function() {
                rentModal.style.display = 'none';
            });
        }

        if (cancelRentalBtn) {
            cancelRentalBtn.addEventListener('click', function() {
                rentModal.style.display = 'none';
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === rentModal) {
                rentModal.style.display = 'none';
            }
        });

        // Price calculation function
        function calculatePrice() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end >= start) {
                    const timeDiff = end.getTime() - start.getTime();
                    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                    
                    const totalPrice = pricePerDay * daysDiff;
                    
                    // Update calculation display
                    calcDays.textContent = daysDiff;
                    calcTotal.textContent = '$' + totalPrice.toFixed(2);
                    
                    priceCalculation.style.display = 'block';
                } else {
                    priceCalculation.style.display = 'none';
                }
            } else {
                priceCalculation.style.display = 'none';
            }
        }

        // Add event listeners for price calculation
        if (startDateInput) {
            startDateInput.addEventListener('change', calculatePrice);
        }
        if (endDateInput) {
            endDateInput.addEventListener('change', calculatePrice);
            
            // Update end date minimum when start date changes
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
                calculatePrice();
            });
        }

        // Form validation
        const rentalForm = document.getElementById('rentalForm');
        if (rentalForm) {
            rentalForm.addEventListener('submit', function(e) {
                const phoneNumber = document.getElementById('phone_number').value.trim();
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const rentalTime = document.getElementById('rental_time').value;
                const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked');

                // Basic validation
                if (!phoneNumber) {
                    alert('Please enter your phone number');
                    e.preventDefault();
                    return;
                }

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    e.preventDefault();
                    return;
                }

                if (new Date(endDate) < new Date(startDate)) {
                    alert('End date must be after start date');
                    e.preventDefault();
                    return;
                }

                if (new Date(startDate) < new Date()) {
                    alert('Start date cannot be in the past');
                    e.preventDefault();
                    return;
                }

                if (!rentalTime) {
                    alert('Please select a rental time');
                    e.preventDefault();
                    return;
                }

                if (!deliveryMethod) {
                    alert('Please select a delivery method');
                    e.preventDefault();
                    return;
                }
            });
        }

        // Show modal if there was a form submission
        <?php if ($rental_success || $rental_error): ?>
        window.onload = function() {
            rentModal.style.display = 'flex';
            <?php if (!$rental_success && !$rental_error): ?>
            calculatePrice();
            <?php endif; ?>
        };
        <?php endif; ?>

        // Initialize price calculation on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (startDateInput && endDateInput) {
                calculatePrice();
            }

            // Delivery option selection
            const deliveryOptions = document.querySelectorAll('.delivery-option');
            deliveryOptions.forEach(option => {
                option.addEventListener('click', function() {
                    deliveryOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close connection
$stmt->close();
$conn->close();
?>
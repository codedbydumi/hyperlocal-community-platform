<?php
// Start the session
session_start();

// Include database connection
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Get the renter email from session
$renter_email = $_SESSION['email'];

// Updated query to JOIN sales with listings table to get image and other listing details
$sql = "SELECT s.*, l.image, l.description, l.city, l.district 
        FROM sales s 
        LEFT JOIN listings l ON s.item_name = l.name 
        WHERE s.renter_email = ? 
        ORDER BY s.rent_date DESC";

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $renter_email);
$stmt->execute();
$result = $stmt->get_result();

// Check if query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Rented Items</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .back-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        h2 {
            color: #333;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .item-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            position: relative;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
            text-transform: uppercase;
        }

        .status-pending {
            background: linear-gradient(45deg, #ffc107, #ff8f00);
            color: white;
        }

        .status-confirmed {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .status-delivered {
            background: linear-gradient(45deg, #17a2b8, #007bff);
            color: white;
        }

        .status-completed {
            background: linear-gradient(45deg, #6f42c1, #e83e8c);
            color: white;
        }

        .status-cancelled {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
        }

        /* Add styles for additional status types that might exist */
        .status-approved {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
        }

        .status-returned {
            background: linear-gradient(45deg, #6f42c1, #e83e8c);
            color: white;
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
            position: relative;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            color: #6c757d;
            font-size: 14px;
            height: 200px;
        }

        .item-content {
            padding: 20px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .item-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .item-type {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .price-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .price-tag {
            font-size: 1.3rem;
            font-weight: 700;
            color: #28a745;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #dc3545;
        }

        .price-tag span, .total-price span {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 400;
        }

        .item-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 0.95rem;
            color: #333;
            font-weight: 500;
        }

        .rental-info {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }

        .rental-dates {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .date-item {
            text-align: center;
        }

        .date-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 600;
        }

        .date-value {
            font-size: 0.9rem;
            color: #333;
            font-weight: 700;
        }

        .location {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            color: #667eea;
            font-weight: 500;
        }

        .description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.5;
        }

        .no-items {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-items h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #495057;
        }

        .no-items p {
            font-size: 1.1rem;
        }

        /* Debug information - remove in production */
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 0.85rem;
            color: #495057;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }

            h2 {
                font-size: 2rem;
            }

            .items-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .price-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .rental-dates {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Your Rented Items</h2>
        <a href="userdashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="items-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="item-card">
                    <?php 
                    // Get the actual status value from database
                    $rawStatus = $row['status'] ?? 'pending';
                    
                    // Clean and normalize the status
                    $status = strtolower(trim($rawStatus));
                    
                    // Map common status variations to consistent display
                    $statusMapping = [
                        'pending' => 'pending',
                        'confirmed' => 'confirmed',
                        'approved' => 'confirmed',
                        'delivered' => 'delivered',
                        'completed' => 'completed',
                        'finished' => 'completed',
                        'returned' => 'completed',
                        'cancelled' => 'cancelled',
                        'canceled' => 'cancelled',
                        'rejected' => 'cancelled'
                    ];
                    
                    // Use mapped status or fallback to original
                    $normalizedStatus = $statusMapping[$status] ?? $status;
                    $statusClass = 'status-' . $normalizedStatus;
                    $statusText = ucfirst($rawStatus); // Show original status text
                    ?>
                    
                    <!-- Debug info - remove this in production -->
                    <!--
                    <div class="debug-info">
                        Raw Status: <?php echo htmlspecialchars($rawStatus); ?><br>
                        Normalized: <?php echo htmlspecialchars($normalizedStatus); ?><br>
                        Class: <?php echo htmlspecialchars($statusClass); ?>
                    </div>
                    -->
                    
                    <div class="status-badge <?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($statusText); ?>
                    </div>
                    
                    <div class="item-image">
                        <?php 
                        // Check if we have image data in sales table or need to fetch from listings
                        $imagePath = '';
                        if (!empty($row['image'])) {
                            $imagePath = $row['image'];
                        } elseif (!empty($row['image_path'])) {
                            $imagePath = $row['image_path'];
                        }
                        
                        if (!empty($imagePath)): ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                 alt="<?php echo htmlspecialchars($row['item_name']); ?>"
                                 onerror="this.parentElement.innerHTML='<div class=\'no-image\'>No Image Available</div>'">
                        <?php else: ?>
                            <div class="no-image">No Image Available</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-content">
                        <div class="item-header">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($row['item_name']); ?></div>
                                <div class="item-type"><?php echo htmlspecialchars($row['item_type']); ?></div>
                            </div>
                        </div>
                        
                        <div class="price-section">
                            <div class="price-tag">
                                $ <?php echo number_format($row['price'], 2); ?>
                                <span>/day</span>
                            </div>
                            <div class="total-price">
                                $ <?php echo number_format($row['total_price'], 2); ?>
                                <span>total</span>
                            </div>
                        </div>
                        
                        <div class="rental-info">
                            <div class="rental-dates">
                                <div class="date-item">
                                    <div class="date-label">Rental Date</div>
                                    <div class="date-value"><?php echo date('M d, Y', strtotime($row['rent_date'])); ?></div>
                                </div>
                                <?php if (!empty($row['return_date'])): ?>
                                <div class="date-item">
                                    <div class="date-label">Return Date</div>
                                    <div class="date-value"><?php echo date('M d, Y', strtotime($row['return_date'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="item-details">
                            <div class="detail-item">
                                <span class="detail-label">Quantity</span>
                                <span class="detail-value"><?php echo htmlspecialchars($row['quantity']); ?> items</span>
                            </div>
                            <?php if (!empty($row['rental_days'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Duration</span>
                                <span class="detail-value"><?php echo htmlspecialchars($row['rental_days']); ?> days</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php 
                        // Only show location if we have the data (from joined listings table)
                        if (!empty($row['city']) && !empty($row['district'])): ?>
                        <div class="location">
                            📍 <?php echo htmlspecialchars($row['city']); ?>, <?php echo htmlspecialchars($row['district']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        // Only show description if we have it (from joined listings table)
                        if (!empty($row['description'])): ?>
                            <div class="description">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['notes'])): ?>
                            <div class="description">
                                <strong>Rental Notes:</strong><br>
                                <?php echo htmlspecialchars($row['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-items">
            <h3>No Rented Items Found</h3>
            <p>You haven't rented any items yet!</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
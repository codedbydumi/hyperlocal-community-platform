<?php
// Start the session at the beginning of the file
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, redirect to index.php
    header("Location: index.php");
    exit(); // Stop further script execution
}

// Database connection
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

// Get the logged-in user's email
$userEmail = $_SESSION['email'];

// Handle order actions (confirm/cancel)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];
    
    // Start transaction to ensure both tables are updated together
    $conn->begin_transaction();
    
    try {
        if ($action == 'confirm') {
            // Update order_confirmations table
            $sql1 = "UPDATE order_confirmations SET status='confirmed' WHERE id=? AND lister_email=?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("is", $orderId, $userEmail);
            $stmt1->execute();
            
            // Get the sale_id from order_confirmations to update sales table
            $sql_get_sale = "SELECT sale_id FROM order_confirmations WHERE id=? AND lister_email=?";
            $stmt_get_sale = $conn->prepare($sql_get_sale);
            $stmt_get_sale->bind_param("is", $orderId, $userEmail);
            $stmt_get_sale->execute();
            $result_sale = $stmt_get_sale->get_result();
            
            if ($row = $result_sale->fetch_assoc()) {
                $saleId = $row['sale_id'];
                
                // Update sales table
                $sql2 = "UPDATE sales SET status='confirmed' WHERE id=?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("i", $saleId);
                $stmt2->execute();
                
                if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0) {
                    $conn->commit();
                    $successMessage = "Order successfully confirmed and updated in both systems";
                } else {
                    $conn->rollback();
                    $errorMessage = "Failed to update order status";
                }
                
                $stmt2->close();
            } else {
                $conn->rollback();
                $errorMessage = "Failed to find corresponding sale record";
            }
            
            $stmt_get_sale->close();
            $stmt1->close();
            
        } elseif ($action == 'cancel') {
            // Update order_confirmations table
            $sql1 = "UPDATE order_confirmations SET status='cancelled' WHERE id=? AND lister_email=?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("is", $orderId, $userEmail);
            $stmt1->execute();
            
            // Get the sale_id from order_confirmations to update sales table
            $sql_get_sale = "SELECT sale_id FROM order_confirmations WHERE id=? AND lister_email=?";
            $stmt_get_sale = $conn->prepare($sql_get_sale);
            $stmt_get_sale->bind_param("is", $orderId, $userEmail);
            $stmt_get_sale->execute();
            $result_sale = $stmt_get_sale->get_result();
            
            if ($row = $result_sale->fetch_assoc()) {
                $saleId = $row['sale_id'];
                
                // Update sales table
                $sql2 = "UPDATE sales SET status='cancelled' WHERE id=?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("i", $saleId);
                $stmt2->execute();
                
                if ($stmt1->affected_rows > 0 || $stmt2->affected_rows > 0) {
                    $conn->commit();
                    $successMessage = "Order successfully cancelled and updated in both systems";
                } else {
                    $conn->rollback();
                    $errorMessage = "Failed to update order status";
                }
                
                $stmt2->close();
            } else {
                $conn->rollback();
                $errorMessage = "Failed to find corresponding sale record";
            }
            
            $stmt_get_sale->close();
            $stmt1->close();
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $errorMessage = "Error updating order status: " . $e->getMessage();
    }
}

// Modified query to fetch all orders for the user's listings
// Join with sales table to get complete order information
$sql = "SELECT 
            oc.id,
            oc.sale_id,
            oc.item_name,
            oc.renter_email,
            oc.lister_email,
            oc.delivery_method,
            oc.status as order_status,
            oc.confirmation_date,
            s.start_date,
            s.end_date,
            s.total_price,
            s.rent_date,
            s.quantity,
            s.rental_time,
            s.status as sale_status
        FROM order_confirmations oc
        LEFT JOIN sales s ON oc.sale_id = s.id
        WHERE oc.lister_email = ?
        ORDER BY oc.confirmation_date DESC, oc.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Debug: Count total orders
$totalOrders = count($orders);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Neighborhood Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        .navbar {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .nav-logo-icon {
            width: 32px;
            height: 32px;
            background: #5865f2;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .search-bar {
            position: relative;
            margin-left: 2rem;
        }

        .search-input {
            width: 300px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e0e6ed;
            border-radius: 25px;
            background: #f8f9fa;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #5865f2;
            background: white;
            box-shadow: 0 0 0 3px rgba(88, 101, 242, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #8b949e;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #656d76;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 0;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #5865f2;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-icon {
            position: relative;
            color: #656d76;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary-nav {
            background: #5865f2;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary-nav:hover {
            background: #4752c4;
            transform: translateY(-1px);
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #24292f;
        }

        /* Debug Info */
        .debug-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .debug-info h4 {
            color: #1976d2;
            margin-bottom: 0.5rem;
        }

        /* Orders Section */
        .orders-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #24292f;
            margin-bottom: 1.5rem;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .orders-table th, .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
            vertical-align: top;
        }

        .orders-table th {
            font-weight: 600;
            color: #586069;
            background-color: #f6f8fa;
        }

        .orders-table tr:hover {
            background-color: #f6f8fa;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3bf;
            color: #e67700;
        }

        .status-confirmed {
            background-color: #d3f9d8;
            color: #2b8a3e;
        }

        .status-cancelled {
            background-color: #ffe3e3;
            color: #c92a2a;
        }

        /* Status sync indicator */
        .status-sync {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .status-sync small {
            font-size: 0.7rem;
            opacity: 0.8;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-confirm {
            background-color: #40c057;
            color: white;
        }

        .btn-confirm:hover {
            background-color: #37b24d;
        }

        .btn-cancel {
            background-color: #fa5252;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #f03e3e;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #586069;
        }

        .empty-icon {
            font-size: 3rem;
            color: #e1e8ed;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            margin-bottom: 1.5rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background-color: #ebfbee;
            color: #2b8a3e;
            border: 1px solid #d3f9d8;
        }

        .alert-error {
            background-color: #ffebee;
            color: #c92a2a;
            border: 1px solid #ffe3e3;
        }

        /* Order Cards for mobile */
        .order-card {
            display: none;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #5865f2;
        }

        .order-card h4 {
            color: #24292f;
            margin-bottom: 1rem;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .order-detail strong {
            color: #586069;
        }

        /* Footer Styles */
        footer {
            background: #24292f;
            color: #f0f6fc;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 0 2rem;
        }

        .footer-section h4 {
            color: #f0f6fc;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #8b949e;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #58a6ff;
        }

        .footer-bottom {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid #30363d;
            margin-top: 2rem;
            color: #8b949e;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .search-bar {
                margin-left: 0;
                order: 3;
                width: 100%;
            }

            .search-input {
                width: 100%;
            }

            .nav-links {
                gap: 1rem;
            }

            .container {
                padding: 1rem;
            }

            .orders-table {
                display: none;
            }

            .order-card {
                display: block;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="index.php" class="nav-logo">
                    <div class="nav-logo-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    NEIGHBORHOOD
                </a>
                <!-- <div class="search-bar">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search for tools, skills or community help...">
                </div> -->
            </div>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="explore.php">Explore</a></li>
                    <li><a href="viewgroups.php">Community</a></li>
                    <li><a href="userdashboard.php" class="active">Dashboard</a></li>
                </ul>
                <div class="user-menu">
                    <a href="notifications.php">     
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge"></span>
                                      </div></a> 
                    <a href="logout.php" class="btn-primary-nav">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Orders</h1>
            </div>

            <!-- Debug Information -->
            <div class="debug-info">
                <h4>Debug Information:</h4>
                <p><strong>Logged in user:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                <p><strong>Total orders found:</strong> <?php echo $totalOrders; ?></p>
                <p><strong>Database connection:</strong> Connected</p>
            </div>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $successMessage; ?>
                </div>
            <?php elseif (isset($errorMessage)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <section class="orders-section">
                <h2 class="section-title">Orders for My Listings (<?php echo $totalOrders; ?> orders)</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="empty-title">No orders yet</h3>
                        <p class="empty-description">When someone rents your items, the orders will appear here.</p>
                        <p><strong>Current user email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                        <p><em>Make sure you have listings that match this email address in the order_confirmations table.</em></p>
                    </div>
                <?php else: ?>
                    <!-- Desktop Table View -->
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Item</th>
                                <th>Renter</th>
                                <th>Delivery</th>
                                <th>Dates</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['sale_id'] ?? 'N/A'); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['item_name'] ?? 'Unknown Item'); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['renter_email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge" style="background: #e3f2fd; color: #1976d2;">
                                            <?php echo ucfirst($order['delivery_method'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($order['start_date']) && !empty($order['end_date'])): ?>
                                            <strong>From:</strong> <?php echo date('M j, Y', strtotime($order['start_date'])); ?><br>
                                            <strong>To:</strong> <?php echo date('M j, Y', strtotime($order['end_date'])); ?>
                                        <?php elseif (!empty($order['rent_date'])): ?>
                                            <strong>Rent Date:</strong> <?php echo date('M j, Y', strtotime($order['rent_date'])); ?>
                                        <?php else: ?>
                                            <em>Date not specified</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong style="color: #2b8a3e;">
                                            $<?php echo number_format($order['total_price'] ?? 0, 2); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span style="background: #f0f8f0; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                            <?php echo htmlspecialchars($order['quantity'] ?? '1'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="status-sync">
                                            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                            <!-- <small>Sales: <?php echo ucfirst($order['sale_status'] ?? 'N/A'); ?></small> -->
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($order['order_status'] == 'pending'): ?>
                                            <div class="action-buttons">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn-action btn-confirm" onclick="return confirm('Confirm this order? This will update both order and sales status.');">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn-action btn-cancel" onclick="return confirm('Cancel this order? This will update both order and sales status.');">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Mobile Card View -->
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <h4><?php echo htmlspecialchars($order['item_name'] ?? 'Unknown Item'); ?></h4>
                            <div class="order-detail">
                                <strong>Order ID:</strong>
                                <span>#<?php echo htmlspecialchars($order['sale_id'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="order-detail">
                                <strong>Renter:</strong>
                                <span><?php echo htmlspecialchars($order['renter_email'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="order-detail">
                                <strong>Price:</strong>
                                <span style="color: #2b8a3e; font-weight: bold;">$<?php echo number_format($order['total_price'] ?? 0, 2); ?></span>
                            </div>
                            <div class="order-detail">
                                <strong>Order Status:</strong>
                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                            <div class="order-detail">
                                <strong>Sales Status:</strong>
                                <span class="status-badge status-<?php echo $order['sale_status'] ?? 'pending'; ?>">
                                    <?php echo ucfirst($order['sale_status'] ?? 'N/A'); ?>
                                </span>
                            </div>
                            <?php if ($order['order_status'] == 'pending'): ?>
                                <div class="action-buttons" style="margin-top: 1rem;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn-action btn-confirm" onclick="return confirm('Confirm this order? This will update both order and sales status.');">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn-action btn-cancel" onclick="return confirm('Cancel this order? This will update both order and sales status.');">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Explore</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#browse">Browse Items</a></li>
                    <li><a href="#services">Browse Services</a></li>
                    <li><a href="viewgroups.php">Community Groups</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Help</h4>
                <ul>
                    <li><a href="#faq">FAQs</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#support">Support Center</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#terms">Terms of Service</a></li>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#cookies">Cookie Policy</a></li>
                    <li><a href="#guidelines">Community Guidelines</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Connect</h4>
                <ul>
                    <li><a href="#facebook">Facebook</a></li>
                    <li><a href="#twitter">Twitter</a></li>
                    <li><a href="#instagram">Instagram</a></li>
                    <li><a href="#linkedin">LinkedIn</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Neighborhood Connect · Connecting communities through sharing resources and skills.</p>
        </div>
    </footer>

    <script>
        // Add confirmation dialogs for actions
        document.addEventListener('DOMContentLoaded', function() {
            const confirmButtons = document.querySelectorAll('.btn-confirm');
            const cancelButtons = document.querySelectorAll('.btn-cancel');
            
            confirmButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to confirm this order?')) {
                        e.preventDefault();
                    }
                });
            });
            
            cancelButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to cancel this order?')) {
                        e.preventDefault();
                    }
                });
            });
        });

        // Auto-refresh page every 30 seconds to check for new orders
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
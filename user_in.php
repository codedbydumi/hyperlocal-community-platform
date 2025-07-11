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

// Redirect if not logged in
if (!$logged_user || empty($logged_user_email)) {
    header("Location: login.php");
    exit();
}

// Handle notification actions (mark as read, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $notification_id = intval($_POST['notification_id']);
        $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("is", $notification_id, $logged_user_email);
        $update_stmt->execute();
        $update_stmt->close();
    }
    
    if (isset($_POST['mark_all_read'])) {
        $update_all_sql = "UPDATE notifications SET is_read = 1 WHERE user_email = ?";
        $update_all_stmt = $conn->prepare($update_all_sql);
        $update_all_stmt->bind_param("s", $logged_user_email);
        $update_all_stmt->execute();
        $update_all_stmt->close();
    }
    
    if (isset($_POST['delete_notification'])) {
        $notification_id = intval($_POST['notification_id']);
        $delete_sql = "DELETE FROM notifications WHERE id = ? AND user_email = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("is", $notification_id, $logged_user_email);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    
    // Redirect to avoid form resubmission
    header("Location: user_in.php");
    exit();
}

// Fetch notifications for the logged-in user
$notifications_sql = "SELECT * FROM notifications WHERE user_email = ? ORDER BY created_at DESC";
$notifications_stmt = $conn->prepare($notifications_sql);
$notifications_stmt->bind_param("s", $logged_user_email);
$notifications_stmt->execute();
$notifications_result = $notifications_stmt->get_result();

// Count unread notifications
$unread_count_sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_email = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_count_sql);
$unread_stmt->bind_param("s", $logged_user_email);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_count'];
$unread_stmt->close();

// Also get rental status updates by joining with sales table
$rental_updates_sql = "SELECT s.*, l.name as item_name, l.image, oc.status as confirmation_status 
                       FROM sales s 
                       LEFT JOIN listings l ON s.item_name = l.name 
                       LEFT JOIN order_confirmations oc ON s.id = oc.sale_id 
                       WHERE s.renter_email = ? 
                       ORDER BY s.rent_date DESC";
$rental_stmt = $conn->prepare($rental_updates_sql);
$rental_stmt->bind_param("s", $logged_user_email);
$rental_stmt->execute();
$rental_result = $rental_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - RentNow</title>
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
            --info-color: #17a2b8;
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

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notification-badge {
            background-color: var(--error-color);
            color: white;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 50%;
            min-width: 20px;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-secondary {
            background-color: var(--light-gray);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: #d8dde4;
        }

        .btn-danger {
            background-color: var(--error-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .notifications-container {
            display: grid;
            gap: 20px;
        }

        .notification-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--light-gray);
            transition: all 0.3s;
        }

        .notification-card.unread {
            border-left-color: var(--accent-color);
            background-color: #f8fbff;
        }

        .notification-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .notification-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .notification-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: var(--dark-gray);
        }

        .notification-actions {
            display: flex;
            gap: 10px;
        }

        .notification-message {
            color: var(--text-color);
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .rental-info {
            background-color: var(--light-gray);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .rental-info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            align-items: center;
        }

        .rental-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .rental-details h4 {
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .rental-details p {
            font-size: 14px;
            color: var(--dark-gray);
            margin-bottom: 3px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .empty-state-icon {
            font-size: 48px;
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--dark-gray);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--dark-gray);
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .tab {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            background-color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: var(--dark-gray);
            transition: all 0.3s;
        }

        .tab.active {
            background-color: var(--primary-color);
            color: white;
        }

        .tab:hover:not(.active) {
            background-color: var(--light-gray);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .notification-header {
                flex-direction: column;
                gap: 10px;
            }

            .notification-actions {
                align-self: flex-start;
            }

            .rental-info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">RentNow</a>
            <div class="nav-links">
                <a href="view_listings.php">Browse Items</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="user_in.php">Notifications</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                Notifications
                <?php if ($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </h1>
            <div class="action-buttons">
                <?php if ($unread_count > 0): ?>
                    <form method="post" style="display: inline;">
                        <button type="submit" name="mark_all_read" class="btn btn-secondary">
                            Mark All Read
                        </button>
                    </form>
                <?php endif; ?>
                <a href="view_listings.php" class="btn btn-primary">Browse Items</a>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('notifications')">
                System Notifications (<?php echo $notifications_result->num_rows; ?>)
            </button>
            <button class="tab" onclick="showTab('rentals')">
                Rental Status (<?php echo $rental_result->num_rows; ?>)
            </button>
        </div>

        <!-- System Notifications Tab -->
        <div id="notifications" class="tab-content active">
            <div class="notifications-container">
                <?php if ($notifications_result->num_rows > 0): ?>
                    <?php while ($notification = $notifications_result->fetch_assoc()): ?>
                        <div class="notification-card <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                            <div class="notification-header">
                                <div>
                                    <div class="notification-title">System Notification</div>
                                    <div class="notification-meta">
                                        <span>📅 <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></span>
                                        <?php if (!$notification['is_read']): ?>
                                            <span style="color: var(--accent-color); font-weight: 600;">● Unread</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="notification-actions">
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" name="mark_read" class="btn btn-secondary btn-sm">
                                                Mark Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <button type="submit" name="delete_notification" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to delete this notification?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="notification-message">
                                <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🔔</div>
                        <h3>No Notifications</h3>
                        <p>You don't have any system notifications yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rental Status Tab -->
        <div id="rentals" class="tab-content">
            <div class="notifications-container">
                <?php if ($rental_result->num_rows > 0): ?>
                    <?php while ($rental = $rental_result->fetch_assoc()): ?>
                        <div class="notification-card">
                            <div class="notification-header">
                                <div>
                                    <div class="notification-title">Rental Order #<?php echo $rental['id']; ?></div>
                                    <div class="notification-meta">
                                        <span>📅 Ordered: <?php echo date('M j, Y', strtotime($rental['rent_date'])); ?></span>
                                        <span>💰 $<?php echo number_format($rental['total_price'], 2); ?></span>
                                        <span class="status-badge status-<?php echo strtolower($rental['confirmation_status'] ?? $rental['status']); ?>">
                                            <?php echo ucfirst($rental['confirmation_status'] ?? $rental['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="rental-info">
                                <div class="rental-info-grid">
                                    <?php if ($rental['image']): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($rental['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($rental['item_name']); ?>" 
                                             class="rental-image">
                                    <?php else: ?>
                                        <div class="rental-image" style="background-color: var(--light-gray); display: flex; align-items: center; justify-content: center; color: var(--dark-gray); font-size: 12px;">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="rental-details">
                                        <h4><?php echo htmlspecialchars($rental['item_name']); ?></h4>
                                        <p><strong>Type:</strong> <?php echo ucfirst($rental['item_type']); ?></p>
                                        <p><strong>Period:</strong> <?php echo date('M j', strtotime($rental['start_date'])) . ' - ' . date('M j, Y', strtotime($rental['end_date'])); ?></p>
                                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($rental['rental_time'])); ?></p>
                                        <p><strong>Delivery:</strong> <?php echo ucfirst($rental['delivery_method']); ?></p>
                                        <?php if ($rental['phone_number']): ?>
                                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($rental['phone_number']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="notification-message">
                                <?php
                                $status = $rental['confirmation_status'] ?? $rental['status'];
                                switch(strtolower($status)) {
                                    case 'pending':
                                        echo "⏳ Your rental request is pending approval from the owner. You'll receive a notification once it's confirmed.";
                                        break;
                                    case 'confirmed':
                                        echo "✅ Great! Your rental has been confirmed by the owner. They will contact you to arrange the " . $rental['delivery_method'] . " details.";
                                        break;
                                    case 'cancelled':
                                        echo "❌ Unfortunately, this rental has been cancelled. If you have any questions, please contact support.";
                                        break;
                                    case 'completed':
                                        echo "🎉 This rental has been completed successfully. Thank you for using RentNow!";
                                        break;
                                    default:
                                        echo "📋 Rental status: " . ucfirst($status);
                                }
                                ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <h3>No Rental History</h3>
                        <p>You haven't rented any items yet. <a href="view_listings.php">Browse available items</a> to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Auto-refresh page every 30 seconds to get new notifications
        setInterval(function() {
            // Only refresh if user is not interacting with the page
            if (document.hidden) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>

<?php
// Close connections
$notifications_stmt->close();
$rental_stmt->close();
$conn->close();
?>
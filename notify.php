<?php
// Database connection - UPDATE THESE VALUES WITH YOUR ACTUAL DATABASE CREDENTIALS
$servername = "localhost";
$username = "root";          // Default WAMP username is usually "root"
$password = "";              // Default WAMP password is usually empty
$dbname = "final";           // Replace with your actual database name

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

// Handle AJAX request for marking notification as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    header('Content-Type: application/json');
    
    // Verify user is logged in
    if (empty($_SESSION['email'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }
    
    $notification_id = intval($_POST['notification_id']);
    $logged_user_email = $_SESSION['email'];
    
    // Create a new connection for this request
    $ajax_conn = new mysqli($servername, $username, $password, $dbname);
    if ($ajax_conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit();
    }
    
    // Update the notification status to 'read' in the database
    $update_sql = "UPDATE order_confirmations SET status = 'read', read_date = NOW() WHERE id = ? AND renter_email = ?";
    $update_stmt = $ajax_conn->prepare($update_sql);
    
    if ($update_stmt) {
        $update_stmt->bind_param("is", $notification_id, $logged_user_email);
        $success = $update_stmt->execute();
        
        if ($success) {
            // Get updated unread count
            $count_sql = "SELECT COUNT(*) as unread_count FROM order_confirmations WHERE renter_email = ? AND status = 'pending'";
            $count_stmt = $ajax_conn->prepare($count_sql);
            if ($count_stmt) {
                $count_stmt->bind_param("s", $logged_user_email);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $unread_count = $count_result->fetch_assoc()['unread_count'];
                $count_stmt->close();
                
                echo json_encode(['success' => true, 'unread_count' => $unread_count]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Count statement preparation failed: ' . $ajax_conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $update_stmt->error]);
        }
        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Update statement preparation failed: ' . $ajax_conn->error]);
    }
    
    $ajax_conn->close();
    exit();
}

// Handle AJAX request for getting notification count
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_count') {
    header('Content-Type: application/json');
    
    // Verify user is logged in
    if (empty($_SESSION['email'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }
    
    $logged_user_email = $_SESSION['email'];
    
    // Create a new connection for this request
    $ajax_conn = new mysqli($servername, $username, $password, $dbname);
    if ($ajax_conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit();
    }
    
    $count_sql = "SELECT COUNT(*) as unread_count FROM order_confirmations WHERE renter_email = ? AND status = 'pending'";
    $count_stmt = $ajax_conn->prepare($count_sql);
    if ($count_stmt) {
        $count_stmt->bind_param("s", $logged_user_email);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $unread_count = $count_result->fetch_assoc()['unread_count'];
        $count_stmt->close();
        
        echo json_encode(['success' => true, 'unread_count' => $unread_count]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $ajax_conn->error]);
    }
    
    $ajax_conn->close();
    exit();
}

// Redirect if not logged in (for main page load)
if (empty($logged_user_email)) {
    header("Location: login.php");
    exit();
}

// Fetch notifications from order_confirmations table
$notifications_sql = "SELECT oc.*, s.start_date, s.end_date, s.total_price, s.rent_date 
                      FROM order_confirmations oc 
                      LEFT JOIN sales s ON oc.sale_id = s.id 
                      WHERE oc.renter_email = ? 
                      ORDER BY oc.confirmation_date DESC, oc.id DESC";
$notifications_stmt = $conn->prepare($notifications_sql);

if (!$notifications_stmt) {
    die("Error preparing statement: " . $conn->error);
}

$notifications_stmt->bind_param("s", $logged_user_email);
$notifications_stmt->execute();
$notifications_result = $notifications_stmt->get_result();
$notifications = $notifications_result->fetch_all(MYSQLI_ASSOC);
$notifications_stmt->close();

// Count unread notifications (pending status)
$unread_count = 0;
foreach ($notifications as $notification) {
    if ($notification['status'] === 'pending') {
        $unread_count++;
    }
}

$conn->close();
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
            --background-color: #f8fafc;
            --text-color: #2d3748;
            --light-gray: #edf2f7;
            --dark-gray: #4a5568;
            --success-color: #48bb78;
            --error-color: #f56565;
            --warning-color: #ed8936;
            --pending-color: #4299e1;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: var(--card-shadow);
            padding: 15px 0;
            margin-bottom: 40px;
            position: sticky;
            top: 0;
            z-index: 100;
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
            font-size: 28px;
            font-weight: 800;
            color: white;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-gray);
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-icon {
            font-size: 28px;
            color: var(--primary-color);
        }

        .notification-count {
            background: linear-gradient(135deg, var(--warning-color), #f6ad55);
            color: white;
            font-size: 14px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            min-width: 24px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .notification-count.hidden {
            display: none;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 30px;
            background: white;
            padding: 6px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .filter-tab {
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: var(--dark-gray);
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .filter-tab.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(74, 111, 165, 0.3);
        }

        .filter-tab:hover:not(.active) {
            background: var(--light-gray);
            color: var(--text-color);
        }

        /* Notifications List */
        .notifications-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .notification-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .notification-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .notification-card.unread {
            border-left: 4px solid var(--pending-color);
            background: linear-gradient(135deg, rgba(66, 153, 225, 0.02) 0%, rgba(66, 153, 225, 0.01) 100%);
        }

        .notification-card.confirmed {
            border-left: 4px solid var(--success-color);
        }

        .notification-card.cancelled {
            border-left: 4px solid var(--error-color);
        }

        .notification-card.read {
            border-left: 4px solid var(--light-gray);
            opacity: 0.8;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .notification-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
        }

        .notification-subtitle {
            font-size: 14px;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(66, 153, 225, 0.1);
            color: var(--pending-color);
            border: 1px solid rgba(66, 153, 225, 0.2);
        }

        .status-confirmed {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(72, 187, 120, 0.2);
        }

        .status-cancelled {
            background: rgba(245, 101, 101, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(245, 101, 101, 0.2);
        }

        .status-read {
            background: rgba(160, 174, 192, 0.1);
            color: var(--dark-gray);
            border: 1px solid rgba(160, 174, 192, 0.2);
        }

        .status-icon {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Notification Details */
        .notification-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-color);
        }

        .detail-value.price {
            font-size: 16px;
            font-weight: 700;
            color: var(--success-color);
        }

        .detail-value.dates {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Notification Footer */
        .notification-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--light-gray);
        }

        .notification-date {
            font-size: 12px;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .notification-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: 1px solid var(--light-gray);
            background: white;
            color: var(--dark-gray);
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .action-btn:hover {
            background: var(--light-gray);
            border-color: var(--dark-gray);
        }

        .action-btn.primary {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .action-btn.primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .action-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .action-btn.loading::after {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .empty-icon {
            font-size: 64px;
            color: var(--light-gray);
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 8px;
        }

        .empty-description {
            font-size: 14px;
            color: var(--dark-gray);
            margin-bottom: 24px;
        }

        .empty-action {
            display: inline-block;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .empty-action:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-color);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: var(--hover-shadow);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            background: var(--error-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .page-title {
                font-size: 24px;
            }

            .filter-tabs {
                overflow-x: auto;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .filter-tabs::-webkit-scrollbar {
                display: none;
            }

            .notification-details {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .notification-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .nav-container {
                padding: 0 15px;
            }

            .container {
                padding: 0 15px;
            }
        }

        /* Animations */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .notification-card {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">RentNow</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="userdashboard.php" class="nav-link">Dashboard</a>
                <a href="explore.php" class="nav-link">Browse</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span class="notification-icon">🔔</span>
                    Notifications
                    <span class="notification-count <?php echo $unread_count > 0 ? '' : 'hidden'; ?>" id="notification-count">
                        <?php echo $unread_count; ?>
                    </span>
                </h1>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All Notifications</button>
            <button class="filter-tab" data-filter="pending">Pending</button>
            <button class="filter-tab" data-filter="confirmed">Confirmed</button>
            <button class="filter-tab" data-filter="cancelled">Cancelled</button>
            <button class="filter-tab" data-filter="read">Read</button>
        </div>

        <!-- Notifications List -->
        <div class="notifications-container">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3 class="empty-title">No notifications yet</h3>
                    <p class="empty-description">
                        When you rent items or receive updates about your rentals, they'll appear here.
                    </p>
                    <a href="view_listings.php" class="empty-action">Browse Items</a>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card <?php echo $notification['status'] === 'pending' ? 'unread' : ''; ?> <?php echo $notification['status']; ?>" 
                         data-status="<?php echo $notification['status']; ?>"
                         data-id="<?php echo $notification['id']; ?>">
                        
                        <div class="notification-header">
                            <div>
                                <h3 class="notification-title">
                                    Rental Request: <?php echo htmlspecialchars($notification['item_name']); ?>
                                </h3>
                                <p class="notification-subtitle">
                                    Order ID: #<?php echo $notification['sale_id']; ?>
                                </p>
                            </div>
                            <div class="status-badge status-<?php echo $notification['status']; ?>">
                                <span class="status-icon"></span>
                                <?php echo ucfirst($notification['status']); ?>
                            </div>
                        </div>

                        <div class="notification-details">
                            <div class="detail-item">
                                <span class="detail-label">Item Name</span>
                                <span class="detail-value"><?php echo htmlspecialchars($notification['item_name']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Delivery Method</span>
                                <span class="detail-value"><?php echo ucfirst($notification['delivery_method']); ?></span>
                            </div>

                            <?php if (!empty($notification['total_price'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Total Price</span>
                                <span class="detail-value price">$<?php echo number_format($notification['total_price'], 2); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($notification['start_date']) && !empty($notification['end_date'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Rental Period</span>
                                <span class="detail-value dates">
                                    <?php echo date('M j, Y', strtotime($notification['start_date'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($notification['end_date'])); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <span class="detail-label">Lister Email</span>
                                <span class="detail-value"><?php echo htmlspecialchars($notification['lister_email']); ?></span>
                            </div>
                        </div>

                        <div class="notification-footer">
                            <span class="notification-date">
                                <?php 
                                $date_to_show = $notification['confirmation_date'] ?: $notification['rent_date'];
                                if ($date_to_show) {
                                    echo date('M j, Y g:i A', strtotime($date_to_show));
                                } else {
                                    echo 'Date not available';
                                }
                                ?>
                            </span>
                            
                            <div class="notification-actions">
                                <?php if ($notification['status'] === 'pending'): ?>
                                    <button class="action-btn mark-read-btn" data-id="<?php echo $notification['id']; ?>">
                                        Mark as Read
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script>
        // Utility function to show toast notifications
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            
            const container = document.getElementById('toast-container');
            container.appendChild(toast);
            
            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Hide and remove toast
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => container.removeChild(toast), 300);
            }, 3000);
        }

        // Function to update notification count
        function updateNotificationCount(newCount) {
            const countBadge = document.getElementById('notification-count');
            if (newCount <= 0) {
                countBadge.classList.add('hidden');
            } else {
                countBadge.classList.remove('hidden');
                countBadge.textContent = newCount;
            }
        }

        // Function to mark notification as read
        function markAsRead(notificationId, button) {
            button.classList.add('loading');
            button.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_id', notificationId);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update UI
                    const card = button.closest('.notification-card');
                    card.classList.remove('unread');
                    card.classList.add('read');
                    card.setAttribute('data-status', 'read');
                    
                    // Update status badge
                    const statusBadge = card.querySelector('.status-badge');
                    statusBadge.className = 'status-badge status-read';
                    statusBadge.innerHTML = '<span class="status-icon"></span>Read';
                    
                    // Remove button
                    button.style.display = 'none';
                    
                    // Update notification count
                    updateNotificationCount(data.unread_count);
                    
                    showToast('Notification marked as read');
                } else {
                    console.error('Server error:', data.error);
                    showToast(data.error || 'Failed to update notification', 'error');
                    button.classList.remove('loading');
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('An error occurred. Please try again.', 'error');
                button.classList.remove('loading');
                button.disabled = false;
            });
        }

        // Filter functionality
        const filterTabs = document.querySelectorAll('.filter-tab');
        const notificationCards = document.querySelectorAll('.notification-card');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');

                // Filter notifications
                notificationCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    
                    if (filter === 'all' || status === filter) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeInUp 0.5s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show empty state if no cards are visible
                const visibleCards = Array.from(notificationCards).filter(card => 
                    card.style.display !== 'none'
                );

                const existingTempEmpty = document.querySelector('.temp-empty');
                if (existingTempEmpty) {
                    existingTempEmpty.remove();
                }

                if (visibleCards.length === 0 && !document.querySelector('.empty-state:not(.temp-empty)')) {
                    const tempEmptyState = document.createElement('div');
                    tempEmptyState.className = 'empty-state temp-empty';
                    tempEmptyState.innerHTML = `
                        <div class="empty-icon">🔍</div>
                        <h3 class="empty-title">No ${filter} notifications</h3>
                        <p class="empty-description">
                            No notifications found for the selected filter.
                        </p>
                    `;
                    document.querySelector('.notifications-container').appendChild(tempEmptyState);
                }
            });
        });

        // Event delegation for mark as read buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('mark-read-btn')) {
                const notificationId = e.target.getAttribute('data-id');
                markAsRead(notificationId, e.target);
            }
        });

        // Function to refresh notification count periodically
        function refreshNotificationCount() {
            fetch(window.location.href + '?action=get_count', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateNotificationCount(data.unread_count);
                } else {
                    console.error('Error refreshing count:', data.error);
                }
            })
            .catch(error => {
                console.error('Error refreshing notification count:', error);
            });
        }

        // Refresh notification count every 30 seconds
        setInterval(refreshNotificationCount, 30000);

        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
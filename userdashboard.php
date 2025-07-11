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

// Initialize counts with default values
$pendingCount = 0;
$totalOrdersCount = 0;
$confirmedCount = 0;
$activeListingsCount = 0;
$itemsRentedCount = 0; // New variable for items I rented

// Query to count pending orders for the user's listings (orders others made on my listings)
$pendingOrdersQuery = "SELECT COUNT(*) as pending_count FROM order_confirmations WHERE lister_email = ? AND status = 'pending'";
$stmt = $conn->prepare($pendingOrdersQuery);

if ($stmt === false) {
    // Log the error for debugging
    error_log("MySQL prepare error: " . $conn->error);
    echo "Database error occurred. Please check if the 'order_confirmations' table exists.";
} else {
    $stmt->bind_param("s", $userEmail);
    if ($stmt->execute()) {
        $pendingResult = $stmt->get_result();
        $pendingCount = $pendingResult->fetch_assoc()['pending_count'];
    } else {
        error_log("MySQL execute error: " . $stmt->error);
    }
    $stmt->close();
}

// Query to count total orders for the user's listings (orders others made on my listings)
$totalOrdersQuery = "SELECT COUNT(*) as total_count FROM order_confirmations WHERE lister_email = ?";
$stmt = $conn->prepare($totalOrdersQuery);

if ($stmt !== false) {
    $stmt->bind_param("s", $userEmail);
    if ($stmt->execute()) {
        $totalResult = $stmt->get_result();
        $totalOrdersCount = $totalResult->fetch_assoc()['total_count'];
    }
    $stmt->close();
}

// Query to count confirmed orders from my listings (items I've successfully rented out)
$confirmedOrdersQuery = "SELECT COUNT(*) as confirmed_count FROM order_confirmations WHERE lister_email = ? AND status = 'confirmed'";
$stmt = $conn->prepare($confirmedOrdersQuery);

if ($stmt !== false) {
    $stmt->bind_param("s", $userEmail);
    if ($stmt->execute()) {
        $confirmedResult = $stmt->get_result();
        $confirmedCount = $confirmedResult->fetch_assoc()['confirmed_count'];
    }
    $stmt->close();
}

// NEW QUERY: Count items I have rented (where I'm the renter)
// This counts confirmed orders where my email is the renter_email
$itemsRentedQuery = "SELECT COUNT(*) as rented_count FROM order_confirmations WHERE renter_email = ? AND status = 'confirmed'";
$stmt = $conn->prepare($itemsRentedQuery);

if ($stmt !== false) {
    $stmt->bind_param("s", $userEmail);
    if ($stmt->execute()) {
        $rentedResult = $stmt->get_result();
        $row = $rentedResult->fetch_assoc();
        $itemsRentedCount = $row ? $row['rented_count'] : 0;
    } else {
        $itemsRentedCount = 0;
    }
    $stmt->close();
} else {
    $itemsRentedCount = 0;
}

// Query to count active listings - FIXED: Removed status filter since column doesn't exist
$activeListingsQuery = "SELECT COUNT(*) as active_count FROM listings WHERE user_email = ?";
$stmt = $conn->prepare($activeListingsQuery);

if ($stmt !== false) {
    $stmt->bind_param("s", $userEmail);
    if ($stmt->execute()) {
        $activeListingsResult = $stmt->get_result();
        $row = $activeListingsResult->fetch_assoc();
        $activeListingsCount = $row ? $row['active_count'] : 0;
    } else {
        $activeListingsCount = 0;
    }
    $stmt->close();
} else {
    $activeListingsCount = 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Neighborhood Connect</title>
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

        /* Hero Section - Made Smaller */
        .hero-section {
            background: linear-gradient(135deg, #5865f2 0%, #7289da 100%);
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            border-radius: 20px;
            margin: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.75rem;
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 0.85rem 1.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-hero-primary {
            background: #ff6b35;
            color: white;
        }

        .btn-hero-primary:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Pending Orders Badge */
        .pending-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid white;
        }

        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-illustration {
            width: 100%;
            max-width: 300px;
            height: 220px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Dashboard Sections */
        .section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #24292f;
            margin-bottom: 1.5rem;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .grid-item {
            background: white;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e1e8ed;
            position: relative;
        }

        .grid-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .grid-item-icon {
            width: 48px;
            height: 48px;
            background: #5865f2;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.2rem;
            position: relative;
        }

        .grid-item h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #24292f;
            margin-bottom: 0.5rem;
        }

        .grid-item a {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        /* Badge for grid items */
        .grid-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid white;
        }

        /* Action Cards */
        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .action-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e1e8ed;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .action-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .action-card-icon {
            width: 48px;
            height: 48px;
            background: #5865f2;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .action-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #24292f;
        }

        .action-card p {
            color: #656d76;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .btn-action {
            background: #5865f2;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-action:hover {
            background: #4752c4;
            transform: translateY(-1px);
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e1e8ed;
            position: relative;
        }

        .stat-card.urgent {
            border-left: 4px solid #ff4757;
        }

        .stat-card.urgent .stat-number {
            color: #ff4757;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #5865f2;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #656d76;
            font-size: 0.9rem;
        }

        /* Pulse animation for pending orders */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Footer */
        footer {
            background: #24292f;
            color: white;
            padding: 2rem;
            margin-top: 4rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-content p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
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

            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text h1 {
                font-size: 1.8rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .container {
                padding: 0 1rem;
            }

            .action-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                margin: 1rem;
                padding: 1.5rem 1rem;
            }
        }

        /* Error message styling */
        .error-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="error-message" id="errorMessage">
        Database connection issues detected. Some features may not work properly.
    </div>

    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="index.php" class="nav-logo">
                    <div class="nav-logo-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    NEIGHBORHOOD
                </a>
                <div class="search-bar">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search for tools, skills or community help...">
                </div>
            </div>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="explore.php">Explore</a></li>
                    <li><a href="viewgroups.php">Community</a></li>
                </ul>
                <div class="user-menu">
                    <!-- Navigation icons container -->
                <div class="nav-icons">
                    <!-- Notification icon (only show when logged in) -->
                    <?php if (isset($_SESSION['email'])): ?>
                        <a href="notifications.php" class="notification-icon" title="Notifications">
                            <i class="fas fa-bell"></i>
                    
                        </a>
                    <?php endif; ?>
                    
                    <a href="logout.php" class="btn-primary-nav">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p>Connect with your neighbors, share resources, and build a stronger community together.</p>
                    <div class="hero-buttons">
                        <a href="createlisting.php" class="btn-hero btn-hero-primary">
                            <i class="fas fa-plus"></i>
                            Create Listing
                        </a>
                        <a href="myorders.php" class="btn-hero btn-hero-secondary">
                            <i class="fas fa-receipt"></i>
                            My Orders
                            <?php if ($pendingCount > 0): ?>
                                <span class="pending-badge"><?php echo $pendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="hero-illustration">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <!-- Quick Stats -->
            <section class="quick-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $activeListingsCount; ?></div>
                    <div class="stat-label">Active Listings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $itemsRentedCount; ?></div>
                    <div class="stat-label">Items I Rented</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Community Groups</div>
                </div>
                <div class="stat-card <?php echo $pendingCount > 0 ? 'urgent pulse' : ''; ?>">
                    <div class="stat-number"><?php echo $pendingCount; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </section>

            <!-- Browse Items -->
            <section class="section">
                <h2 class="section-title">Browse Items</h2>
                <div class="grid-container">
                    <div class="grid-item">
                        <a href="rented_item.php">
                            <div class="grid-item-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3>My Listed Items</h3>
                        </a>
                    </div>
                    <div class="grid-item">
                        <a href="listed_item.php">
                            <div class="grid-item-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>What I Rented</h3>
                        </a>
                    </div>
                    <div class="grid-item">
                        <a href="myorders.php">
                            <div class="grid-item-icon">
                                <i class="fas fa-receipt"></i>
                                <?php if ($pendingCount > 0): ?>
                                    <span class="grid-badge"><?php echo $pendingCount; ?></span>
                                <?php endif; ?>
                            </div>
                            <h3>My Orders</h3>
                        </a>
                    </div>
                    <div class="grid-item">
                        <a href="viewgroups.php">
                            <div class="grid-item-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>Community Chat</h3>
                        </a>
                    </div>
                    <div class="grid-item">
                        <a href="explore.php">
                            <div class="grid-item-icon">
                                <i class="fas fa-th"></i>
                            </div>
                            <h3>All Items</h3>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Action Cards -->
            <section class="action-cards">
                <div class="action-card">
                    <div class="action-card-header">
                        <div class="action-card-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h3>Manage Account</h3>
                    </div>
                    <p>Update your profile information, preferences, and account settings.</p>
                    <a href="editaccount.php" class="btn-action">
                        <i class="fas fa-arrow-right"></i>
                        Edit Account
                    </a>
                </div>

                <div class="action-card">
                    <div class="action-card-header">
                        <div class="action-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Community Groups</h3>
                    </div>
                    <p>Join existing groups or create new ones to connect with like-minded neighbors.</p>
                    <a href="creategroup.php" class="btn-action">
                        <i class="fas fa-arrow-right"></i>
                        Create a new Group
                    </a>
                </div>

                <div class="action-card">
                    <div class="action-card-header">
                        <div class="action-card-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h3>Share Resources</h3>
                    </div>
                    <p>List items you want to share or rent out to your neighbors.</p>
                    <a href="createlisting.php" class="btn-action">
                        <i class="fas fa-arrow-right"></i>
                        Create Listing
                    </a>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>© 2025 Neighborhood Connect · Connecting communities through sharing resources and skills.</p>
        </div>
    </footer>

    <script>
        // Auto-refresh notification badge every 30 seconds
        setInterval(function() {
            fetch('get_pending_orders_count.php')
                .then(response => response.json())
                .then(data => {
                    const badges = document.querySelectorAll('.notification-badge, .pending-badge, .grid-badge');
                    badges.forEach(badge => {
                        if (data.pending_count > 0) {
                            badge.textContent = data.pending_count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    });
                    
                    // Update pending orders stat
                    const pendingStatNumber = document.querySelector('.stat-card.urgent .stat-number');
                    if (pendingStatNumber) {
                        pendingStatNumber.textContent = data.pending_count;
                    }
                })
                .catch(error => {
                    console.error('Error fetching pending orders:', error);
                    document.getElementById('errorMessage').style.display = 'block';
                });
        }, 30000);


        // Search functionality for the dashboard
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const searchResults = createSearchResultsContainer();
    
    // Create search results container
    function createSearchResultsContainer() {
        const container = document.createElement('div');
        container.className = 'search-results';
        container.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e0e6ed;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            margin-top: 8px;
        `;
        
        searchInput.parentElement.appendChild(container);
        return container;
    }
    
    // Sample search data - you can replace this with actual data from your database
    const searchData = [
        { title: 'Power Tools', category: 'Tools', url: 'explore.php?category=tools', icon: 'fas fa-drill' },
        { title: 'Garden Equipment', category: 'Tools', url: 'explore.php?category=garden', icon: 'fas fa-seedling' },
        { title: 'Kitchen Appliances', category: 'Appliances', url: 'explore.php?category=kitchen', icon: 'fas fa-blender' },
        { title: 'Sports Equipment', category: 'Sports', url: 'explore.php?category=sports', icon: 'fas fa-football-ball' },
        { title: 'Electronics', category: 'Electronics', url: 'explore.php?category=electronics', icon: 'fas fa-laptop' },
        { title: 'My Listed Items', category: 'Account', url: 'rented_item.php', icon: 'fas fa-tools' },
        { title: 'What I Rented', category: 'Account', url: 'listed_item.php', icon: 'fas fa-shopping-bag' },
        { title: 'My Orders', category: 'Account', url: 'myorders.php', icon: 'fas fa-receipt' },
        { title: 'Community Chat', category: 'Community', url: 'viewgroups.php', icon: 'fas fa-comments' },
        { title: 'Create Listing', category: 'Actions', url: 'createlisting.php', icon: 'fas fa-plus-circle' },
        { title: 'Edit Account', category: 'Account', url: 'editaccount.php', icon: 'fas fa-user-edit' },
        { title: 'Create Group', category: 'Community', url: 'creategroup.php', icon: 'fas fa-users' },
        { title: 'Home Repair Skills', category: 'Skills', url: 'explore.php?category=skills&type=repair', icon: 'fas fa-hammer' },
        { title: 'Tutoring Services', category: 'Skills', url: 'explore.php?category=skills&type=tutoring', icon: 'fas fa-graduation-cap' },
        { title: 'Pet Care', category: 'Services', url: 'explore.php?category=services&type=petcare', icon: 'fas fa-paw' },
        { title: 'Cleaning Services', category: 'Services', url: 'explore.php?category=services&type=cleaning', icon: 'fas fa-broom' }
    ];
    
    // Search function
    function performSearch(query) {
        if (!query.trim()) {
            hideSearchResults();
            return;
        }
        
        const results = searchData.filter(item => 
            item.title.toLowerCase().includes(query.toLowerCase()) ||
            item.category.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 8); // Limit to 8 results
        
        displaySearchResults(results, query);
    }
    
    // Display search results
    function displaySearchResults(results, query) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: #656d76;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p>No results found for "${query}"</p>
                    <small>Try searching for tools, skills, or community help</small>
                </div>
            `;
        } else {
            searchResults.innerHTML = results.map(item => `
                <a href="${item.url}" class="search-result-item" style="
                    display: flex;
                    align-items: center;
                    padding: 0.75rem 1rem;
                    text-decoration: none;
                    color: #333;
                    border-bottom: 1px solid #f0f0f0;
                    transition: background-color 0.2s ease;
                " onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                    <div style="
                        width: 36px;
                        height: 36px;
                        background: #5865f2;
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        margin-right: 0.75rem;
                        font-size: 0.9rem;
                    ">
                        <i class="${item.icon}"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 500; font-size: 0.9rem;">${highlightText(item.title, query)}</div>
                        <div style="font-size: 0.8rem; color: #656d76;">${item.category}</div>
                    </div>
                    <i class="fas fa-arrow-right" style="color: #8b949e; font-size: 0.8rem;"></i>
                </a>
            `).join('');
        }
        
        showSearchResults();
    }
    
    // Highlight matching text
    function highlightText(text, query) {
        if (!query.trim()) return text;
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark style="background: #fff3cd; padding: 0;">$1</mark>');
    }
    
    // Show search results
    function showSearchResults() {
        searchResults.style.display = 'block';
    }
    
    // Hide search results
    function hideSearchResults() {
        searchResults.style.display = 'none';
    }
    
    // Event listeners
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value;
        clearTimeout(searchInput.searchTimeout);
        
        // Debounce search to avoid too many calls
        searchInput.searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.trim()) {
            performSearch(this.value);
        }
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.parentElement.contains(e.target)) {
            hideSearchResults();
        }
    });
    
    // Handle Enter key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = searchResults.querySelector('.search-result-item');
            if (firstResult) {
                firstResult.click();
            } else {
                // If no results, redirect to explore page with search query
                window.location.href = `explore.php?search=${encodeURIComponent(this.value)}`;
            }
        }
        
        // Handle arrow key navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const items = searchResults.querySelectorAll('.search-result-item');
            if (items.length === 0) return;
            
            let currentIndex = Array.from(items).findIndex(item => 
                item.style.backgroundColor === 'rgb(248, 249, 250)'
            );
            
            // Reset all items
            items.forEach(item => item.style.backgroundColor = 'transparent');
            
            if (e.key === 'ArrowDown') {
                currentIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
            } else {
                currentIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
            }
            
            items[currentIndex].style.backgroundColor = '#f8f9fa';
            
            // Handle Enter on selected item
            searchInput.selectedResult = items[currentIndex];
        }
        
        if (e.key === 'Enter' && searchInput.selectedResult) {
            searchInput.selectedResult.click();
        }
    });
    
    // Clear search
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            hideSearchResults();
            this.blur();
        }
    });
});

// Optional: Add search suggestions based on user's activity
function addRecentSearches() {
    // This would typically come from localStorage or user activity
    const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
    
    // You can implement this to show recent searches when the input is focused but empty
    return recentSearches;
}

// Optional: Save search queries
function saveSearchQuery(query) {
    if (!query.trim()) return;
    
    let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
    recentSearches = recentSearches.filter(search => search !== query); // Remove if exists
    recentSearches.unshift(query); // Add to beginning
    recentSearches = recentSearches.slice(0, 5); // Keep only 5 recent searches
    
    localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
}



        
    </script>
</body>
</html>
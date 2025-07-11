<?php
// Start the session to check if the user is logged in
session_start();

// Database connection setup
$host = "localhost";
$username = "root";
$password = "";
$database = "final";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch top 4 groups - FIXED: Added backticks around 'groups'
$groupsQuery = "SELECT * FROM `groups` ORDER BY created_at DESC LIMIT 3";
$groupsResult = $conn->query($groupsQuery);

// Get notification count for logged-in user
$notificationCount = 0;
if (isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];
    
    // Count unread notifications from order_confirmations table (matching your notifications page)
    $notificationQuery = "SELECT COUNT(*) as count FROM order_confirmations WHERE renter_email = ? AND status = 'pending'";
    $stmt = $conn->prepare($notificationQuery);
    
    if ($stmt) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $notificationResult = $stmt->get_result();
        
        if ($notificationResult && $notificationResult->num_rows > 0) {
            $notificationRow = $notificationResult->fetch_assoc();
            $notificationCount = $notificationRow['count'];
        }
        $stmt->close();
    }
}

// Get cart count from session
$cartCount = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Count total items in cart (considering quantities)
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += isset($item['quantity']) ? $item['quantity'] : 1;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share. Connect. Save.</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Add Font Awesome for nice icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for the icons */
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #4a6fa5;
            background-color: #f5f7fa;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .category-card:hover .category-icon {
            background-color: #4a6fa5;
            color: white;
            transform: scale(1.05);
        }

        /* Logo styles */
        .logo {
            width: 75px;
            height: auto;
            display: block;
        }

        .footer-logo {
            width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        
/* Popular Groups Section Styles */
.popular-groups {
    padding: 20px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    margin: 30px 0;
    position: relative;
}

.popular-groups::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="%23000" opacity="0.02"/></svg>') repeat;
    background-size: 30px 30px;
}

.popular-groups h2 {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    position: relative;
}

.popular-groups h2::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #4a6fa5, #3b5998);
    border-radius: 2px;
}

.popular-groups > p {
    text-align: center;
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 50px;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
}

/* Enhanced Groups Grid */
.groups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Enhanced Group Card */
.group-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    padding: 30px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.group-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #4a6fa5, #3b5998, #2c3e50);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.group-card:hover::before {
    transform: scaleX(1);
}

.group-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.group-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1.4rem;
    font-weight: 600;
    line-height: 1.3;
}

.group-card p {
    color: #6c757d;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 20px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.group-card .footer {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    color: #6c757d;
    font-size: 0.9rem;
}

.group-card .footer p {
    margin: 0;
    font-weight: 500;
}

/* Enhanced Button Styles */
.join-btn, .view-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #4a6fa5, #3b5998);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(74, 111, 165, 0.3);
    position: relative;
    overflow: hidden;
}

.join-btn::before, .view-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.join-btn:hover::before, .view-btn:hover::before {
    left: 100%;
}

.join-btn:hover, .view-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 111, 165, 0.4);
}

.view-btn {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.view-btn:hover {
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* View All Groups Button */
.popular-groups .btn-cta {
    background: linear-gradient(135deg, #4a6fa5, #3b5998);
    color: white;
    padding: 15px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(74, 111, 165, 0.3);
    position: relative;
    overflow: hidden;
}

.popular-groups .btn-cta::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.popular-groups .btn-cta:hover::before {
    left: 100%;
}

.popular-groups .btn-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(74, 111, 165, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
    .popular-groups {
        padding: 60px 0;
    }
    
    .popular-groups h2 {
        font-size: 2rem;
    }
    
    .groups-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 0 15px;
    }
    
    .group-card {
        padding: 25px;
    }
    
    .group-card h3 {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .popular-groups h2 {
        font-size: 1.8rem;
    }
    
    .group-card {
        padding: 20px;
    }
    
    .join-btn, .view-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}
        .join-btn, .view-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 15px;
            background-color: #4a6fa5;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        .join-btn:hover, .view-btn:hover {
            background-color: #3b5998;
        }
        
        .view-btn {
            background-color: #6c757d;
        }
        
        .view-btn:hover {
            background-color: #5a6268;
        }
        
        /* Cart and notification icon styles */
        .cart-icon, .notification-icon {
            font-size: 1.3rem;
            margin-left: 15px;
            color: #333;
            transition: color 0.3s ease;
            position: relative;
            text-decoration: none;
        }
        
        .cart-icon:hover, .notification-icon:hover {
            color: #4a6fa5;
        }
        
        /* Notification and Cart badge styles */
        .notification-badge, .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        
        /* Different color for cart badge */
        .cart-badge {
            background-color: #27ae60;
        }
        
        /* Hide badge when count is 0 */
        .notification-badge.hidden, .cart-badge.hidden {
            display: none;
        }
        
        /* Pulse animation for badges */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* Icon container to keep icons aligned */
        .nav-icons {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <a href="index.php" class="nav-logo">
                    <img src="images/1.jpg" alt="Logo" class="logo">
                </a>
                <div class="search-container">
                    <input type="text" placeholder="Search for tools, skills or community help...">
                    <div class="search-icons">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="explore.php">Explore</a></li>
                    <li><a href="viewgroups.php">Community</a></li>
                </ul>

                <?php if (isset($_SESSION['username'])): ?>
                    <!-- If the user is logged in, show their name and link to their dashboard -->
                    <a href="userdashboard.php" class="btn-login"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <?php else: ?>
                    <!-- If the user is not logged in, show the Login button -->
                    <a href="login.php" class="btn-login">Login</a>
                <?php endif; ?>
                
                <!-- Navigation icons container -->
                <div class="nav-icons">
                    <!-- Notification icon (only show when logged in) -->
                    <?php if (isset($_SESSION['email'])): ?>
                        <a href="notifications.php" class="notification-icon" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <?php if ($notificationCount > 0): ?>
                                <span class="notification-badge">
                                    <?php echo $notificationCount > 99 ? '99+' : $notificationCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Cart icon with count -->
                    <a href="view_cart.php" class="cart-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge">
                                <?php echo $cartCount > 99 ? '99+' : $cartCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Share. Connect. Save.</h1>
                <p>Borrow tools, find local help, and connect with skilled neighbors in your community.</p>
                <div class="hero-buttons">
                    <a href="explore.php" class="btn-hero btn-find">Find Tools</a>
                    <a href="explore.php" class="btn-hero btn-offer">Offer Skills</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="images/ll.jpg" alt="Community sharing illustration">
            </div>
        </section>

        <section class="categories">
            <h2>Browse items</h2>
            <div class="category-grid">
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <span>Tools</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-guitar"></i>
                    </div>
                    <span>Music equipment</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <span>Electronics</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <span>Garden tools</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <span>All items</span>
                </div>
            </div>
        </section>

        <section class="categories">
            <h2>Browse Services</h2>
            <div class="category-grid">
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <span>Mechanic</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <span>Gardener</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span>Tutoring</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-broom"></i>
                    </div>
                    <span>House maid</span>
                </div>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <span>All Services</span>
                </div>
            </div>
        </section>

       <!-- Popular Groups Section -->
        <section class="popular-groups">
            <div style="text-align: center; margin-bottom: 50px;">
                <h2>Popular Groups</h2>
                <p>Join like-minded neighbors and build meaningful connections in your community</p>
            </div>
            <div class="groups-grid">
                <?php
                if ($groupsResult && $groupsResult->num_rows > 0) {
                    // Output data of each row as a card in the grid
                    while ($row = $groupsResult->fetch_assoc()) {
                        $groupId = $row["id"];
                        
                        // Check if the user is already a member of this group (if logged in)
                        $isMember = false;
                        if (isset($_SESSION['email'])) {
                            $userEmail = $_SESSION['email'];
                            $checkMembershipQuery = "SELECT * FROM group_memberships WHERE user_email = ? AND group_id = ?";
                            $stmt = $conn->prepare($checkMembershipQuery);
                            $stmt->bind_param("si", $userEmail, $groupId);
                            $stmt->execute();
                            $membershipResult = $stmt->get_result();
                            $isMember = $membershipResult->num_rows > 0;
                            $stmt->close();
                        }

                        echo "<div class='group-card'>";
                        echo "<h3>" . htmlspecialchars($row["name"]) . "</h3>";
                        echo "<p>" . htmlspecialchars($row["description"]) . "</p>";
                        echo "<div class='footer'>";
                        echo "<p><strong>Created:</strong> " . date('M d, Y', strtotime($row["created_at"])) . "</p>";
                        echo "</div>"; // Footer

                        // Show different buttons based on login status and membership
                        if (!isset($_SESSION['email'])) {
                            echo "<a href='login.php' class='join-btn'><i class='fas fa-sign-in-alt'></i> Login to Join</a>";
                        } else if ($isMember) {
                            echo "<a href='group_details.php?group_id=" . $groupId . "' class='view-btn'><i class='fas fa-eye'></i> View Group</a>";
                        } else {
                            echo "<a href='join_group.php?group_id=" . $groupId . "' class='join-btn'><i class='fas fa-users'></i> Join Now</a>";
                        }

                        echo "</div>"; // Group card
                    }
                } else {
                    // Fallback content if no groups are found
                    echo "<div class='group-card'>";
                    echo "<h3>Home Improvement</h3>";
                    echo "<p>Share tips and tools for your home improvement projects. Connect with experienced DIY enthusiasts and learn new skills together.</p>";
                    echo "<div class='footer'>";
                    echo "<p><strong>Created:</strong> Mar 10, 2025</p>";
                    echo "</div>";
                    echo "<a href='login.php' class='join-btn'><i class='fas fa-sign-in-alt'></i> Login to Join</a>";
                    echo "</div>";
                    
                    echo "<div class='group-card'>";
                    echo "<h3>Garden Enthusiasts</h3>";
                    echo "<p>Connect with local gardeners to share advice and equipment. From beginner tips to advanced techniques, grow together as a community.</p>";
                    echo "<div class='footer'>";
                    echo "<p><strong>Created:</strong> Mar 5, 2025</p>";
                    echo "</div>";
                    echo "<a href='login.php' class='join-btn'><i class='fas fa-sign-in-alt'></i> Login to Join</a>";
                    echo "</div>";
                    
                    echo "<div class='group-card'>";
                    echo "<h3>Tech Support Network</h3>";
                    echo "<p>Get help with tech issues from neighbors with IT skills. Troubleshoot problems and stay updated with the latest technology trends.</p>";
                    echo "<div class='footer'>";
                    echo "<p><strong>Created:</strong> Feb 28, 2025</p>";
                    echo "</div>";
                    echo "<a href='login.php' class='join-btn'><i class='fas fa-sign-in-alt'></i> Login to Join</a>";
                    echo "</div>";
                    
                
                }
                ?>
            </div>
            <div style="text-align: center; margin-top: 60px;">
                <a href="viewgroups.php" class="btn-cta">
                    <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                    Explore All Groups
                </a>
            </div>
        </section>

        <section class="cta">
            <h2>Join Our Community Today</h2>
            <a href="register.php" class="btn-cta">Sign Up Now</a>
        </section>
    </main>

    <footer style="background-color: #3366cc; color:rgb(255, 255, 255); padding: 60px 0 20px; font-family: inherit;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 40px;">
                <!-- Footer Brand Section with Logo -->
                <div style="flex: 0 0 100%; max-width: 350px; margin-bottom: 30px;">
                    <img src="images/new.jpg" alt="Logo" class="footer-logo">
                    <p style="margin-bottom: 20px; line-height: 1.6; color:rgb(255, 255, 255);">Connecting communities through sharing resources and skills.</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="#" aria-label="Facebook" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.1); color: #f5f7fa; transition: all 0.3s ease;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.1); color: #f5f7fa; transition: all 0.3s ease;"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.1); color: #f5f7fa; transition: all 0.3s ease;"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.1); color: #f5f7fa; transition: all 0.3s ease;"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <!-- Footer Links Section -->
                <div style="display: flex; flex-wrap: wrap; flex: 1; gap: 30px;">
                    <!-- Explore Column -->
                    <div style="flex: 1; min-width: 160px;">
                        <h4 style="font-size: 1.1rem; margin: 0 0 20px; position: relative; color: #ffffff;">Explore</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 12px;"><a href="index.php" style="color:rgb(255, 255, 255); text-decoration: none; transition: color 0.3s ease;">Home</a></li>
                            <li style="margin-bottom: 12px;"><a href="explore.php" style="color:rgb(223, 241, 252); text-decoration: none; transition: color 0.3s ease;">Browse Items</a></li>
                            <li style="margin-bottom: 12px;"><a href="explore.php" style="color:rgb(255, 255, 255); text-decoration: none; transition: color 0.3s ease;">Browse Services</a></li>
                            <li style="margin-bottom: 12px;"><a href="viewgroups.php" style="color:rgb(255, 255, 255); text-decoration: none; transition: color 0.3s ease;">Community Groups</a></li>
                        </ul>
                    </div>
                    
                    <!-- Help Column -->
                    <div style="flex: 1; min-width: 160px;">
                        <h4 style="font-size: 1.1rem; margin: 0 0 20px; position: relative; color: #ffffff;">Help</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 12px;"><a href="faqs.html" style="color:rgb(239, 249, 255); text-decoration: none; transition: color 0.3s ease;">FAQs</a></li>
                            <li style="margin-bottom: 12px;"><a href="contact.html" style="color:rgb(255, 255, 255); text-decoration: none; transition: color 0.3s ease;">Contact Us</a></li>
                            <li style="margin-bottom: 12px;"><a href="help.html" style="color:#cdd4d8; text-decoration: none; transition: color 0.3s ease;">Support Center</a></li>
                        </ul>
                    </div>
                    
                    <!-- Legal Column -->
                    <div style="flex: 1; min-width: 160px;">
                        <h4 style="font-size: 1.1rem; margin: 0 0 20px; position: relative; color: #ffffff;">Legal</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 12px;"><a href="terms.html" style="color:rgb(255, 255, 255); text-decoration: none; transition: color 0.3s ease;">Terms of Service</a></li>
                            <li style="margin-bottom: 12px;"><a href="privacy.html" style="color:rgb(251, 254, 255); text-decoration: none; transition: color 0.3s ease;">Privacy Policy</a></li>
                            <li style="margin-bottom: 12px;"><a href="cookie.html" style="color:rgb(255, 255, 255); text-decoration: none; transition: color 0.3s ease;">Cookie Policy</a></li>
                            <li style="margin-bottom: 12px;"><a href="community.html" style="color: #bdc3c7; text-decoration: none; transition: color 0.3s ease;">Community Guidelines</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript for interactive effects -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Social icons hover effect
            const socialIcons = document.querySelectorAll('footer a[aria-label]');
            socialIcons.forEach(icon => {
                icon.addEventListener('mouseover', function() {
                    this.style.backgroundColor = '#4a6fa5';
                    this.style.transform = 'translateY(-3px)';
                });
                icon.addEventListener('mouseout', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Footer links hover effect
            const footerLinks = document.querySelectorAll('footer ul li a');
            footerLinks.forEach(link => {
                link.addEventListener('mouseover', function() {
                    this.style.color = '#ffffff';
                    this.style.paddingLeft = '5px';
                });
                link.addEventListener('mouseout', function() {
                    this.style.color = '#bdc3c7';
                    this.style.paddingLeft = '0';
                });
            });
            
            // Debug: Log notification and cart counts
            console.log('Notification count: <?php echo $notificationCount; ?>');
            console.log('Cart count: <?php echo $cartCount; ?>');

            // Add this JavaScript to your home page (index.php) to update notification badges in real-time

document.addEventListener('DOMContentLoaded', function() {
    // Function to update notification count
    function updateNotificationCount() {
        // Only run if user is logged in (check if notification icon exists)
        const notificationIcon = document.querySelector('.notification-icon');
        if (!notificationIcon) return;
        
        fetch('get_notification_count.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_unread_count'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationBadge = document.querySelector('.notification-badge');
                
                if (data.unread_count > 0) {
                    // Show badge with count
                    if (notificationBadge) {
                        notificationBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        notificationBadge.classList.remove('hidden');
                    } else {
                        // Create badge if it doesn't exist
                        const badge = document.createElement('span');
                        badge.className = 'notification-badge';
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        notificationIcon.appendChild(badge);
                    }
                } else {
                    // Hide badge when count is 0
                    if (notificationBadge) {
                        notificationBadge.classList.add('hidden');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error updating notification count:', error);
        });
    }

    // Update notification count every 30 seconds
    setInterval(updateNotificationCount, 30000);

    // Also update when the page becomes visible again (user returns from another tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(updateNotificationCount, 1000); // Small delay to ensure any DB updates are complete
        }
    });

    // Update when user focuses on the window
    window.addEventListener('focus', function() {
        setTimeout(updateNotificationCount, 1000);
    });

    // Debug: Log notification and cart counts
    console.log('Notification count: <?php echo $notificationCount; ?>');
    console.log('Cart count: <?php echo $cartCount; ?>');
});
        });

        // Search functionality for the community sharing platform
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-container input[type="text"]');
    const searchIcon = document.querySelector('.search-icons .fa-search');
    const searchContainer = document.querySelector('.search-container');

    // Create search results dropdown
    const searchResults = document.createElement('div');
    searchResults.className = 'search-results';
    searchResults.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-height: 400px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    `;
    searchContainer.style.position = 'relative';
    searchContainer.appendChild(searchResults);

    // Sample data - replace with your actual data or API calls
    const sampleData = {
        tools: [
            { name: 'Electric Drill', category: 'Tools', type: 'item' },
            { name: 'Lawn Mower', category: 'Garden tools', type: 'item' },
            { name: 'Guitar Amplifier', category: 'Music equipment', type: 'item' },
            { name: 'Laptop', category: 'Electronics', type: 'item' },
            { name: 'Circular Saw', category: 'Tools', type: 'item' },
            { name: 'Digital Camera', category: 'Electronics', type: 'item' }
        ],
        services: [
            { name: 'Car Repair', category: 'Mechanic', type: 'service' },
            { name: 'Garden Maintenance', category: 'Gardener', type: 'service' },
            { name: 'Math Tutoring', category: 'Tutoring', type: 'service' },
            { name: 'House Cleaning', category: 'House maid', type: 'service' },
            { name: 'Plumbing Service', category: 'Mechanic', type: 'service' },
            { name: 'English Lessons', category: 'Tutoring', type: 'service' }
        ],
        groups: [
            { name: 'Home Improvement', category: 'Community', type: 'group' },
            { name: 'Garden Enthusiasts', category: 'Community', type: 'group' },
            { name: 'Tech Support Network', category: 'Community', type: 'group' },
            { name: 'Music Lovers', category: 'Community', type: 'group' }
        ]
    };

    // Combine all data for searching
    const allData = [
        ...sampleData.tools,
        ...sampleData.services,
        ...sampleData.groups
    ];

    // Search function
    function performSearch(query) {
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        const filteredResults = allData.filter(item => 
            item.name.toLowerCase().includes(query.toLowerCase()) ||
            item.category.toLowerCase().includes(query.toLowerCase())
        );

        displaySearchResults(filteredResults, query);
    }

    // Display search results
    function displaySearchResults(results, query) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #666;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No results found for "${query}"</p>
                    <p style="font-size: 0.9rem; margin-top: 5px;">Try searching for tools, services, or community groups</p>
                </div>
            `;
        } else {
            let html = '<div style="padding: 10px 0;">';
            
            // Group results by type
            const groupedResults = {
                item: results.filter(r => r.type === 'item'),
                service: results.filter(r => r.type === 'service'),
                group: results.filter(r => r.type === 'group')
            };

            // Display items
            if (groupedResults.item.length > 0) {
                html += '<div style="padding: 10px 15px; background: #f8f9fa; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Items</div>';
                groupedResults.item.forEach(item => {
                    html += createResultItem(item, query);
                });
            }

            // Display services
            if (groupedResults.service.length > 0) {
                html += '<div style="padding: 10px 15px; background: #f8f9fa; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Services</div>';
                groupedResults.service.forEach(item => {
                    html += createResultItem(item, query);
                });
            }

            // Display groups
            if (groupedResults.group.length > 0) {
                html += '<div style="padding: 10px 15px; background: #f8f9fa; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6;">Community Groups</div>';
                groupedResults.group.forEach(item => {
                    html += createResultItem(item, query);
                });
            }

            html += '</div>';
            searchResults.innerHTML = html;
        }

        searchResults.style.display = 'block';
    }

    // Create individual result item
    function createResultItem(item, query) {
        const icon = getTypeIcon(item.type);
        const highlightedName = highlightText(item.name, query);
        const highlightedCategory = highlightText(item.category, query);
        
        return `
            <div class="search-result-item" style="
                padding: 12px 15px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: background-color 0.2s ease;
                display: flex;
                align-items: center;
                gap: 12px;
            " onmouseover="this.style.backgroundColor='#f8f9fa'" 
               onmouseout="this.style.backgroundColor='white'"
               onclick="selectSearchResult('${item.type}', '${item.name}')">
                <div style="color: #4a6fa5; font-size: 1.1rem; width: 20px; text-align: center;">
                    <i class="${icon}"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 500; color: #333; margin-bottom: 2px;">
                        ${highlightedName}
                    </div>
                    <div style="font-size: 0.85rem; color: #666;">
                        ${highlightedCategory}
                    </div>
                </div>
                <div style="color: #999; font-size: 0.8rem;">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        `;
    }

    // Get icon for item type
    function getTypeIcon(type) {
        switch(type) {
            case 'item': return 'fas fa-box';
            case 'service': return 'fas fa-hands-helping';
            case 'group': return 'fas fa-users';
            default: return 'fas fa-search';
        }
    }

    // Highlight matching text
    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span style="background-color: #fff3cd; font-weight: 600;">$1</span>');
    }

    // Handle search result selection
    window.selectSearchResult = function(type, name) {
        searchInput.value = name;
        searchResults.style.display = 'none';
        
        // Navigate based on type
        switch(type) {
            case 'item':
            case 'service':
                window.location.href = `explore.php?search=${encodeURIComponent(name)}`;
                break;
            case 'group':
                window.location.href = `viewgroups.php?search=${encodeURIComponent(name)}`;
                break;
        }
    };

    // Event listeners
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        performSearch(query);
    });

    searchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            performSearch(query);
        }
    });

    // Search on icon click or Enter key
    function executeSearch() {
        const query = searchInput.value.trim();
        if (query) {
            searchResults.style.display = 'none';
            window.location.href = `explore.php?search=${encodeURIComponent(query)}`;
        }
    }

    searchIcon.addEventListener('click', executeSearch);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeSearch();
        }
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchContainer.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Add some additional CSS for better mobile experience
    const additionalStyles = document.createElement('style');
    additionalStyles.textContent = `
        @media (max-width: 768px) {
            .search-results {
                max-height: 300px !important;
                font-size: 14px;
            }
            .search-result-item {
                padding: 10px 12px !important;
            }
        }
        
        .search-results::-webkit-scrollbar {
            width: 6px;
        }
        
        .search-results::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .search-results::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .search-results::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    `;
    document.head.appendChild(additionalStyles);

    console.log('Search functionality initialized successfully');
});

// Optional: Advanced search with filters
function initAdvancedSearch() {
    // This function can be called to enable more advanced search features
    // You can expand this based on your needs
    
    const searchContainer = document.querySelector('.search-container');
    const advancedButton = document.createElement('button');
    advancedButton.innerHTML = '<i class="fas fa-filter"></i>';
    advancedButton.style.cssText = `
        position: absolute;
        right: 45px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 5px;
        border-radius: 3px;
        transition: color 0.3s ease;
    `;
    
    advancedButton.addEventListener('mouseover', function() {
        this.style.color = '#4a6fa5';
    });
    
    advancedButton.addEventListener('mouseout', function() {
        this.style.color = '#666';
    });
    
    advancedButton.addEventListener('click', function() {
        // You can implement advanced search modal or dropdown here
        alert('Advanced search coming soon!');
    });
    
    searchContainer.appendChild(advancedButton);
}
    </script>

    <?php $conn->close(); ?>
</body>
</html>
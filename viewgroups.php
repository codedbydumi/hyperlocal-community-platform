<?php
session_start(); // Start the session to access user data

// Database connection setup
$host = "localhost"; // your database host
$username = "root";  // your database username
$password = "";      // your database password
$database = "final"; // your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in (email session should be set)
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['email']; // Get logged-in user's email

// Fetch data from the 'groups' table - FIXED: Added backticks around 'groups'
$sql = "SELECT * FROM `groups`";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Groups</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        /* Glassmorphism container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: linear-gradient(135deg,rgb(99, 205, 241) 0%, #8b5cf6 100%);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        /* Header section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .back-btn, .home-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .back-btn:hover, .home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .page-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-left: 2rem;
        }

        /* Create Group Button */
        .create-group-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .create-group-btn:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .create-group-btn i {
            font-size: 1.1rem;
        }

        /* Search section */
        .search-container {
            position: relative;
            margin-bottom: 3rem;
        }

        .search-wrapper {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            width: 100%;
            padding: 1.25rem 1.5rem 1.25rem 3.5rem;
            font-size: 1.1rem;
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .search-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 1);
        }

        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6366f1;
            font-size: 1.2rem;
        }

        /* Grid layout */
        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Group cards */
        .group-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .group-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            transition: left 0.6s;
        }

        .group-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
            border-color: #e5e7eb;
        }

        .group-card:hover::before {
            left: 100%;
        }

        .group-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg,rgb(50, 162, 182), #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.5rem;
        }

        .group-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2d3748;
            line-height: 1.3;
        }

        .group-description {
            color: #4a5568;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .group-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #718096;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Action buttons */
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            text-align: center;
        }

        .join-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .join-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .view-btn {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .groups-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .header {
                flex-direction: column;
                text-align: left;
                align-items: flex-start;
            }

            .header-left {
                width: 100%;
                justify-content: flex-start;
            }

            .nav-buttons {
                width: 100%;
                justify-content: flex-start;
            }

            .page-title {
                margin-left: 0;
                margin-top: 1rem;
                align-self: center;
            }

            .create-group-btn {
                width: 100%;
                justify-content: center;
                margin-top: 1rem;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Pulse animation for cards on load */
        .group-card {
            animation: pulse 0.6s ease-out;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="nav-buttons">
                    <a href="javascript:history.back()" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </a>
                    <a href="userdashboard.php" class="home-btn">
                        <i class="fas fa-home"></i>
                        Home
                    </a>
                </div>
                <h1 class="page-title">Discover Groups</h1>
            </div>
            <a href="creategroup.php" class="create-group-btn">
                <i class="fas fa-plus-circle"></i>
                Create Group
            </a>
        </div>

        <div class="search-container">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input 
                    type="text" 
                    id="searchInput" 
                    class="search-input"
                    placeholder="Search for amazing groups to join..."
                    onkeyup="searchGroups()"
                >
            </div>
        </div>

        <div class="groups-grid" id="groupsGrid">
            <?php
            if ($result->num_rows > 0) {
                $cardIndex = 0;
                while ($row = $result->fetch_assoc()) {
                    $groupId = $row["id"];
                    
                    // Check if the user is already a member of this group
                    $checkMembershipQuery = "SELECT * FROM group_memberships WHERE user_email = ? AND group_id = ?";
                    $stmt = $conn->prepare($checkMembershipQuery);
                    $stmt->bind_param("si", $userEmail, $groupId);
                    $stmt->execute();
                    $membershipResult = $stmt->get_result();
                    $isMember = $membershipResult->num_rows > 0;

                    echo "<div class='group-card' style='animation-delay: " . ($cardIndex * 0.1) . "s'>";
                    
                    // Group icon
                    echo "<div class='group-icon'>";
                    echo "<i class='fas fa-users'></i>";
                    echo "</div>";
                    
                    echo "<h3 class='group-title'>" . htmlspecialchars($row["name"]) . "</h3>";
                    echo "<p class='group-description'>" . htmlspecialchars($row["description"]) . "</p>";
                    
                    // Meta information
                    echo "<div class='group-meta'>";
                    echo "<div class='meta-item'>";
                    echo "<i class='fas fa-calendar-alt'></i>";
                    echo "<span>" . date('M j, Y', strtotime($row["created_at"])) . "</span>";
                    echo "</div>";
                    echo "</div>";

                    // Action button
                    if ($isMember) {
                        echo "<a href='group_details.php?group_id=" . $groupId . "' class='action-btn view-btn'>";
                        echo "<i class='fas fa-eye'></i>";
                        echo "View Group";
                        echo "</a>";
                    } else {
                        echo "<a href='join_group.php?group_id=" . $groupId . "' class='action-btn join-btn'>";
                        echo "<i class='fas fa-plus'></i>";
                        echo "Join Now";
                        echo "</a>";
                    }

                    echo "</div>";
                    $cardIndex++;
                }
            } else {
                echo "<div class='empty-state'>";
                echo "<div class='empty-icon'><i class='fas fa-users-slash'></i></div>";
                echo "<h3 class='empty-title'>No Groups Found</h3>";
                echo "<p class='empty-description'>Be the first to create an amazing group!</p>";
                echo "<a href='creategroup.php' class='create-group-btn' style='margin-top: 1rem;'>";
                echo "<i class='fas fa-plus-circle'></i>";
                echo "Create Your First Group";
                echo "</a>";
                echo "</div>";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script>
        // Enhanced search functionality with animations
        function searchGroups() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const groupCards = document.querySelectorAll('.group-card');
            let visibleCount = 0;
            
            groupCards.forEach((card, index) => {
                const title = card.querySelector('.group-title')?.textContent.toLowerCase() || '';
                const description = card.querySelector('.group-description')?.textContent.toLowerCase() || '';
                const searchText = title + ' ' + description;
                
                if (searchText.includes(input)) {
                    card.style.display = '';
                    card.style.animation = `fadeInUp 0.4s ease-out ${index * 0.05}s both`;
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show empty state if no results
            const grid = document.getElementById('groupsGrid');
            if (visibleCount === 0 && input.length > 0) {
                if (!document.querySelector('.search-empty-state')) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'empty-state search-empty-state';
                    emptyState.innerHTML = `
                        <div class="empty-icon"><i class="fas fa-search"></i></div>
                        <h3 class="empty-title">No groups match your search</h3>
                        <p class="empty-description">Try adjusting your search terms or create a new group!</p>
                        <a href="creategroup.php" class="create-group-btn" style="margin-top: 1rem;">
                            <i class="fas fa-plus-circle"></i>
                            Create New Group
                        </a>
                    `;
                    grid.appendChild(emptyState);
                }
            } else {
                const searchEmptyState = document.querySelector('.search-empty-state');
                if (searchEmptyState) {
                    searchEmptyState.remove();
                }
            }
        }

        // Add smooth scrolling and loading states
        document.addEventListener('DOMContentLoaded', function() {
            // Stagger card animations
            const cards = document.querySelectorAll('.group-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.action-btn, .create-group-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });

        // Add intersection observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.group-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>
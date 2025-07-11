<?php
session_start(); // Start the session to store cart items

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";  // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$districtFilter = isset($_GET['district']) ? $_GET['district'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$itemTypeFilter = isset($_GET['item_type']) ? $_GET['item_type'] : '';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query based on filters
$sql = "SELECT listings.*, 
               COALESCE(AVG(ratings.rating), 0) AS avg_rating 
        FROM listings
        LEFT JOIN ratings ON listings.id = ratings.product_id 
        WHERE 1=1";

if (!empty($districtFilter)) {
    $sql .= " AND district = '" . $conn->real_escape_string($districtFilter) . "'";
}

if (!empty($categoryFilter)) {
    $sql .= " AND category = '" . $conn->real_escape_string($categoryFilter) . "'";
}

if (!empty($itemTypeFilter)) {
    $sql .= " AND item_type = '" . $conn->real_escape_string($itemTypeFilter) . "'";
}

// Add search functionality
if (!empty($searchTerm)) {
    $escapedSearch = $conn->real_escape_string($searchTerm);
    $sql .= " AND (name LIKE '%$escapedSearch%' OR description LIKE '%$escapedSearch%')";
}

$sql .= " GROUP BY listings.id"; // Group by product to calculate average rating

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Listings</title>
    <style>
        /* Import Modern Font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        /* CSS Variables for Design System */
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #f8fafc;
            --accent-color: #10b981;
            --accent-hover: #059669;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }
        
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        /* Header Navigation */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 2rem;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
            flex-shrink: 0;
            justify-self: start;
        }
        
        /* Header Search Bar */
        .header-search {
            justify-self: center;
            width: 100%;
            max-width: 500px;
        }
        
        .header-search-box {
            display: flex;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 2px solid var(--border-color);
            background: var(--bg-secondary);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .header-search-box:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .header-search-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.875rem 1.25rem;
            font-size: 0.875rem;
            font-family: inherit;
        }
        
        .header-search-box input:focus {
            outline: none;
        }
        
        .header-search-box input::placeholder {
            color: var(--text-muted);
        }
        
        .header-search-button, .header-voice-button {
            border: none;
            padding: 0.875rem 1rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header-search-button {
            background: var(--primary-color);
            color: white;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-search-button:hover {
            background: var(--primary-hover);
        }
        
        .header-voice-button {
            background: var(--danger-color);
            color: white;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-voice-button:hover {
            background: #dc2626;
        }
        
        .header-voice-button.recording {
            background: var(--accent-color);
            animation: pulse 1.5s infinite;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-lg);
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            justify-self: end;
        }
        
        .back-link:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        /* Main Container - Enhanced for larger content area */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            min-height: calc(100vh - 100px);
        }
        
        /* Modern Sidebar - Reduced width */
        .sidebar {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 120px;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }
        
        .sidebar h2 {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-color);
            position: relative;
        }
        
        .sidebar h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--primary-color);
        }
        
        /* Form Styling */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .form-group select, 
        .form-group input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-secondary);
            font-size: 0.8rem;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        
        .form-group select:focus, 
        .form-group input[type="text"]:focus {
            border-color: var(--primary-color);
            background: var(--bg-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        /* Search Container */
        .search-container {
            position: relative;
        }
        
        .search-box {
            display: flex;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 2px solid var(--border-color);
            background: var(--bg-secondary);
            transition: all 0.2s ease;
        }
        
        .search-box:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.75rem;
            font-size: 0.8rem;
        }
        
        .search-box input:focus {
            outline: none;
        }
        
        .search-button, .voice-search-button {
            border: none;
            padding: 0.75rem;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-button {
            background: var(--primary-color);
            color: white;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .search-button:hover {
            background: var(--primary-hover);
        }
        
        .voice-search-button {
            background: var(--danger-color);
            color: white;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .voice-search-button:hover {
            background: #dc2626;
        }
        
        .voice-search-button.recording {
            background: var(--accent-color);
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        /* Submit Button */
        .form-group button[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: white;
            border: none;
            padding: 0.875rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .form-group button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        /* Main Content - Enhanced and Larger */
        .main-content {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
            overflow-y: auto;
            max-height: calc(100vh - 140px);
            min-height: 600px;
        }
        
        .main-content h2 {
            color: var(--text-primary);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
        }
        
        /* Listings Grid - Modified to show exactly 2 items per row */
        .listings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Exactly 2 columns */
            gap: 2.5rem;
            padding: 1rem 0;
        }
        
        .listing-item {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            min-height: 400px;
        }
        
        .listing-item:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-color);
        }
        
        .listing-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }
        
        .listing-content {
            padding: 2rem;
        }
        
        .listing-item h3 {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.25rem;
            line-height: 1.3;
        }
        
        .listing-item p {
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .listing-item p strong {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        /* Action Buttons - Enhanced */
        .listing-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
        }
        
        .add-to-cart-button, .view-button {
            flex: 1;
            padding: 1rem 1.25rem;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .add-to-cart-button {
            background: var(--accent-color);
            color: white;
        }
        
        .add-to-cart-button:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }
        
        .view-button {
            background: var(--warning-color);
            color: white;
        }
        
        .view-button:hover {
            background: #d97706;
            transform: translateY(-1px);
        }
        
        /* Empty State - Enhanced */
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
            color: var(--text-secondary);
            grid-column: 1 / -1; /* Span across both columns */
        }
        
        .empty-state h3 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }
        
        .empty-state p {
            font-size: 1.125rem;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
            transition: background 0.2s ease;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-hover);
        }
        
        /* Responsive Design - Enhanced */
        @media screen and (max-width: 1200px) {
            .container {
                grid-template-columns: 280px 1fr;
                gap: 1.5rem;
            }
            
            /* Still maintain 2 columns for listings */
            .listings-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }
        
        @media screen and (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 1.5rem;
            }
            
            .sidebar {
                position: relative;
                top: 0;
                max-height: none;
            }
            
            .main-content {
                max-height: none;
                padding: 2.5rem;
            }
            
            /* Still keep 2 columns on tablets */
            .listings-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
            
            .header-content {
                grid-template-columns: 1fr;
                gap: 1rem;
                text-align: center;
            }
            
            .logo {
                justify-self: center;
            }
            
            .header-search {
                justify-self: center;
                width: 100%;
                max-width: none;
            }
            
            .back-link {
                justify-self: center;
            }
        }
        
        @media screen and (max-width: 768px) {
            .header-content {
                padding: 0 1rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            .sidebar {
                padding: 1.5rem;
            }
            
            .main-content {
                padding: 2rem;
            }
            
            .main-content h2 {
                font-size: 2rem;
            }
            
            /* Switch to single column on mobile */
            .listings-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .listing-actions {
                flex-direction: column;
            }
        }
        
        @media screen and (max-width: 480px) {
            /* Ensure single column on very small screens */
            .listings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Modern Header -->
<header class="header">
    <div class="header-content">
        <a href="#" class="logo">🏪 Marketplace</a>
        
        <!-- Search Bar in Header -->
        <div class="header-search">
            <form method="GET" action="" id="header-search-form">
                <!-- Hidden inputs to preserve other filters -->
                <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($itemTypeFilter); ?>">
                <input type="hidden" name="district" value="<?php echo htmlspecialchars($districtFilter); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>">
                
                <div class="header-search-box">
                    <input type="text" id="header-search" name="search" placeholder="Search listings by name or description..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="header-search-button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" id="header-voice-search-btn" class="header-voice-button">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
    </div>
</header>

<!-- Main Container -->
<div class="container">
    <!-- Modern Sidebar -->
    <aside class="sidebar">
        <h2>🔍 Filter Listings</h2>
        <form method="GET" action="" id="filter-form">
            <!-- Preserve search term -->
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
            
            <!-- Item Type Filter -->
            <div class="form-group">
                <label for="item_type">Listing Type</label>
                <select id="item_type" name="item_type" onchange="updateCategories()">
                    <option value="">All Types</option>
                    <option value="item" <?php if ($itemTypeFilter == 'item') echo 'selected'; ?>>Item</option>
                    <option value="service" <?php if ($itemTypeFilter == 'service') echo 'selected'; ?>>Service</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php
                    // Define categories based on item type
                    $itemCategories = [
                        "Electronics", "Furniture", "Vehicles", "Tools & Equipment", 
                        "Sports & Recreation", "Party & Events", "Home & Garden", 
                        "Books & Media", "Clothing & Accessories", "Musical Instruments", 
                        "Photography & Video", "Kitchen & Appliances", "Baby & Kids", 
                        "Fitness Equipment", "Other"
                    ];
                    
                    $serviceCategories = [
                        "Home Services", "Transportation", "Event Services", 
                        "Professional Services", "Personal Care", "Tutoring & Education", 
                        "Pet Services", "Health & Wellness", "Technical Services", 
                        "Creative Services", "Maintenance & Repair", "Cleaning Services", 
                        "Photography & Videography", "Catering & Food", "Other"
                    ];
                    
                    // Show appropriate categories based on current selection
                    $categoriesToShow = [];
                    if ($itemTypeFilter == 'item') {
                        $categoriesToShow = $itemCategories;
                    } elseif ($itemTypeFilter == 'service') {
                        $categoriesToShow = $serviceCategories;
                    } else {
                        $categoriesToShow = array_merge($itemCategories, $serviceCategories);
                    }
                    
                    foreach ($categoriesToShow as $category) {
                        $selected = ($categoryFilter == $category) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($category) . "\" $selected>" . htmlspecialchars($category) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- District Filter -->
            <div class="form-group">
                <label for="district">District</label>
                <select id="district" name="district">
                    <option value="">All Districts</option>
                    <option value="california" <?php if ($districtFilter == 'california') echo 'selected'; ?>>California</option>
                    <option value="texas" <?php if ($districtFilter == 'texas') echo 'selected'; ?>>Texas</option>
                    <option value="florida" <?php if ($districtFilter == 'florida') echo 'selected'; ?>>Florida</option>
                    <option value="new_york" <?php if ($districtFilter == 'new_york') echo 'selected'; ?>>New York</option>
                </select>
            </div>

            <!-- Apply Filters Button -->
            <div class="form-group">
                <button type="submit">Apply Filters</button>
            </div>
        </form>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h2>✨ Available Listings</h2>

        <div class="listings-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='listing-item'>";

                    // Display image if available
                    if ($row['image']) {
                        echo "<img src='image.php?id=" . $row['id'] . "' alt='Image' class='listing-image' />";
                    }

                    echo "<div class='listing-content'>";
                    echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                    echo "<p><strong>Type:</strong> " . ucfirst(htmlspecialchars($row['item_type'])) . "</p>";
                    echo "<p><strong>Category:</strong> " . htmlspecialchars($row['category']) . "</p>";
                    echo "<p><strong>Price (USD):</strong> $" . number_format($row['price_per_day'], 2) . "</p>";
                    echo "<p><strong>Quantity:</strong> " . ($row['quantity'] ? $row['quantity'] : 'N/A') . "</p>";

                    // Format and display location
                    $location = [];
                    if (!empty($row['district'])) $location[] = htmlspecialchars(ucfirst($row['district']));
                    echo "<p><strong>Location:</strong> " . implode(", ", $location) . "</p>";

                    echo "<div class='listing-actions'>";
                    
                    // Add to Cart button
                    echo "<form method='POST' action='add_to_cart.php' style='flex: 1;'>";
                    echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' class='add-to-cart-button'>";
                    echo "<i class='fas fa-cart-plus'></i> Add to Cart";
                    echo "</button>";
                    echo "</form>";

                    echo "<a href='product_detail.php?id=" . $row['id'] . "' class='view-button'>";
                    echo "<i class='fas fa-eye'></i> View Details";
                    echo "</a>";
                    
                    echo "</div>"; // listing-actions
                    echo "</div>"; // listing-content
                    echo "</div>"; // listing-item
                }
            } else {
                echo "<div class='empty-state'>";
                echo "<h3>🔍 No listings found</h3>";
                echo "<p>Try adjusting your filters or search terms to find what you're looking for.</p>";
                echo "</div>";
            }
            ?>
        </div>
    </main>
</div>


<!-- JavaScript for Dynamic Category Updates -->
<script>
// Function to update categories based on item type selection
function updateCategories() {
    const itemType = document.getElementById('item_type').value;
    const categorySelect = document.getElementById('category');
    
    // Define all categories
    const itemCategories = [
        "Electronics", "Furniture", "Vehicles", "Tools & Equipment", 
        "Sports & Recreation", "Party & Events", "Home & Garden", 
        "Books & Media", "Clothing & Accessories", "Musical Instruments", 
        "Photography & Video", "Kitchen & Appliances", "Baby & Kids", 
        "Fitness Equipment", "Other"
    ];
    
    const serviceCategories = [
        "Home Services", "Transportation", "Event Services", 
        "Professional Services", "Personal Care", "Tutoring & Education", 
        "Pet Services", "Health & Wellness", "Technical Services", 
        "Creative Services", "Maintenance & Repair", "Cleaning Services", 
        "Photography & Videography", "Catering & Food", "Other"
    ];
    
    // Clear current options
    categorySelect.innerHTML = '<option value="">All Categories</option>';
    
    // Add appropriate options based on item type
    let categoriesToAdd = [];
    if (itemType === 'item') {
        categoriesToAdd = itemCategories;
    } else if (itemType === 'service') {
        categoriesToAdd = serviceCategories;
    } else {
        categoriesToAdd = itemCategories.concat(serviceCategories);
    }
    
    categoriesToAdd.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categorySelect.appendChild(option);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const headerVoiceSearchBtn = document.getElementById('header-voice-search-btn');
    const headerSearchInput = document.getElementById('header-search');
    
    // Check if browser supports Speech Recognition
    if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        
        recognition.continuous = false;
        recognition.lang = 'en-US';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        
        // When voice search button is clicked
        headerVoiceSearchBtn.addEventListener('click', function() {
            if (headerVoiceSearchBtn.classList.contains('recording')) {
                // Stop recording
                recognition.stop();
                headerVoiceSearchBtn.classList.remove('recording');
                headerVoiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            } else {
                // Start recording
                recognition.start();
                headerVoiceSearchBtn.classList.add('recording');
                headerVoiceSearchBtn.innerHTML = '<i class="fas fa-stop"></i>';
            }
        });
        
        // Processing results
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            headerSearchInput.value = transcript;
            headerVoiceSearchBtn.classList.remove('recording');
            headerVoiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        };
        
        // Handle end of speech recognition
        recognition.onend = function() {
            headerVoiceSearchBtn.classList.remove('recording');
            headerVoiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        };
        
        // Handle errors
        recognition.onerror = function(event) {
            console.error('Speech recognition error', event.error);
            headerVoiceSearchBtn.classList.remove('recording');
            headerVoiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        };
    } else {
        // If browser doesn't support Speech Recognition
        headerVoiceSearchBtn.style.display = 'none';
        console.log('Speech Recognition not supported in this browser');
    }
    
    // Sync forms - when filters change, update search form
    const filterForm = document.getElementById('filter-form');
    const headerSearchForm = document.getElementById('header-search-form');
    
    // Update hidden fields in header search form when filters change
    filterForm.addEventListener('change', function() {
        const itemType = document.getElementById('item_type').value;
        const district = document.getElementById('district').value;
        const category = document.getElementById('category').value;
        
        // Update hidden fields in header search form
        headerSearchForm.querySelector('input[name="item_type"]').value = itemType;
        headerSearchForm.querySelector('input[name="district"]').value = district;
        headerSearchForm.querySelector('input[name="category"]').value = category;
    });
    
    // Update search input in filter form when header search changes
    headerSearchInput.addEventListener('input', function() {
        const searchTerm = headerSearchInput.value;
        filterForm.querySelector('input[name="search"]').value = searchTerm;
    });
});
</script>

<!-- Link to external JavaScript file for the existing voice-to-text functionality -->
<script src="voiceToText.js"></script>

</body>
</html>

<?php
$conn->close();
?>
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

// Query to get all rented items for the logged-in user
// Using prepared statement to prevent SQL injection
$sql = "SELECT * FROM listings WHERE user_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $renter_email);
$stmt->execute();
$result = $stmt->get_result();

// Helper function to get the correct image path
function getImagePath($row) {
    // Check multiple possible image column names
    $imageColumns = ['image', 'image_path', 'image_url', 'photo'];
    
    foreach ($imageColumns as $column) {
        if (isset($row[$column]) && !empty($row[$column])) {
            $imagePath = $row[$column];
            
            // Handle different path formats
            if (strpos($imagePath, 'http') === 0) {
                // Already a full URL
                return $imagePath;
            } elseif (strpos($imagePath, '/') === 0) {
                // Absolute path from root
                return $imagePath;
            } elseif (strpos($imagePath, 'uploads/') === 0) {
                // Relative path starting with uploads/
                return $imagePath;
            } else {
                // Assume it's just a filename, prepend uploads/
                return 'uploads/' . $imagePath;
            }
        }
    }
    
    return null;
}

// Helper function to safely escape HTML - handles null values
function safeHtmlspecialchars($value, $flags = ENT_QUOTES, $encoding = 'UTF-8') {
    return htmlspecialchars($value ?? '', $flags, $encoding);
}

// Helper function to safely format numbers - handles null values
function safeNumberFormat($value, $decimals = 2) {
    return number_format($value ?? 0, $decimals);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Listed Items</title>
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
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .item-image {
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .item-card:hover .item-image img {
            transform: scale(1.05);
        }

        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            color: #6c757d;
            font-size: 14px;
            height: 200px;
            text-align: center;
        }

        .no-image::before {
            content: "📷";
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.5;
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

        .price-tag {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(45deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
        }

        .price-tag span {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 400;
        }

        .item-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
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

        .image-error {
            background: #f8f9fa;
            color: #dc3545;
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
            font-size: 0.8rem;
            display: none;
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
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Your Listed Items</h2>
        <a href="userdashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="items-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="item-card">
                    <div class="item-image">
                        <?php 
                        $imagePath = getImagePath($row);
                        if ($imagePath): 
                        ?>
                            <img src="<?php echo safeHtmlspecialchars($imagePath); ?>" 
                                 alt="<?php echo safeHtmlspecialchars($row['name']); ?>"
                                 loading="lazy"
                                 onerror="handleImageError(this, '<?php echo safeHtmlspecialchars($imagePath); ?>')">
                        <?php else: ?>
                            <div class="no-image">No Image Available</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-content">
                        <div class="item-header">
                            <div>
                                <div class="item-name"><?php echo safeHtmlspecialchars($row['name']); ?></div>
                                <div class="item-type"><?php echo safeHtmlspecialchars($row['item_type']); ?></div>
                            </div>
                        </div>
                        
                        <div class="price-tag">
                            $ <?php echo safeNumberFormat($row['price_per_day']); ?>
                            <span>/day</span>
                        </div>
                        
                        <div class="item-details">
                            <div class="detail-item">
                                <span class="detail-label">Quantity</span>
                                <span class="detail-value"><?php echo safeHtmlspecialchars($row['quantity']); ?> available</span>
                            </div>
                            <?php if (isset($row['created_at']) && !empty($row['created_at'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Listed On</span>
                                <span class="detail-value"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="location">
                            📍 <?php echo safeHtmlspecialchars($row['city']); ?>, <?php echo safeHtmlspecialchars($row['district']); ?>
                        </div>
                        
                        <?php if (!empty($row['description'])): ?>
                            <div class="description">
                                <?php echo nl2br(safeHtmlspecialchars($row['description'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-items">
            <h3>No Items Found</h3>
            <p>You haven't listed any items yet!</p>
        </div>
    <?php endif; ?>
</div>

<script>
function handleImageError(img, originalPath) {
    console.log('Image failed to load:', originalPath);
    
    // Try alternative paths
    const alternatives = [
        originalPath.replace('uploads/', './uploads/'),
        originalPath.replace('uploads/', '../uploads/'),
        originalPath.replace(/^(?!\/)/, './'),
        originalPath.replace(/^(?!\/)/, '../')
    ];
    
    let currentIndex = 0;
    
    function tryNextPath() {
        if (currentIndex < alternatives.length) {
            const nextPath = alternatives[currentIndex];
            currentIndex++;
            
            // Create a new image to test if it loads
            const testImg = new Image();
            testImg.onload = function() {
                img.src = nextPath;
            };
            testImg.onerror = function() {
                tryNextPath();
            };
            testImg.src = nextPath;
        } else {
            // All alternatives failed, show no image placeholder
            img.parentElement.innerHTML = '<div class="no-image">Image not found<br><small>Path: ' + originalPath + '</small></div>';
        }
    }
    
    tryNextPath();
}

// Add some debugging
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.item-image img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            console.log('Image loaded successfully:', this.src);
        });
    });
});
</script>

</body>
</html>

<?php
// Close the prepared statement and connection
$stmt->close();
$conn->close();
?>
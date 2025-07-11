<?php
session_start(); // Start the session to access user data

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";  // Update with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemType = $_POST['itemType'];
    $category = $_POST['category'];
    $customCategory = isset($_POST['customCategory']) ? $_POST['customCategory'] : '';
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : null;
    $district = $_POST['district'];
    $city = $_POST['city'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    // Use custom category if "Other" is selected
    if ($category === 'other' && !empty($customCategory)) {
        $category = $customCategory;
    }

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = $targetFilePath;
        } else {
            echo "<div class='response'>Error uploading the image.</div>";
        }
    }

    $itemType = $conn->real_escape_string($itemType);
    $category = $conn->real_escape_string($category);
    $name = $conn->real_escape_string($name);
    $price = (float)$price;
    $district = $conn->real_escape_string($district);
    $city = $conn->real_escape_string($city);
    $description = $conn->real_escape_string($description);
    $email = $conn->real_escape_string($userEmail);
    if ($imagePath) {
        $imagePath = $conn->real_escape_string($imagePath);
    }

    $sql = "INSERT INTO listings (item_type, category, name, price_per_day, quantity, district, city, user_email, description, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $quantityParam = $quantity ? $quantity : null;
        $stmt->bind_param("sssdssssss", $itemType, $category, $name, $price, $quantityParam, $district, $city, $email, $description, $imagePath);

        if ($stmt->execute()) {
            echo "<div class='response'>Listing has been successfully added!</div>";
        } else {
            echo "<div class='response'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='response'>Error preparing the query: " . $conn->error . "</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Item or Service</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            text-align: center;
            color: white;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="70" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="70" cy="20" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 1.1rem;
            margin-top: 10px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .back-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .form-container {
            padding: 40px;
        }

        .user-info {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info::before {
            content: '👤';
            font-size: 1.2rem;
        }

        .form-grid {
            display: grid;
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .custom-category-field {
            display: none;
            margin-top: 15px;
        }

        .custom-category-field input {
            border-color: #f093fb;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .response {
            padding: 20px;
            margin: 20px 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            font-weight: 500;
            text-align: center;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: block;
            padding: 15px;
            border: 2px dashed #e1e5e9;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-upload-label:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="userdashboard.php" class="back-link">← Back to Dashboard</a>
            <h1>List Your Rental</h1>
           
        </div>

        <div class="form-container">
            <div class="user-info">
                 <p>Share your items and services with the community</p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label for="itemType">What are you listing?</label>
                    <select id="itemType" name="itemType" onchange="showTypeFields()" required>
                        <option value="">Choose an option</option>
                        <option value="item">Physical Item</option>
                        <option value="service">Service</option>
                    </select>
                </div>

                <div class="form-group hidden" id="categoryDiv">
                    <label for="category">Category</label>
                    <select id="category" name="category" onchange="showCustomCategory()" required>
                        <option value="">Select a category</option>
                    </select>
                    <div id="customCategoryDiv" class="custom-category-field">
                        <input type="text" id="customCategory" name="customCategory" placeholder="Enter your custom category">
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="What's the name of your item or service?" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price per Day (USD)</label>
                        <input type="number" id="price" name="price" min="1" step="0.01" placeholder="0.00" required>
                    </div>

                    <div id="quantityDiv" class="form-group hidden">
                        <label for="quantity">Available Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" placeholder="How many available?">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your item or service in detail..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="district">State</label>
                        <select id="district" name="district" onchange="populateCities()" required>
                            <option value="">Select your state</option>
                            <option value="california">California</option>
                            <option value="texas">Texas</option>
                            <option value="florida">Florida</option>
                            <option value="new_york">New York</option>
                        </select>
                    </div>

                    <div class="form-group hidden" id="cityDiv">
                        <label for="city">City</label>
                        <select id="city" name="city" required>
                            <option value="">Select your city</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Upload Image</label>
                    <div class="file-upload">
                        <input type="file" id="image" name="image" accept="image/*">
                        <label for="image" class="file-upload-label">
                            📸 Click to upload an image or drag and drop
                        </label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">🚀 Create Listing</button>
            </form>
        </div>
    </div>

    <script>
        // Category definitions
        const itemCategories = [
            "Electronics",
            "Furniture",
            "Vehicles",
            "Tools & Equipment",
            "Sports & Recreation",
            "Party & Events",
            "Home & Garden",
            "Books & Media",
            "Clothing & Accessories",
            "Musical Instruments",
            "Photography & Video",
            "Kitchen & Appliances",
            "Baby & Kids",
            "Fitness Equipment",
            "Other"
        ];

        const serviceCategories = [
            "Home Services",
            "Transportation",
            "Event Services",
            "Professional Services",
            "Personal Care",
            "Tutoring & Education",
            "Pet Services",
            "Health & Wellness",
            "Technical Services",
            "Creative Services",
            "Maintenance & Repair",
            "Cleaning Services",
            "Photography & Videography",
            "Catering & Food",
            "Other"
        ];

        function showTypeFields() {
            const itemType = document.getElementById("itemType").value;
            const quantityDiv = document.getElementById("quantityDiv");
            const categoryDiv = document.getElementById("categoryDiv");
            const categoryDropdown = document.getElementById("category");
            
            // Show/hide quantity field for items
            if (itemType === "item") {
                quantityDiv.classList.remove("hidden");
            } else {
                quantityDiv.classList.add("hidden");
            }
            
            // Show category dropdown
            if (itemType) {
                categoryDiv.classList.remove("hidden");
                populateCategories(itemType);
                categoryDropdown.setAttribute('required', 'required');
            } else {
                categoryDiv.classList.add("hidden");
                categoryDropdown.removeAttribute('required');
            }
            
            // Hide custom category field
            document.getElementById("customCategoryDiv").style.display = "none";
        }

        function populateCategories(type) {
            const categoryDropdown = document.getElementById("category");
            categoryDropdown.innerHTML = '<option value="">Select a category</option>';
            
            const categories = type === "item" ? itemCategories : serviceCategories;
            
            categories.forEach(function(category) {
                const option = document.createElement("option");
                option.value = category.toLowerCase().replace(/\s+/g, '_').replace(/&/g, 'and');
                option.textContent = category;
                categoryDropdown.appendChild(option);
            });
        }

        function showCustomCategory() {
            const category = document.getElementById("category").value;
            const customCategoryDiv = document.getElementById("customCategoryDiv");
            const customCategoryInput = document.getElementById("customCategory");
            
            if (category === "other") {
                customCategoryDiv.style.display = "block";
                customCategoryInput.setAttribute('required', 'required');
            } else {
                customCategoryDiv.style.display = "none";
                customCategoryInput.removeAttribute('required');
                customCategoryInput.value = "";
            }
        }

        function populateCities() {
            const district = document.getElementById("district").value;
            const cityDiv = document.getElementById("cityDiv");
            const cityDropdown = document.getElementById("city");

            cityDropdown.innerHTML = '<option value="">Select your city</option>';
            
            if (district) {
                cityDiv.classList.remove("hidden");
            } else {
                cityDiv.classList.add("hidden");
            }

            let cities = [];

            if (district === "california") {
                cities = ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno", "Sacramento"];
            } else if (district === "texas") {
                cities = ["Houston", "Dallas", "Austin", "San Antonio", "Fort Worth", "El Paso"];
            } else if (district === "florida") {
                cities = ["Miami", "Orlando", "Tampa", "Jacksonville", "Tallahassee"];
            } else if (district === "new_york") {
                cities = ["New York City", "Buffalo", "Rochester", "Albany", "Yonkers"];
            }

            cities.forEach(function(city) {
                const option = document.createElement("option");
                option.value = city;
                option.textContent = city;
                cityDropdown.appendChild(option);
            });
        }

        // File upload feedback
        document.getElementById('image').addEventListener('change', function(e) {
            const label = document.querySelector('.file-upload-label');
            if (e.target.files.length > 0) {
                label.textContent = `📸 ${e.target.files[0].name}`;
                label.style.color = '#667eea';
            } else {
                label.textContent = '📸 Click to upload an image or drag and drop';
                label.style.color = '';
            }
        });
    </script>
</body>
</html>
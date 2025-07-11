<?php 
session_start(); // Start the session to access cart items

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
   
} else {
    // Connect to the database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "final";  // Database name
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        
    // Define total price variable
    $totalPrice = 0;
        
    // Fetch cart items from the session and the product details from the database
    $cartItems = [];
    foreach ($_SESSION['cart'] as $productId => $cartItem) {
        $sql = "SELECT name, price_per_day FROM listings WHERE id = '$productId'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $product['quantity'] = $cartItem['quantity'];
            $product['totalPrice'] = $product['price_per_day'] * $cartItem['quantity'];
            $product['id'] = $productId;  // Make sure the product ID is included
            $cartItems[] = $product;
            $totalPrice += $product['totalPrice']; // Accumulate the total price
        }
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
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

        .cart-items {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .cart-item {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .item-info {
            flex: 1;
            margin-bottom: 15px;
        }

        .item-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .item-link {
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .item-link:hover {
            color: #667eea;
            text-decoration: none;
        }

        .item-price {
            font-size: 1.1rem;
            color: #28a745;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .item-quantity {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .item-total {
            font-size: 1.3rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 15px;
        }

        .remove-btn {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            align-self: flex-end;
            text-align: center;
        }

        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        .cart-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .cart-summary h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
        }

        .total-amount {
            font-size: 2.2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 20px;
        }

        .checkout-btn {
            background: white;
            color: #667eea;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            display: block;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-cart h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #495057;
        }

        .empty-cart p {
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        .shop-now-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: inline-block;
        }

        .shop-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }

            h2 {
                font-size: 2rem;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .cart-items {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .total-amount {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .cart-item {
                padding: 20px;
            }

            .item-name {
                font-size: 1.2rem;
            }

            .item-total {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Your Cart</h2>
        <a href="index.php" class="back-btn">← Back to Home</a>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <h3>Your Cart is Empty</h3>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="index.php" class="shop-now-btn">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">
                            <a href="product_detail.php?id=<?php echo $item['id']; ?>" class="item-link">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                        </div>
                        <div class="item-price">$<?php echo number_format($item['price_per_day'], 2); ?> per day</div>
                        <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                        <div class="item-total">Total: $<?php echo number_format($item['totalPrice'], 2); ?></div>
                    </div>
                    <a href="remove_from_cart.php?product_id=<?php echo $item['id']; ?>" class="remove-btn">Remove</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <h3>Order Summary</h3>
            <div class="total-amount">$<?php echo number_format($totalPrice, 2); ?></div>
           
        </div>
    <?php endif; ?>
</div>

</body>
</html>
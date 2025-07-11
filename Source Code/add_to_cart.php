<?php
session_start(); // Start the session to store cart items

// Check if the product ID is set
if (isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];

    // Initialize cart if it's not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += 1; // Increment quantity if product already exists in the cart
    } else {
        // Add product to cart with a quantity of 1 if it's not already in the cart
        $_SESSION['cart'][$productId] = [
            'product_id' => $productId,
            'quantity' => 1
        ];
    }
}

// Redirect back to the previous page (e.g., the listings page)
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>

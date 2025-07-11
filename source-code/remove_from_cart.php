<?php
session_start();

// Check if product ID is set and exists in the cart
if (isset($_GET['product_id']) && isset($_SESSION['cart'][$_GET['product_id']])) {
    // Remove the product from the cart
    unset($_SESSION['cart'][$_GET['product_id']]);
}

// Redirect back to the cart page
header("Location: view_cart.php");
exit;
?>

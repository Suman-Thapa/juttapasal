<?php
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/cart.php');
    exit;
}

$receiverName    = trim($_POST['receiver_name'] ?? '');
$receiverPhone   = trim($_POST['receiver_phone'] ?? '');
$city            = trim($_POST['city'] ?? '');
$deliveryAddress = trim($_POST['delivery_address'] ?? '');
$postalCode      = trim($_POST['postal_code'] ?? '');

if (!$receiverName || !$receiverPhone || !$city || !$deliveryAddress) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please fill in all required delivery fields.'];
    header('Location: ../pages/checkout.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $lockStmt = $pdo->prepare(
        'SELECT c.Product_id, c.Item_quantity, p.Price, p.Quantity AS stock, p.Brand, p.Description
         FROM cart_item c JOIN product p ON p.Product_id = c.Product_id
         WHERE c.User_id = ? FOR UPDATE'
    );
    $lockStmt->execute([$userId]);
    $items = $lockStmt->fetchAll();

    if (!$items) {
        $pdo->rollBack();
        header('Location: ../pages/cart.php');
        exit;
    }

    foreach ($items as $item) {
        if ($item['Item_quantity'] > $item['stock']) {
            throw new Exception("Not enough stock for {$item['Brand']} - {$item['Description']}.");
        }
    }

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['Price'] * $item['Item_quantity'];
    }
    $shipping = 150; // flat delivery charge — adjust or wire up free-shipping thresholds as needed
    $total    = $subtotal + $shipping;

    $stmt = $pdo->prepare(
        'INSERT INTO orders (
            User_id, Receiver_Name, Receiver_Phone, City, Delivery_Address, Postal_Code,
            Total_Amount, Status, Payment_Method, Payment_Status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, "processing", "cod", "unpaid"
        )'
    );
    $stmt->execute([$userId, $receiverName, $receiverPhone, $city, $deliveryAddress, $postalCode, $total]);

    $orderId = (int) $pdo->lastInsertId();

    $itemStmt  = $pdo->prepare(
        'INSERT INTO order_items (Order_id, Product_id, Product_Name, Price, Quantity) VALUES (?, ?, ?, ?, ?)'
    );
    $stockStmt = $pdo->prepare('UPDATE product SET Quantity = Quantity - ? WHERE Product_id = ?');

    foreach ($items as $item) {
        $productName = $item['Brand'] . ' - ' . $item['Description'];
        $itemStmt->execute([$orderId, $item['Product_id'], $productName, $item['Price'], $item['Item_quantity']]);
        $stockStmt->execute([$item['Item_quantity'], $item['Product_id']]);
    }

    $pdo->prepare('DELETE FROM cart_item WHERE User_id = ?')->execute([$userId]);

    $pdo->commit();

    $_SESSION['flash'] = ['type' => 'success', 'message' => "Order #$orderId placed! Pay " . money($total) . " on delivery."];
    header('Location: ../pages/orders.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Checkout failed: ' . $e->getMessage()];
    header('Location: ../pages/checkout.php');
    exit;
}

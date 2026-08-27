<?php
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId = $_SESSION['user_id'];
$method = $_GET['method'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($method, ['esewa', 'khalti'], true)) {
    header('Location: ../pages/checkout.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT c.Product_id, c.Item_quantity, p.Brand, p.Description, p.Price, p.Quantity AS stock
     FROM cart_item c JOIN product p ON p.Product_id = c.Product_id
     WHERE c.User_id = ?'
);
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

if (!$rows) {
    header('Location: ../pages/cart.php');
    exit;
}

$items = [];
$subtotal = 0;
foreach ($rows as $row) {
    $items[] = [
        'product_id' => $row['Product_id'],
        'name'       => $row['Brand'] . ' - ' . $row['Description'],
        'price'      => $row['Price'],
        'quantity'   => $row['Item_quantity'],
    ];
    $subtotal += $row['Price'] * $row['Item_quantity'];
}
$shipping = 150;
$total    = $subtotal + $shipping;

$_SESSION['pending_items'] = $items;
$_SESSION['pending_total'] = $total;
$_SESSION['delivery'] = [
    'receiver_name'    => trim($_POST['receiver_name'] ?? ''),
    'receiver_phone'   => trim($_POST['receiver_phone'] ?? ''),
    'city'             => trim($_POST['city'] ?? ''),
    'delivery_address' => trim($_POST['delivery_address'] ?? ''),
    'postal_code'      => trim($_POST['postal_code'] ?? ''),
];

header('Location: ' . ($method === 'esewa' ? 'payment_esewa.php' : 'payment_khalti.php'));
exit;

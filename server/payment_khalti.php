<?php
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId = $_SESSION['user_id'];
$khalti_secret_key = 'c1baae33db524a35a67d4641c84f9077'; // sandbox key — replace with your own

$repayOrderId = $_SESSION['repay_order_id'] ?? null;

// ==================================================================
// STEP 2: Callback from Khalti (?pidx present)
// ==================================================================





if (isset($_GET['pidx'])) {

    $pidx = $_GET['pidx'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://dev.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => [
            "Authorization: key $khalti_secret_key",
            'Content-Type: application/json',
        ],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    $data   = json_decode($response, true);
    $status = $data['status'] ?? '';
    $ref_id = $data['transaction_id'] ?? '';

    $items = $_SESSION['pending_items'] ?? [];
    $total = $_SESSION['pending_total'] ?? 0;

    if ($status !== 'Completed') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Khalti payment was not completed. Please try again.'];
        header('Location: ' . ($repayOrderId ? '../pages/orders.php' : '../pages/cart.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($repayOrderId) {
            $stmt = $pdo->prepare(
                'UPDATE orders SET Payment_Status = "paid", Payment_Method = "khalti", Ref_Id = ?, Transaction_Uuid = ?
                 WHERE Order_id = ? AND User_id = ?'
            );
            $stmt->execute([$ref_id, $pidx, $repayOrderId, $userId]);

            $pdo->commit();
            unset($_SESSION['repay_order_id'], $_SESSION['pending_total']);

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Payment successful! Order #$repayOrderId is now paid."];
            header('Location: ../pages/orders.php');
            exit;
        }

        if (empty($items)) {
            throw new Exception('No items found to complete this order.');
        }

        foreach ($items as $item) {
            $stockRow = $pdo->prepare('SELECT Quantity FROM product WHERE Product_id = ? FOR UPDATE');
            $stockRow->execute([$item['product_id']]);
            $stock = $stockRow->fetchColumn();
            if ($item['quantity'] > $stock) {
                throw new Exception("Not enough stock for {$item['name']}.");
            }
        }

        $delivery = $_SESSION['delivery'] ?? null;
        if (!$delivery) {
            throw new Exception('Delivery information not found.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO orders (
                User_id, Receiver_Name, Receiver_Phone, City, Delivery_Address, Postal_Code,
                Total_Amount, Status, Payment_Method, Payment_Status, Ref_Id, Transaction_Uuid
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, "processing", "khalti", "paid", ?, ?
            )'
        );
        $stmt->execute([
            $userId,
            $delivery['receiver_name'],
            $delivery['receiver_phone'],
            $delivery['city'],
            $delivery['delivery_address'],
            $delivery['postal_code'],
            $total,
            $ref_id,
            $pidx,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $itemStmt  = $pdo->prepare(
            'INSERT INTO order_items (Order_id, Product_id, Product_Name, Price, Quantity) VALUES (?, ?, ?, ?, ?)'
        );
        $stockStmt = $pdo->prepare('UPDATE product SET Quantity = Quantity - ? WHERE Product_id = ?');

        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }

        $pdo->prepare('DELETE FROM cart_item WHERE User_id = ?')->execute([$userId]);

        $pdo->commit();
        unset($_SESSION['pending_items'], $_SESSION['pending_total'], $_SESSION['delivery']);

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Payment successful! Order #$orderId confirmed via Khalti."];
        header('Location: ../pages/orders.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Khalti checkout failed: ' . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Checkout failed: ' . $e->getMessage()];
        header('Location: ' . ($repayOrderId ? '../pages/orders.php' : '../pages/checkout.php'));
        exit;
    }
}

// ==================================================================
// STEP 1: Initiate
// ==================================================================
$items = $_SESSION['pending_items'] ?? [];
$total = $_SESSION['pending_total'] ?? 0;

if (!$repayOrderId && (empty($items) || !$total)) {
    header('Location: ../pages/cart.php');
    exit;
}
if ($repayOrderId && !$total) {
    header('Location: ../pages/orders.php');
    exit;
}

$stmt = $pdo->prepare('SELECT First_Name, Last_Name, Email FROM Users WHERE ID = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
$fullName = trim(($user['First_Name'] ?? '') . ' ' . ($user['Last_Name'] ?? ''));

$amount_paisa = (int) round($total * 100);

// IMPORTANT: update these to your real deployed host before going live
$return_url  = 'http://localhost/KicksPayment-integration/server/payment_khalti.php';
$website_url = 'http://localhost/KicksPayment-integration';

$init_curl = curl_init();
curl_setopt_array($init_curl, [
    CURLOPT_URL => 'https://dev.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'return_url'          => $return_url,
        'website_url'         => $website_url,
        'amount'              => $amount_paisa,
        'purchase_order_id'   => "KICKS_{$userId}_" . time(),
        'purchase_order_name' => 'Kicks Order',
        'customer_info' => [
            'name'  => $fullName,
            'email' => $user['Email'] ?? '',
        ],
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: key $khalti_secret_key",
        'Content-Type: application/json',
    ],
]);
$init_response = curl_exec($init_curl);
curl_close($init_curl);

$init_data = json_decode($init_response, true);

if (isset($init_data['payment_url'])) {
    header('Location: ' . $init_data['payment_url']);
    exit;
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Khalti initiate failed. Please try again.'];
    header('Location: ' . ($repayOrderId ? '../pages/orders.php' : '../pages/cart.php'));
    exit;
}
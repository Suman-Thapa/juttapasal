<?php
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId = $_SESSION['user_id'];

// eSewa sandbox test credentials — replace with your live values in production
$esewa_product_code = 'EPAYTEST';
$esewa_secret_key   = '8gBm/:&EnhH.1/q';
$esewa_form_url     = 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';

function esewaSignature(string $secret, string $message): string {
    return base64_encode(hash_hmac('sha256', $message, $secret, true));
}

$repayOrderId = $_SESSION['repay_order_id'] ?? null;

// ==================================================================
// STEP 2: Callback from eSewa (?data=base64 present)
// ==================================================================
if (isset($_GET['data'])) {

    $decoded = json_decode(base64_decode($_GET['data']), true);

    $status           = $decoded['status'] ?? '';
    $ref_id           = $decoded['transaction_code'] ?? '';
    $transaction_uuid = $decoded['transaction_uuid'] ?? '';

    $items = $_SESSION['pending_items'] ?? [];
    $total = $_SESSION['pending_total'] ?? 0;

    if ($status !== 'COMPLETE') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'eSewa payment was not completed. Please try again.'];
        header('Location: ' . ($repayOrderId ? '../pages/orders.php' : '../pages/cart.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($repayOrderId) {
            // ---- Repay flow: settle payment on an existing unpaid order ----
            $stmt = $pdo->prepare(
                'UPDATE orders SET Payment_Status = "paid", Payment_Method = "esewa", Ref_Id = ?, Transaction_Uuid = ?
                 WHERE Order_id = ? AND User_id = ?'
            );
            $stmt->execute([$ref_id, $transaction_uuid, $repayOrderId, $userId]);

            $pdo->commit();
            unset($_SESSION['repay_order_id'], $_SESSION['pending_total']);

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Payment successful! Order #$repayOrderId is now paid."];
            header('Location: ../pages/orders.php');
            exit;
        }

        // ---- New order from cart ----
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
                ?, ?, ?, ?, ?, ?, ?, "processing", "esewa", "paid", ?, ?
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
            $transaction_uuid,
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

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Payment successful! Order #$orderId confirmed via eSewa."];
        header('Location: ../pages/orders.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Checkout failed: ' . $e->getMessage()];
        header('Location: ' . ($repayOrderId ? '../pages/orders.php' : '../pages/cart.php'));
        exit;
    }
}

// ==================================================================
// STEP 1: Initiate — build and auto-submit the eSewa payment form
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

$transaction_uuid = date('Ymd-His') . '-' . $userId;
$amount            = number_format($total, 2, '.', '');
$tax_amount        = '0';
$total_amount      = $amount;

$signed_field_names = 'total_amount,transaction_uuid,product_code';
$message   = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$esewa_product_code}";
$signature = esewaSignature($esewa_secret_key, $message);

// IMPORTANT: update these two to your real deployed host before going live
$success_url = 'http://localhost/KicksPayment-integration/server/payment_esewa.php';
$failure_url = 'http://localhost/KicksPayment-integration/pages/' . ($repayOrderId ? 'orders.php?error=Payment+cancelled' : 'cart.php?error=Payment+cancelled');
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Redirecting to eSewa…</title></head>
<body style="font-family:'Poppins',sans-serif;text-align:center;margin-top:80px;">
  <p>Redirecting to eSewa, please wait…</p>
  <form id="esewa-form" action="<?= $esewa_form_url ?>" method="POST">
    <input type="hidden" name="amount" value="<?= $amount ?>">
    <input type="hidden" name="tax_amount" value="<?= $tax_amount ?>">
    <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
    <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
    <input type="hidden" name="product_code" value="<?= $esewa_product_code ?>">
    <input type="hidden" name="product_service_charge" value="0">
    <input type="hidden" name="product_delivery_charge" value="0">
    <input type="hidden" name="success_url" value="<?= $success_url ?>">
    <input type="hidden" name="failure_url" value="<?= $failure_url ?>">
    <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>">
    <input type="hidden" name="signature" value="<?= $signature ?>">
  </form>
  <script>document.getElementById('esewa-form').submit();</script>
</body>
</html>

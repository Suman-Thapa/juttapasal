<?php
require_once __DIR__ . '/../server/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId  = $_SESSION['user_id'];
$orderId = (int) ($_GET['order_id'] ?? $_POST['order_id'] ?? 0);

if (!$orderId) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE Order_id = ? AND User_id = ?');
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Order not found.'];
    header('Location: orders.php');
    exit;
}
if ($order['Payment_Status'] === 'paid') {
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'This order is already paid.'];
    header('Location: orders.php');
    exit;
}

$itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE Order_id = ?');
$itemStmt->execute([$orderId]);
$lines = $itemStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';
    if (in_array($method, ['esewa', 'khalti'], true)) {
        $_SESSION['repay_order_id'] = $orderId;
        $_SESSION['pending_total']  = $order['Total_Amount'];
        header('Location: ' . ($method === 'esewa' ? '../server/payment_esewa.php' : '../server/payment_khalti.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Pay for Order #<?= (int)$orderId ?> — Kicks</title>
<link rel="stylesheet" href="../styles/login.css" />
<link rel="stylesheet" href="../styles/style.css?99" />
<link rel="stylesheet" href="../styles/footer.css" />
<link rel="stylesheet" href="../styles/orders.css" />
<link rel="shortcut icon" href="../images/header_icon.ico" type="image/x-icon" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="../scripts/app.js?payorder"></script>
</head>
<body>
<header>
  <nav class="flex">
    <a href="../index.php"><img src="../images/logo.png" alt="logo" class="logo" /></a>
    <ul class="links flex">
      <li><a href="../index.php">Home</a></li>
      <li><a href="./shop.php">Shop</a></li>
      <li><a href="./orders.php">My Orders</a></li>
      <li><a href="./cart.php"><i class="fa-solid fa-cart-shopping"><span class="badge bg-dark" id="cart_count">0</span></i></a></li>
      <li id="login_btn"><a href="./login.php">Login</a></li>
    </ul>
    <div class="burger"><div class="line1"></div><div class="line2"></div><div class="line3"></div></div>
  </nav>
</header>

<main class="pay-order-main">
  <h1 class="orders-title">Complete your payment</h1>
  <p class="orders-sub">Order #<?= (int)$orderId ?> is waiting to be paid</p>

  <div class="pay-order-summary">
    <?php foreach ($lines as $line): ?>
      <div class="order-line">
        <div class="order-line-info">
          <div class="order-line-name"><?= h($line['Product_Name']) ?></div>
          <div class="order-line-sub">AED<?= number_format($line['Price'], 2) ?> × <?= (int)$line['Quantity'] ?></div>
        </div>
        <div class="order-line-total">AED<?= number_format($line['Price'] * $line['Quantity'], 2) ?></div>
      </div>
    <?php endforeach; ?>
    <div class="order-total-row"><span>Amount due</span><b>AED<?= number_format($order['Total_Amount'], 2) ?></b></div>
  </div>

  <form method="post" class="pay-form-panel">
    <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
    <input type="hidden" name="payment_method" id="payment_method_input" value="">

    <div class="kicks-payment-options">
      <label class="kicks-payment-card" id="label-esewa">
        <input type="radio" name="pm_radio" value="esewa" onchange="selectRepay('esewa')">
        <div class="kicks-payment-card-inner">
          <span class="kicks-payment-icon"><i class="fa-solid fa-wallet" style="color:#60bb46;"></i></span>
          <div><div class="kicks-payment-name">eSewa</div><div class="kicks-payment-sub">Pay online instantly</div></div>
          <span class="kicks-payment-check">✓</span>
        </div>
      </label>
      <label class="kicks-payment-card" id="label-khalti">
        <input type="radio" name="pm_radio" value="khalti" onchange="selectRepay('khalti')">
        <div class="kicks-payment-card-inner">
          <span class="kicks-payment-icon"><i class="fa-solid fa-wallet" style="color:#5c2d91;"></i></span>
          <div><div class="kicks-payment-name">Khalti</div><div class="kicks-payment-sub">Pay online instantly</div></div>
          <span class="kicks-payment-check">✓</span>
        </div>
      </label>
    </div>

    <button type="submit" class="kicks-place-btn" id="pay-btn" disabled>Select a payment method</button>
  </form>
  <a href="orders.php" class="back-link">← Back to My Orders</a>
</main>
<?php include "footer.php" ?>
<script src="../scripts/animations.js"></script>
<script>
function selectRepay(method) {
  document.querySelectorAll('.kicks-payment-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('label-' + method).classList.add('selected');
  document.getElementById('payment_method_input').value = method;
  const btn = document.getElementById('pay-btn');
  btn.disabled = false;
  btn.textContent = method === 'esewa' ? 'Pay with eSewa →' : 'Pay with Khalti →';
}
$(document).ready(function () { get_user_info(); });
</script>
</body>
</html>

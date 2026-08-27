<?php
require_once __DIR__ . '/../server/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM orders WHERE User_id = ? ORDER BY Created_At DESC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$itemStmt = $pdo->prepare('SELECT * FROM order_items WHERE Order_id = ?');

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$trackerSteps = [
    'processing' => '⏳ Processing',
    'packing'    => '📦 Packing',
    'shipping'   => '🚚 Shipping',
    'delivered'  => '✓ Delivered',
];
$trackerKeys = array_keys($trackerSteps);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>My Orders — Kicks</title>
<link rel="stylesheet" href="../styles/login.css" />
<link rel="stylesheet" href="../styles/style.css?99" />
<link rel="stylesheet" href="../styles/footer.css" />
<link rel="stylesheet" href="../styles/orders.css" />
<link rel="shortcut icon" href="../images/header_icon.ico" type="image/x-icon" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="../scripts/app.js?orders"></script>
</head>
<body>
<header>
  <nav class="flex">
    <a href="../index.php"><img src="../images/logo.png" alt="logo" class="logo" /></a>
    <ul class="links flex">
      <li><a href="../index.php">Home</a></li>
      <li><a href="./shop.php">Shop</a></li>
      <li><a href="./contacts.php">Contact</a></li>
      <li><a href="./orders.php">My Orders</a></li>
      <li><a href="./cart.php"><i class="fa-solid fa-cart-shopping"><span class="badge bg-dark" id="cart_count">0</span></i></a></li>
      <li id="login_btn"><a href="./login.php">Login</a></li>
    </ul>
    <div class="burger"><div class="line1"></div><div class="line2"></div><div class="line3"></div></div>
  </nav>
</header>

<main class="orders-main">
  <h1 class="orders-title">My Orders</h1>
  <p class="orders-sub"><?= count($orders) ?> order(s) placed</p>

  <?php if ($flash): ?>
    <div class="kicks-flash <?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= h($flash['message']) ?></div>
  <?php endif; ?>

  <?php if (!$orders): ?>
    <div class="orders-empty">
      <i class="fa-solid fa-bag-shopping"></i>
      <p>No orders yet. <a href="shop.php">Start shopping →</a></p>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php
        $itemStmt->execute([$order['Order_id']]);
        $lines = $itemStmt->fetchAll();
        $isUnpaid = ($order['Payment_Status'] ?? 'unpaid') === 'unpaid';
        $status = $order['Status'];
        $isCancelled = $status === 'cancelled';
        $currentIndex = array_search($status, $trackerKeys, true);
      ?>
      <div class="order-card <?= !$isUnpaid ? 'is-paid' : '' ?>">
        <?php if (!$isUnpaid): ?>
          <div class="big-tick-badge">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M7 12.5l3 3 7-7"/></svg>
          </div>
        <?php endif; ?>

        <div class="order-card-head">
          <h2>Order #<?= (int)$order['Order_id'] ?> <span class="order-pill"><?= h(ucfirst($status)) ?></span></h2>
          <span class="order-date"><?= h(date('M j, Y g:ia', strtotime($order['Created_At']))) ?></span>
        </div>

        <div class="order-meta">
          <?php if ($isUnpaid): ?>
            <span class="status-chip pending">🕒 Payment pending</span>
          <?php else: ?>
            <span class="status-chip paid">✓ Paid</span>
          <?php endif; ?>
          <span class="payment-via">via <?= h(strtoupper($order['Payment_Method'] ?? 'COD')) ?></span>
          <?php if ($isUnpaid && !$isCancelled): ?>
            <a href="pay_order.php?order_id=<?= (int)$order['Order_id'] ?>" class="pay-now-btn">Complete payment →</a>
          <?php endif; ?>
        </div>

        <div class="order-lines">
          <?php foreach ($lines as $line): ?>
            <div class="order-line">
              <div class="order-line-info">
                <div class="order-line-name"><?= h($line['Product_Name']) ?></div>
                <div class="order-line-sub">AED<?= number_format($line['Price'], 2) ?> × <?= (int)$line['Quantity'] ?></div>
              </div>
              <div class="order-line-total">AED<?= number_format($line['Price'] * $line['Quantity'], 2) ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="order-total-row">
          <span>Order total</span>
          <b>AED<?= number_format($order['Total_Amount'], 2) ?></b>
        </div>

        <div class="delivery-card">
          <div class="delivery-title">
            <span>📍 Delivery Information</span>
            <?php if ($isCancelled): ?><span class="delivery-status-chip cancelled">✕ Cancelled</span><?php endif; ?>
          </div>

          <?php if (!$isCancelled): ?>
            <div class="status-tracker">
              <?php foreach ($trackerSteps as $key => $label): ?>
                <?php
                  $stepIndex = array_search($key, $trackerKeys, true);
                  $stateClass = $stepIndex < $currentIndex ? 'done' : ($stepIndex === $currentIndex ? 'active' : '');
                ?>
                <div class="tracker-step <?= $stateClass ?>">
                  <div class="tracker-dot"></div>
                  <div class="tracker-label"><?= $label ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="delivery-grid">
            <div class="delivery-item"><span class="delivery-label">Receiver</span><span class="delivery-value"><?= h($order['Receiver_Name']) ?></span></div>
            <div class="delivery-item"><span class="delivery-label">Phone</span><span class="delivery-value"><?= h($order['Receiver_Phone']) ?></span></div>
            <div class="delivery-item"><span class="delivery-label">City</span><span class="delivery-value"><?= h($order['City']) ?></span></div>
            <div class="delivery-item delivery-address"><span class="delivery-label">Address</span><span class="delivery-value"><?= nl2br(h($order['Delivery_Address'])) ?></span></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
<?php include "footer.php" ?>
<script src="../scripts/animations.js"></script>
<script>$(document).ready(function () { get_user_info(); });</script>
</body>
</html>

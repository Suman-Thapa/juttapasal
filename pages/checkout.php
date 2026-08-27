<?php
require_once __DIR__ . '/../server/db_pdo.php';
require_once __DIR__ . '/../includes/auth.php';
require_login('../');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT c.Product_id, c.Item_quantity, p.Brand, p.Description, p.Price, p.Quantity AS stock, p.Image_url
     FROM cart_item c JOIN product p ON p.Product_id = c.Product_id
     WHERE c.User_id = ?'
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if (!$items) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['Price'] * $item['Item_quantity'];
}
$shipping = 150;
$total    = $subtotal + $shipping;

$userStmt = $pdo->prepare('SELECT First_Name, Last_Name FROM Users WHERE ID = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
$fullName = trim(($user['First_Name'] ?? '') . ' ' . ($user['Last_Name'] ?? ''));

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Checkout — Kicks</title>
<link rel="stylesheet" href="../styles/login.css" />
<link rel="stylesheet" href="../styles/style.css?99" />
<link rel="stylesheet" href="../styles/checkout.css?" />
<link rel="stylesheet" href="../styles/footer.css" />
<link rel="shortcut icon" href="../images/header_icon.ico" type="image/x-icon" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="../scripts/app.js?checkout"></script>
<style>
  h3, h1 { color: black; }
  div h1 { padding: 1rem; font-size: 1.5rem; color: white; background: linear-gradient(90deg, #ffba00 0%, #ff6c00 100%); }

  .kicks-payment-options { display:flex; flex-direction:column; gap:12px; margin:16px 0 20px; }
  .kicks-payment-card {
    border:2px solid #eee; border-radius:14px; cursor:pointer; overflow:hidden;
    transition: border-color .15s, background .15s, transform .1s;
  }
  .kicks-payment-card:hover { transform: translateY(-1px); }
  .kicks-payment-card input { display:none; }
  .kicks-payment-card-inner { display:flex; align-items:center; gap:14px; padding:16px 18px; }
  .kicks-payment-icon { font-size:1.6rem; width:40px; text-align:center; }
  .kicks-payment-name { font-weight:700; font-size:.98rem; }
  .kicks-payment-sub { font-size:.8rem; color:grey; margin-top:2px; }
  .kicks-payment-check {
    margin-left:auto; width:22px; height:22px; border-radius:50%; border:2px solid #ddd;
    display:flex; align-items:center; justify-content:center; font-size:.7rem; color:transparent; flex-shrink:0;
  }
  .kicks-payment-card.selected { border-color: #ff6c00; background: rgba(255,108,0,0.06); }
  .kicks-payment-card.selected .kicks-payment-check { background:#ff6c00; border-color:#ff6c00; color:#fff; }

  .kicks-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:8px; }
  .kicks-form-grid .full { grid-column: 1 / -1; }
  .kicks-form-grid label { display:block; font-size:.85rem; font-weight:600; margin-bottom:4px; color:#333; }
  .kicks-form-grid input, .kicks-form-grid textarea {
    width:100%; padding:.65rem .8rem; border:1px solid #ddd; border-radius:8px; font-family:'Poppins',sans-serif;
  }
  .kicks-place-btn {
    width:100%; padding:.9rem; border:none; border-radius:10px; font-weight:700; color:#fff; cursor:pointer;
    background: linear-gradient(90deg, #ffba00 0%, #ff6c00 100%);
  }
  .kicks-place-btn:disabled { background:#ccc; cursor:not-allowed; }
  .kicks-flash { padding:12px 16px; border-radius:10px; margin:0 0 18px; font-weight:600; }
  .kicks-flash.error { background:#fdecec; color:#a33; }
  .kicks-flash.success { background:#eaf7ee; color:#2E7D5B; }
</style>
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

<main>
  <section class="third-section third-section-modify">
    <div class="container-fluid">
      <div class="row checkout-div">
        <h1 class="chechout-header">Checkout</h1>

        <?php if ($flash): ?>
          <div class="kicks-flash <?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>

        <div class="col-lg-8 col-sm-12 details-container">
          <h2>Delivery Details</h2>

          <form method="post" id="checkout-form" action="../server/place_order_cod.php">
            <div class="kicks-form-grid">
              <div>
                <label>Receiver Name</label>
                <input type="text" name="receiver_name" required value="<?= h($fullName) ?>">
              </div>
              <div>
                <label>Phone Number</label>
                <input type="text" name="receiver_phone" required placeholder="98XXXXXXXX">
              </div>
              <div>
                <label>City</label>
                <input type="text" name="city" required>
              </div>
              <div>
                <label>Postal Code (optional)</label>
                <input type="text" name="postal_code">
              </div>
              <div class="full">
                <label>Delivery Address</label>
                <textarea name="delivery_address" rows="3" required placeholder="House No, Street, Area, Landmark"></textarea>
              </div>
            </div>

            <h2>Payment Method</h2>
            <div class="kicks-payment-options">

              <label class="kicks-payment-card" id="label-cod">
                <input type="radio" name="pm_radio" value="cod" onchange="selectKicksPayment('cod')">
                <div class="kicks-payment-card-inner">
                  <span class="kicks-payment-icon">💵</span>
                  <div>
                    <div class="kicks-payment-name">Cash on Delivery</div>
                    <div class="kicks-payment-sub">Pay when your order arrives</div>
                  </div>
                  <span class="kicks-payment-check">✓</span>
                </div>
              </label>

              <label class="kicks-payment-card" id="label-esewa">
                <input type="radio" name="pm_radio" value="esewa" onchange="selectKicksPayment('esewa')">
                <div class="kicks-payment-card-inner">
                  <span class="kicks-payment-icon"><i class="fa-solid fa-wallet" style="color:#60bb46;"></i></span>
                  <div>
                    <div class="kicks-payment-name">eSewa</div>
                    <div class="kicks-payment-sub">Pay online instantly</div>
                  </div>
                  <span class="kicks-payment-check">✓</span>
                </div>
              </label>

              <label class="kicks-payment-card" id="label-khalti">
                <input type="radio" name="pm_radio" value="khalti" onchange="selectKicksPayment('khalti')">
                <div class="kicks-payment-card-inner">
                  <span class="kicks-payment-icon"><i class="fa-solid fa-wallet" style="color:#5c2d91;"></i></span>
                  <div>
                    <div class="kicks-payment-name">Khalti</div>
                    <div class="kicks-payment-sub">Pay online instantly</div>
                  </div>
                  <span class="kicks-payment-check">✓</span>
                </div>
              </label>

            </div>

            <button type="submit" class="kicks-place-btn" id="place-order-btn" disabled>Select a payment method</button>
          </form>
        </div>

        <div class="col-lg-4 col-sm-12 items-container">
          <h2>Items</h2>
          <div class="items-checkout">
            <?php foreach ($items as $item): ?>
              <div class="checkout-item-list">
                <div class="image-flex">
                  <img src="../<?= h($item['Image_url']) ?>" alt="<?= h($item['Brand']) ?>">
                  <span class="badge bg-warning bg-lg checkout-badge"><?= (int)$item['Item_quantity'] ?></span>
                </div>
                <div class="checkout-info">
                  <h6><?= h($item['Brand']) ?></h6>
                  <p><?= h($item['Description']) ?></p>
                </div>
                <h6 id="price">Npr<?= number_format($item['Price'] * $item['Item_quantity'], 2) ?></h6>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-lg-12">
          <div class="col-lg-4 col-sm-12 place-order-area">
            <div class="vat_total_price">
              <div class="checkout-total"><p class="checkout-text">Subtotal:</p><p>Npr<?= number_format($subtotal, 2) ?></p></div>
              <div class="checkout-total"><p class="checkout-text">Delivery charge:</p><p>Npr<?= number_format($shipping, 2) ?></p></div>
              <div class="checkout-total"><p class="checkout-text"><strong>Total:</strong></p><p><strong>Npr<?= number_format($total, 2) ?></strong></p></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include "footer.php" ?>
</body>
<script src="../scripts/animations.js"></script>
<script>
function selectKicksPayment(method) {
  document.querySelectorAll('.kicks-payment-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('label-' + method).classList.add('selected');

  const form = document.getElementById('checkout-form');
  const btn  = document.getElementById('place-order-btn');
  btn.disabled = false;

  if (method === 'cod') {
    form.action = '../server/place_order_cod.php';
    btn.textContent = 'Place order (COD) →';
  } else if (method === 'esewa') {
    form.action = '../server/checkout_redirect.php?method=esewa';
    btn.textContent = 'Pay with eSewa →';
  } else {
    form.action = '../server/checkout_redirect.php?method=khalti';
    btn.textContent = 'Pay with Khalti →';
  }
}

// Load cart count in the header, same as every other page
$(document).ready(function () { get_user_info(); });
</script>
</html>

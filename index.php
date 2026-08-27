<?php
require_once __DIR__ . '/server/db_pdo.php';

// Shows the 10 most recently added products — whatever the admin has
// most recently added/edited in admin/dashboard.php shows up here first.
// No "featured" flag needed: managing products IS managing this section.
$stmt = $pdo->query('SELECT Product_id, Brand, Description, Price, Image_url FROM product ORDER BY Product_id DESC LIMIT 10');
$collection = $stmt->fetchAll();

// Split into two 5-tile rows to reuse the existing .shoe-gallery grid exactly as-is
$rowA = array_slice($collection, 0, 5);
$rowB = array_slice($collection, 5, 5);

function renderGalleryRow(array $products): void {
    $slots = 5;
    for ($i = 0; $i < $slots; $i++) {
        $p = $products[$i] ?? null;
        echo '<div class="grid-pic grid-pic' . ($i + 1) . '">';
        if ($p) {
            echo '<a href="pages/shop.php" class="gallery-link">';
            echo '<img src="' . htmlspecialchars($p['Image_url']) . '" alt="' . htmlspecialchars($p['Brand']) . '" />';
            echo '<span class="gallery-caption"><strong>' . htmlspecialchars($p['Brand']) . '</strong><br>AED ' . number_format($p['Price'], 0) . '</span>';
            echo '</a>';
        } else {
            echo '<div class="gallery-placeholder"></div>';
        }
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- STYLES -->
    <link rel="stylesheet" href="./styles/style.css?15" />
    <link rel="stylesheet" href="./styles/footer.css" />
    <link rel="stylesheet" href="./styles/home_gallery.css" />

    <!-- HEADER ICON -->
    <link rel="shortcut icon" href="./images/header_icon.ico" type="image/x-icon" />
    <!-- FONTAWESOME LINK -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;800&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://kit.fontawesome.com/f583d957c8.js" crossorigin="anonymous"></script>
    <!-- AJAX AND JQUERY-->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- javascript script -->
    <script src="scripts/app.js?3"></script>

    <title>Home</title>
  </head>
  <body onload="get_user_info('home');">
    <header>
      <nav class="flex">
        <a href=""><img src="./images/logo.png" alt="logo" class="logo" /></a>
        <ul class="links flex">
          <li><a href="./index.php">Home</a></li>
          <li><a href="pages/shop.php">Shop</a></li>
          <li><a href="pages/contacts.php">Contact</a></li>
          <li><a href="pages/orders.php">My Orders</a></li>
          <li>
            <a href="pages/cart.php">
              <i class="fa-solid fa-cart-shopping">
                <span class="badge bg-dark" id="cart_count">0</span></i
              >
            </a>
          </li>
          <li id="login_btn"><a href="pages/login.php">Login</a></li>
        </ul>
        <div class="burger">
          <div class="line1"></div>
          <div class="line2"></div>
          <div class="line3"></div>
        </div>
      </nav>
    </header>
    <img src="./images/main-background.png" alt="" class="main-background" />
    <main>
      <section class="first-section flex">
        <div class="info">
          <h3>Nike New Collection!</h3>
          <p>
            This is Our Online Store <a href="pages/shop.php"><strong style="color:rgba(255, 157, 0, 0.799); text-decoration:none">Jutta Pasal</strong> </a>
            <br> Here you can find the best Deals on your prefered Sneaker Brands!
          </p>
          <div class="add-to-bag">
            <button class="shop-btn">
              <a href="pages/shop.php"
                ><i class="text-light fa-solid fa-cart-shopping"></i
              ></a>
            </button>
            <span>Go to shop</span>
          </div>
        </div>
        <div class="main-image">
          <img src="./images/product3.png" alt="main-image" class="logo" />
        </div>
      </section>
      <section class="second-section">
        <div class="mission flex">
          <div class="card">
            <h2><i class="fa-solid fa-truck"></i></h2>
            <h4>Free Delivery</h4>
            <p>Free shipping on all orders</p>
          </div>
          <div class="card">
            <h2><i class="fa-solid fa-rotate-left"></i></h2>
            <h4>Return Policy</h4>
            <p>Flexible return policy</p>
          </div>
          <div class="card">
            <h2><i class="fa-solid fa-headphones"></i></h2>
            <h4>24/7 support</h4>
            <p>Delightful service team</p>
          </div>
          <div class="card">
            <h2><i class="fa-solid fa-database"></i></h2>
            <h4>Secure Payment</h4>
            <p>Trusted and authenticated payments</p>
          </div>
        </div>

        <!-- DYNAMIC: latest 10 products the admin has added, 5 per row -->
        <div class="title"><h2>Our Collection</h2></div>
        <div class="shoe-gallery">
          <?php renderGalleryRow($rowA); ?>
        </div>

        <?php if ($rowB): ?>
        <div class="title" style="margin-top:2rem;"><h2>More Picks</h2></div>
        <div class="shoe-gallery">
          <?php renderGalleryRow($rowB); ?>
        </div>
        <?php endif; ?>

      </section>
    </main>
    <?php include "./pages/footer.php"?>
    <!-- javascript file -->
    <script src="scripts/animations.js"></script>
  </body>
</html>
